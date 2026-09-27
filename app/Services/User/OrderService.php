<?php

namespace App\Services\User;

use App\Enums\CartType;
use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Exceptions\CustomExceptionWithMessage;
use App\Http\Resources\Order\OneResource as OneResource;
use App\Http\Resources\Order\AllResource;
use App\Models\AffiliateWalletTransaction;
use App\Models\Basket;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Recipe;
use App\Models\ShopProductVariant;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Coupon;
use App\Models\PointExchange;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use App\Services\BaseService;
use App\Services\PointExchangeService;
use App\Services\User\CalculateDeliveryPriceService;
use App\Services\User\PromotionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Services\InventoryService;
use App\Traits\HasCurrencyConversion;
use Carbon\Carbon;

class OrderService extends BaseService
{
    use HasCurrencyConversion;
    public function __construct(
        Order $model,
        private CheckoutPolicyService $checkoutPolicy,
        private CartService $cartService,
    ) {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;

        $this->searchableFields = ['total', 'total_quantity'];
        $this->sortableFields   = ['id', 'total', 'total_quantity'];
        $this->relations = [
            'user',
            'address',
            'paymentMethod',
            'coupon',
            'items',
            'items.extras',
            'items.extras.extraDetail',
            'items.shopProductVariant.shop',
            'items.shopProductVariant.productVariant.product',
        ];
        $this->pagination       = true;
    }


    public function getAll($filters = [], $config = [])
    {
        $filters['user_id'] =  auth('user')->id() ?? 1;
        return parent::getAll($filters, $config);
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $status = $filters['status'] ?? null;
        $isRestaurant = array_key_exists('is_restaurant', $filters) ? $filters['is_restaurant'] : null;
        $shopType = $filters['shop_type'] ?? null;

        unset($filters['status'], $filters['is_restaurant'], $filters['shop_type']);

        $query = parent::queryBuilder($query, $filters, $config);

        // Status filter
        if (!empty($status)) {
            $query->where('status', $status);
        }

        if ($isRestaurant !== null) {
            $query->whereHas('items.shopProductVariant.shop', function ($shopQuery) use ($isRestaurant) {
                $shopQuery->where('is_restaurant', filter_var($isRestaurant, FILTER_VALIDATE_BOOLEAN));
            });
        }

        if (!empty($shopType)) {
            $query->whereHas('items.shopProductVariant.shop', function ($shopQuery) use ($shopType) {
                if ($shopType === 'restaurant') {
                    $shopQuery->where('is_restaurant', true);
                    return;
                }

                if ($shopType === 'store') {
                    $shopQuery
                        ->where('is_restaurant', false)
                        ->where('is_service_provider', false);
                }
            });
        }

        return $query;
    }

    /**
     * CREATE ORDER
     */
    public function create($data)
    {
        return DB::transaction(function () use ($data) {

            $user = $this->resolveUser();
            $address = $this->resolveAddress($user, $data['address_id']);
            $policy = $this->checkoutPolicy->forShops(
                $this->checkoutPolicy->shopsFromVariantIds(
                    collect($data['items'])->pluck('shop_product_variant_id')->all()
                )
            );
            $choice = ! empty($policy['instant_only'])
                ? 'asap'
                : $this->checkoutPolicy->resolveChoice($data);
            $this->checkoutPolicy->assertDeliveryChoice(
                $choice,
                $data['scheduled_delivery_at'] ?? null,
                $policy
            );

            $order = $this->createOrder($user, $address, $data, $choice);

            [$basketDiscountPercent, $deliveryPrice] =
                $this->resolveBasketAndDelivery($order, $data);
            $originalDeliveryPrice = $deliveryPrice;

            [
                $subtotalBeforeDiscount,
                $subtotalAfterProductDiscount,
                $totalQuantity,
                $orderItems
            ] = $this->addItemsToOrder($order, $data);

            $this->assertMinOrder($policy, (float) $subtotalAfterProductDiscount);

            $discounts = $this->applyExternalDiscounts(
                $order,
                $user,
                $data,
                $orderItems,
                $subtotalBeforeDiscount,
                $deliveryPrice
            );

            $externalDiscount = $discounts['total_discount'];
            $deliveryPrice = $discounts['delivery_price'];

            $promotionService = app(PromotionService::class);
            $deliveryBeforeAutomaticFreeShipping = $deliveryPrice;
            $deliveryPrice = $promotionService->resolveAutomaticFreeShippingDeliveryPrice(
                $deliveryPrice,
                $subtotalBeforeDiscount,
                $orderItems,
                $user,
                $order->id
            );
            $discounts['delivery_price'] = $deliveryPrice;

            $basketDiscount = $this->calculateBasketDiscount(
                $basketDiscountPercent,
                $subtotalBeforeDiscount,
                $externalDiscount
            );

            $finalTotal = $this->calculateFinalTotal(
                $subtotalAfterProductDiscount,
                $basketDiscount,
                $externalDiscount,
                $deliveryPrice
            );

            $affiliateData = $this->resolveAffiliateData(
                $data,
                $discounts['coupon'] ?? null,
                $orderItems,
                $finalTotal
            );

            $this->updateOrderTotals(
                $order,
                $discounts,
                $basketDiscount,
                $subtotalBeforeDiscount,
                $totalQuantity,
                $deliveryPrice,
                $originalDeliveryPrice,
                $finalTotal,
                $data['promotion_id'] ?? null,
                $affiliateData
            );

            $automaticPromotionsResult = $promotionService->applyAutomaticOrderPromotions(
                $order,
                $user->id,
                $subtotalBeforeDiscount,
                false,
                $orderItems
            );

            $automaticSnapshot = $promotionService->compileAutomaticPromotionsSnapshot(
                $deliveryBeforeAutomaticFreeShipping,
                $deliveryPrice,
                $automaticPromotionsResult,
                $subtotalBeforeDiscount,
                $orderItems,
                $user,
                $order->id,
                $discounts['automatic_trigger_discounts'] ?? []
            );
            if ($automaticSnapshot !== null) {
                $order->update(['automatic_promotions_snapshot' => $automaticSnapshot]);
            }

            $this->maybeIncrementRecipeOrdersCount($order, $data);


            $this->dispatchOrderCreatedEvent($order);

            return new OneResource($order->fresh('items'));
        });
    }

    /**
     * PREVIEW ORDER
     */
    public function preview(array $data): array
    {
        $user = $this->resolveUser();

        [$basketDiscountPercent, $deliveryPrice] =
            $this->resolveBasketAndDelivery(null, $data);

        [
            $subtotalBeforeDiscount,
            $subtotalAfterProductDiscount,
            $totalQuantity,
            $orderItems
        ] = $this->addItemsToOrder(null, $data);
        $policy = $this->checkoutPolicy->forShops(
            $this->checkoutPolicy->shopsFromVariantIds(
                collect($data['items'] ?? [])->pluck('shop_product_variant_id')->all()
            )
        );
        $checkout = $this->cartService->presentCheckout(
            $this->checkoutPolicy->summarize($policy, (float) $subtotalAfterProductDiscount)
        );
        $checkout['delivery_error'] = $this->deliveryChoiceError($data, $policy);
        if ($checkout['delivery_error'] !== null) {
            $checkout['can_checkout'] = false;
        }
        $promotionService = app(PromotionService::class);
        // خصومات عامة + نقاط + اشتراك
        $discounts = $this->applyExternalDiscounts(
            null,
            $user,
            $data,
            $orderItems,
            $subtotalBeforeDiscount,
            $deliveryPrice
        );

        unset($discounts['coupon']);

        $externalDiscount = $discounts['total_discount'];
        $deliveryPrice = $discounts['delivery_price'];

        $deliveryPrice = $promotionService->resolveAutomaticFreeShippingDeliveryPrice(
            $deliveryPrice,
            $subtotalBeforeDiscount,
            $orderItems,
            $user
        );
        $discounts['delivery_price'] = $deliveryPrice;

        $basketDiscount = $this->calculateBasketDiscount(
            $basketDiscountPercent,
            $subtotalBeforeDiscount,
            $externalDiscount
        );

        $finalTotal = $this->calculateFinalTotal(
            $subtotalAfterProductDiscount,
            $basketDiscount,
            $externalDiscount,
            $deliveryPrice
        );
        $availablePromotions = $promotionService
            ->getAvailablePromotions($subtotalBeforeDiscount, collect($orderItems));

        $automaticPromotions = $promotionService->applyAutomaticOrderPromotions(
            null,
            $user->id,
            $subtotalBeforeDiscount,
            true,
            $orderItems
        );

        $discounts['basketDiscount'] = $basketDiscount;
        $automaticTriggerDiscounts = $discounts['automatic_trigger_discounts'] ?? ['discount' => 0, 'promotions' => []];
        unset($discounts['automatic_trigger_discounts']);

        $formattedDiscounts = array_map(function ($value) {
            if (is_numeric($value)) {
                return $this->convertFormattedPrice($value);
            }
            return $value;
        }, $discounts);

        $formattedItems = array_map(function ($item) {
            return [
                "shop_product_variant_id" => $item["shop_product_variant_id"],
                "product_name" => $item["product_name"],
                "product_image" => $item["product_image"] ?? null,
                "quantity" => $item["quantity"],
                "unit_price" => $this->convertFormattedPrice($item["unit_price"]),
                "final_price" => $this->convertFormattedPrice($item["final_price"]),
                "subtotal" => $this->convertFormattedPrice($item["subtotal"]),
                "extras_total" => $this->convertFormattedPrice($item["extras_total"]),
                "total" => $this->convertFormattedPrice($item["total"]),
                "variant" => $item["variant"],
            ];
        }, $orderItems->toArray());

        return [
            'discounts' => $formattedDiscounts,
            'subtotal_before_discount' => $this->convertFormattedPrice($subtotalBeforeDiscount),
            'subtotal_after_product_discount' => $this->convertFormattedPrice($subtotalAfterProductDiscount),
            'total_quantity' => $totalQuantity,
            'total' => $this->convertFormattedPrice($finalTotal),
            'available_promotions' => $availablePromotions,
            'automatic_promotions' => array_merge($automaticPromotions, [
                'free_shipping_applies' => $promotionService->hasActiveAutomaticFreeShipping(
                    $subtotalBeforeDiscount,
                    $orderItems,
                    $user
                ),
                'discounts' => $automaticTriggerDiscounts['promotions'] ?? [],
            ]),
            'promotion' => $discounts['promotion'] ?? null,
            'excluded_items' => $discounts['excluded_items'] ?? [],
            'orderItems' => $formattedItems,
            'checkout' => $checkout,
        ];
    }

    private function deliveryChoiceError(array $data, array $policy): ?string
    {
        if (! empty($policy['instant_only'])) {
            return null;
        }

        if (!array_key_exists('delivery_choice', $data) && !array_key_exists('is_instant_delivery', $data) && empty($data['scheduled_delivery_at'])) {
            return null;
        }

        try {
            $this->checkoutPolicy->assertDeliveryChoice(
                $this->checkoutPolicy->resolveChoice($data),
                isset($data['scheduled_delivery_at']) ? (string) $data['scheduled_delivery_at'] : null,
                $policy
            );
        } catch (CustomExceptionWithMessage $exception) {
            return __($exception->getTranslationKey(), $exception->getReplacements());
        }

        return null;
    }

    private function assertMinOrder(array $policy, float $subtotal): void
    {
        $summary = $this->checkoutPolicy->summarize($policy, $subtotal);
        if ($summary['can_checkout']) {
            return;
        }

        throw new CustomExceptionWithMessage('custom.checkout.min_order_remaining', 422, [
            'min' => $this->convertFormattedPrice($summary['min_order_amount']),
            'remaining' => $this->convertFormattedPrice($summary['remaining_amount']),
        ]);
    }

    /**
     * USER
     */
    private function resolveUser(): User
    {
        return auth('user')->user() ?? User::findOrFail(1);
    }

    private function resolveAddress(User $user, int $addressId)
    {
        return $user->addresses()->findOrFail($addressId);
    }

    private function createOrder(User $user, $address, array $data, string $choice): Order
    {
        $paymentMethod = $this->resolvePaymentMethod($data);

        return Order::create([
            'user_id' => $user->id,
            'user_address_id' => $address->id,
            'cart_type' => $data['cart_type'] ?? CartType::DEFAULT->value,
            'is_instant_delivery' => $choice === 'asap',
            'scheduled_delivery_at' => $choice === 'scheduled'
                ? Carbon::parse($data['scheduled_delivery_at'])->seconds(0)
                : null,
            'status' => OrderStatus::PENDING->value,
            'payment_method_id' => $paymentMethod->id,
            'is_paid' => $paymentMethod->isPaidOnPlacement(),
        ]);
    }

    private function resolvePaymentMethod(array $data): PaymentMethod
    {
        if (!empty($data['payment_method_id'])) {
            $paymentMethod = PaymentMethod::query()
                ->active()
                ->find($data['payment_method_id']);

            if (!$paymentMethod) {
                throw new CustomExceptionWithMessage('طريقة الدفع المختارة غير متاحة حالياً', 422);
            }

            return $paymentMethod;
        }

        $defaultPaymentMethod = PaymentMethod::resolveDefault();

        if (!$defaultPaymentMethod) {
            throw new CustomExceptionWithMessage('لا توجد بوابة دفع افتراضية مفعلة في النظام', 422);
        }

        return $defaultPaymentMethod;
    }

    private function applyExternalDiscounts(
        ?Order $order,
        User $user,
        array $data,
        $orderItems,
        float $subtotal,
        float $deliveryPrice
    ): array {

        $promotionService = app(PromotionService::class);
        $couponDiscount = 0;
        $pointsDiscount = 0;
        $subscriptionDiscount = 0;
        $useSubscriptionFreeDelivery = false;
        $promotionDiscount = 0;
        $promotionPreview = null;
        $freeDeliveryFromPoints = false;
        $totalDiscount = 0;

        $isPreview = !$order;
        $excludedItems = [];
        $couponModel = null;

        /**
         * COUPON
         */
        if (!empty($data['coupon'])) {

            [$code, $couponDiscount, $excludedItems, $couponModel] =
                $this->applyCoupon($order, $orderItems, $data['coupon'], 0);

            $totalDiscount += $couponDiscount;
        }

        /**
         * POINTS (preview / create)
         */
        $points = $this->handlePointExchanges(
            $user->id,
            $data,
            $deliveryPrice,
            !$isPreview // apply only when create
        );

        $pointsDiscount = $points['coupon_discount'];
        $freeDeliveryFromPoints = $points['free_delivery'];
        $totalDiscount += $pointsDiscount;

        if ($freeDeliveryFromPoints) {
            $deliveryPrice = 0;
        }

        /**
         * SUBSCRIPTION
         */
        if (!empty($data['use_subscription_discount']) || !empty($data['use_subscription_free_delivery'])) {

            $subscription = app(SubscriptionBenefitsService::class)
                ->applyBenefits(
                    $order,
                    $user->id,
                    $subtotal,
                    $deliveryPrice,
                    !empty($data['use_subscription_discount']),
                    !empty($data['use_subscription_free_delivery'])
                );

            $subscriptionDiscount = $subscription['discount_amount'] ?? 0;
            $totalDiscount += $subscriptionDiscount;

            if ($subscription['free_delivery_applied'] ?? false) {
                $deliveryPrice = 0;
                $useSubscriptionFreeDelivery = true;
            }
        }

        /**
         * PROMOTION
         */
        if (!empty($data['promotion_id'])) {
            $promotionPreview = $promotionService->evaluateDiscountPromotion(
                (int) $data['promotion_id'],
                $subtotal,
                collect($orderItems)
            );

            $promotionDiscount = (float) ($promotionPreview['discount'] ?? 0);

            $totalDiscount += $promotionDiscount;
        }

        $automaticTriggerDiscounts = $promotionService->evaluateAutomaticTriggerDiscounts(
            $user,
            $subtotal,
            collect($orderItems),
            $order?->id,
            $totalDiscount
        );
        $promotionDiscount += (float) ($automaticTriggerDiscounts['discount'] ?? 0);
        $totalDiscount += (float) ($automaticTriggerDiscounts['discount'] ?? 0);

        return [
            'coupon_discount' =>   $couponDiscount,
            'coupon_discount_from_points' => $pointsDiscount,
            'subscription_discount' =>  $subscriptionDiscount,
            'promotion_discount' => $promotionDiscount,
            'total_discount' => $totalDiscount,
            'delivery_price' => $deliveryPrice,
            'useSubscriptionFreeDelivery' => $useSubscriptionFreeDelivery,
            'free_delivery_from_points' => $freeDeliveryFromPoints,
            'coupon' => $couponModel,
            'promotion' => $promotionPreview,
            'automatic_trigger_discounts' => $automaticTriggerDiscounts,

            'excluded_items' => $excludedItems, // <--- ترجع الآن بالـ preview
        ];
    }

    private function calculateBasketDiscount(
        float $percent,
        float $subtotal,
        float $externalDiscount
    ): float {

        if ($externalDiscount > 0) {
            return 0;
        }

        return $subtotal * $percent / 100;
    }

    private function calculateFinalTotal(
        float $subtotal,
        float $basketDiscount,
        float $externalDiscount,
        float $delivery
    ): float {

        return round(
            $subtotal - $basketDiscount - $externalDiscount + $delivery,
            2
        );
    }

    private function updateOrderTotals(
        Order $order,
        array $discounts,
        float $basketDiscount,
        float $subtotal,
        int $totalQuantity,
        float $deliveryPrice,
        float $originalDeliveryPrice,
        float $finalTotal,
        ?int $promotionId = null,
        ?array $affiliateData = null
    ): void {

        $order->update([
            'subtotal' => $subtotal,
            'basket_discount' => round($basketDiscount, 2),
            'coupon_discount' => round($discounts['coupon_discount'], 2),
            'coupon_discount_from_points' =>
            round($discounts['coupon_discount_from_points'], 2),
            'free_delivery_from_points' =>
            $discounts['free_delivery_from_points'],
            'subscription_discount' =>
            round($discounts['subscription_discount'], 2),
            'promotion_discount' =>
            round($discounts['promotion_discount'], 2),
            'delivery_price' => $deliveryPrice,
            'original_delivery_price' => round($originalDeliveryPrice, 2),
            'total_quantity' => $totalQuantity,
            'total' => $finalTotal,
            'promotion_id' => $promotionId,
            'affiliate_id' => $affiliateData['affiliate_id'] ?? null,
            'affiliate_rate' => $affiliateData['affiliate_rate'] ?? null,
            'affiliate_source' => $affiliateData['affiliate_source'] ?? null,
            'affiliate_commission_type' => $affiliateData['affiliate_commission_type'] ?? null,
            'affiliate_fixed_commission' => $affiliateData['affiliate_fixed_commission'] ?? null,
            'affiliate_commission_amount' => $affiliateData['affiliate_commission_amount'] ?? 0,
        ]);
    }

    private function dispatchOrderCreatedEvent(Order $order): void
    {
        OrderStatusChanged::dispatch(
            $order->fresh('items'),
            null,
            OrderStatus::PENDING->value,
            'user'
        );
    }

    private function maybeIncrementRecipeOrdersCount(Order $order, array $data): void
    {
        $recipeId = $data['recipe_id'] ?? null;
        $cartType = $data['cart_type'] ?? CartType::DEFAULT->value;

        if ($cartType !== CartType::RECIPE->value || !$recipeId) {
            return;
        }

        Recipe::whereKey($recipeId)->increment('orders_count');
    }

    /** -----------------------------
     * Resolve basket discount & delivery price
     * ----------------------------- */
    protected function resolveBasketAndDelivery($orderOrNull, $data)
    {
        //admin basket id
        //scedule id
        $cartType = $data['cart_type'] ?? CartType::DEFAULT->value;
        $basketDiscount = 0;
        $deliveryPrice = 0;
        $user = auth('user')->user() ?? User::find(1);

        switch ($cartType) {
            case CartType::RECIPE->value:
                $basket = Recipe::findOrFail($data['recipe_id']);
                $basketDiscount = $basket->discount;
                $deliveryPrice  = $basket->delivery_price;
                break;

            case CartType::ADMIN_CART->value:
                $basket = Basket::findOrFail($data['admin_basket_id']);
                $basketDiscount = $basket->discount;
                $deliveryPrice  = $basket->delivery_price;
                break;

            case CartType::SCHEDULE_ADMIN_CART->value:
                // Support both admin_schedule_basket_id and admin_basket_id for backward compatibility
                $basketId = $data['admin_schedule_basket_id'] ?? $data['admin_basket_id'] ?? null;

                if (!$basketId) {
                    throw new CustomExceptionWithMessage('custom.orders.basket_id_required');
                }

                $basket = Basket::with('catalogSchedule')->findOrFail($basketId);
                $basketDiscount = $basket->resolvedDiscountValue();
                $deliveryPrice = $basket->delivery_price;
                break;

            case CartType::DEFAULT->value:
            default:
                $variantIds = collect($data['items'])->pluck('shop_product_variant_id')->values()->toArray();

                $deliveryPrice = CalculateDeliveryPriceService::handle(
                    user: $user,
                    items: $variantIds,
                    addressId: $data['address_id'] ?? null
                );

                // التحقق من التوصيل المجاني من النقاط
                try {
                    $exchangeService = app(\App\Services\PointExchangeService::class);
                    if ($exchangeService->hasActiveFreeDelivery($user->id)) {
                        $deliveryPrice = 0;
                    }
                } catch (\Throwable $e) {
                    // تجاهل أخطاء النقاط
                }
                break;
        }

        return [$basketDiscount, $deliveryPrice];
    }

    /** -----------------------------
     * Add items to order / preview
     * ----------------------------- */
    protected function addItemsToOrder($order, $data)
    {
        $subtotalBeforeDiscount = 0;
        $subtotalAfterProductDiscount = 0;
        $totalQuantity = 0;
        $orderItems = collect();

        $isPreview = !$order;

        // تحديد إذا في خصم خارجي
        $hasExternalDiscount = !empty($data['coupon'])
            || !empty($data['point_coupon_exchange_id'])
            || !empty($data['use_subscription_discount'])
            || !empty($data['promotion_id']);

        // تحديد نوع السلة
        $cartType = $data['cart_type'] ?? 'default';
        $hasBasket = $cartType !== CartType::DEFAULT->value;

        foreach ($data['items'] as $item) {

            $shopVariant = ShopProductVariant::with('productVariant.product')
                ->findOrFail($item['shop_product_variant_id']);

            $productVariant = $shopVariant->productVariant;

            if (!$isPreview && $productVariant) {
                $productVariant = ProductVariant::query()
                    ->with('product')
                    ->lockForUpdate()
                    ->findOrFail($productVariant->id);
            }

            $availableQty = $productVariant?->quantity;

            if (!is_null($availableQty) && $availableQty < $item['quantity']) {
                throw new CustomExceptionWithMessage(
                    'custom.orders.insufficient_stock',
                    400,
                    ['product' => $productVariant->product->name]
                );
            }

            $product = $productVariant->product;
            $unitPrice = (float) $productVariant->price;
            $quantity = $item['quantity'];

            // حساب سعر الـ extras
            $extrasTotal = 0;
            $extrasData = [];

            if (!empty($item['extras']) && is_array($item['extras'])) {
                $extrasMap = collect($item['extras'])
                    ->filter(fn ($extra) => is_array($extra) && isset($extra['id']))
                    ->mapWithKeys(fn ($extra) => [
                        (int) $extra['id'] => (int) ($extra['quantity'] ?? 1),
                    ])
                    ->all();

                $extraDetails = DB::table('product_extra_detail_options')
                    ->join(
                        'product_extra_details',
                        'product_extra_detail_options.product_extra_detail_id',
                        '=',
                        'product_extra_details.id'
                    )
                    ->where('product_extra_detail_options.product_id', $product->id)
                    ->whereIn('product_extra_detail_options.product_extra_detail_id', array_keys($extrasMap))
                    ->select([
                        'product_extra_details.id',
                        'product_extra_details.detail_key',
                        'product_extra_details.detail_value',
                        'product_extra_detail_options.price',
                        'product_extra_detail_options.quantity as max_quantity',
                    ])
                    ->get();

                foreach ($extraDetails as $extra) {
                    $extraQuantity = $extrasMap[$extra->id] ?? 1;
                    $extrasTotal += $extra->price * $extraQuantity;
                    $extrasData[] = [
                        'id' => $extra->id,
                        'detail_key' => $extra->detail_key,
                        'detail_value' => $extra->detail_value,
                        'price' => $extra->price,
                        'quantity' => $extraQuantity,
                    ];
                }
            }

            $finalPrice = $this->resolveFinalPrice(
                unitPrice: $unitPrice,
                productDiscountPercent: $product->discount,
                hasExternalDiscount: $hasExternalDiscount,
                hasBasket: $hasBasket,
                cartType: $cartType
            );

            $subtotal = $finalPrice * $quantity;
            $total = $subtotal + $extrasTotal;
            $commissionProfile = $this->resolveVendorCommissionProfile((int) $product->vendor_id);
            $commissionAmount = $this->resolveCommissionAmount(
                lineTotal: $total,
                quantity: (int) $quantity,
                commissionType: (string) ($commissionProfile['type'] ?? 'percentage'),
                commissionRate: (float) ($commissionProfile['rate'] ?? 0),
                fixedCommission: (float) ($commissionProfile['fixed'] ?? 0)
            );

            if ($order) {
                $orderItem = $order->items()->create([
                    'shop_product_variant_id' => $shopVariant->id,
                    'product_name' => $product->name,
                    'variant_attributes' => $productVariant->getAttributesValuesAttribute(),
                    'product_image' => $product->image_url,
                    'note' => $item['note'] ?? null,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'unit_price' => $unitPrice,
                    'final_price' => $finalPrice,
                    'subtotal' => $subtotal,
                    'extras_total' => $extrasTotal,
                    'total' => $total,
                    'commission_snapshot_type' => (string) ($commissionProfile['type'] ?? 'percentage'),
                    'commission_snapshot_rate' => (float) ($commissionProfile['rate'] ?? 0),
                    'commission_snapshot_fixed' => (float) ($commissionProfile['fixed'] ?? 0),
                    'commission_snapshot_amount' => $commissionAmount,
                    'commission_snapshot_source' => (string) ($commissionProfile['source'] ?? 'vendor'),
                    'commission_snapshot_package_id' => $commissionProfile['package_id'] ?? null,
                    'commission_snapshot_package_name' => $commissionProfile['package_name'] ?? null,
                ]);

                // حفظ الـ extras
                if (!empty($extrasData)) {
                    foreach ($extrasData as $extra) {
                        $orderItem->extras()->create([
                            'product_extra_detail_id' => $extra['id'],
                            'price' => $extra['price'],
                            'quantity' => $extra['quantity'],
                        ]);
                    }
                }

                app(InventoryService::class)
                    ->decreaseStock($shopVariant->id, $quantity);
            }

            $subtotalBeforeDiscount += $unitPrice * $quantity;
            $subtotalAfterProductDiscount += $total;
            $totalQuantity += $quantity;

            $orderItems->push([
                'shop_product_variant_id' => $shopVariant->id,
                'shop_id' => $shopVariant->shop_id,
                'product_name' => $product->name,
                'product_image' => $product->image_url,
                'note' => $item['note'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'final_price' => $finalPrice,
                'subtotal' => $subtotal,
                'extras_total' => $extrasTotal,
                'total' => $total,
                'extras' => $extrasData,
                'vendor_id' => $product->vendor_id,
                'category_id' => $product->category_id,
                'product_id' => $product->id,
                'variant' => $productVariant->attributes_values->pluck('name')->toArray()

            ]);
        }


        return [
            $subtotalBeforeDiscount,
            $subtotalAfterProductDiscount,
            $totalQuantity,
            $orderItems
        ];
    }

    protected function resolveFinalPrice(
        float $unitPrice,
        float $productDiscountPercent,
        bool $hasExternalDiscount,
        bool $hasBasket,
        string $cartType
    ): float {
        $shouldApplyProductDiscount =
            !$hasExternalDiscount
            && !$hasBasket
            && $cartType === CartType::DEFAULT->value;

        if (!$shouldApplyProductDiscount) {
            return $unitPrice;
        }

        return $unitPrice * (1 - ($productDiscountPercent / 100));
    }

    protected function applyCoupon($orderOrNull, $basketItemsCollection, $couponCode, $basketDiscount)
    {
        if (!$couponCode) return [null, 0, [], null];

        $coupon = Coupon::valid()
            ->with(['products', 'categories', 'vendors', 'shops'])
            ->where('code', $couponCode)
            ->first();

        if (!$coupon) {
            throw new CustomExceptionWithMessage('custom.invalid_coupon');
        }

        // ❌ Marketer cannot use own affiliate coupon
        $buyerUserId = $orderOrNull?->user_id ?? auth('user')->id();
        if (!empty($coupon->affiliate_id) && $buyerUserId) {
            $buyer = User::query()->select(['id', 'affiliate_id'])->find($buyerUserId);
            if ($buyer?->affiliate_id && (string) $buyer->affiliate_id === (string) $coupon->affiliate_id) {
                throw new CustomExceptionWithMessage('custom.coupons.cannot_use_own_coupon');
            }
        }

        $excludedItems = [];

        foreach ($basketItemsCollection as $item) {
            $allowed = true;

            if ($coupon->products->isNotEmpty() && !$coupon->products->contains('id', $item['product_id'])) {
                $allowed = false;
            }

            if ($coupon->categories->isNotEmpty() && !$coupon->categories->contains('id', $item['category_id'])) {
                $allowed = false;
            }

            if ($coupon->vendors->isNotEmpty() && !$coupon->vendors->contains('id', $item['vendor_id'])) {
                $allowed = false;
            }

            if ($coupon->shops->isNotEmpty() && !$coupon->shops->contains('id', $item['shop_id'])) {
                $allowed = false;
            }

            if (!$allowed) {
                $excludedItems[] = $item['shop_product_variant_id'];
            }
        }

        $eligibleSubtotal = collect($basketItemsCollection)
            ->whereNotIn('shop_product_variant_id', $excludedItems)
            ->sum(fn($i) => $i['subtotal']);

        $couponDiscountAmount = $coupon->calculateDiscount($eligibleSubtotal);

        return [
            $coupon->code,
            $couponDiscountAmount,
            $excludedItems,
            $coupon
        ];
    }

    private function resolveAffiliateData(array $data, ?Coupon $coupon, $orderItems, float $finalTotal): ?array
    {
        $affiliateId = $data['affiliate_id'] ?? null;
        $affiliateSource = $affiliateId ? 'link' : null;

        if (!empty($coupon?->affiliate_id)) {
            $affiliateId = (string) $coupon->affiliate_id;
            $affiliateSource = 'coupon';
        }

        if (empty($affiliateId)) {
            return null;
        }

        $affiliate = User::query()
            ->with('affiliateProducts:id')
            ->where('affiliate_id', $affiliateId)
            ->where('is_affiliate', true)
            ->where('affiliate_approved', true)
            ->first();

        if (!$affiliate) {
            return null;
        }

        $commissionType = $affiliate->affiliate_commission_type ?? 'percentage_order';
        $rate = $affiliate->affiliate_rate ? (float) $affiliate->affiliate_rate : null;
        $fixedCommission = $affiliate->affiliate_fixed_commission ? (float) $affiliate->affiliate_fixed_commission : null;

        $commissionAmount = 0.0;

        if ($commissionType === 'fixed_per_order') {
            $commissionAmount = (float) ($fixedCommission ?? 0);
        } elseif ($commissionType === 'percentage_selected_products') {
            $eligibleProductIds = $affiliate->affiliateProducts->pluck('id')->all();
            $eligibleTotal = collect($orderItems)
                ->whereIn('product_id', $eligibleProductIds)
                ->sum(fn($item) => (float) $item['total']);

            $commissionAmount = $rate ? ($eligibleTotal * ($rate / 100)) : 0;
        } else {
            $commissionAmount = $rate ? ($finalTotal * ($rate / 100)) : 0;
        }

        return [
            'affiliate_id' => $affiliate->affiliate_id,
            'affiliate_rate' => $rate,
            'affiliate_source' => $affiliateSource,
            'affiliate_commission_type' => $commissionType,
            'affiliate_fixed_commission' => $commissionType === 'fixed_per_order' ? $fixedCommission : null,
            'affiliate_commission_amount' => round($commissionAmount, 2),
        ];
    }
    protected function handlePointExchanges(
        int $userId,
        array $data,
        float $deliveryPrice,
        bool $apply = false
    ): array {

        $result = [
            'coupon_discount' => 0,
            'free_delivery' => false,
            'coupon_exchange_id' => null,
            'free_delivery_exchange_id' => null,
        ];

        try {

            $exchangeService = app(PointExchangeService::class);

            /**
             * Coupon from points
             */
            if (!empty($data['point_coupon_exchange_id'])) {

                $exchange = PointExchange::where('id', $data['point_coupon_exchange_id'])
                    ->where('user_id', $userId)
                    ->where('exchange_type', 'coupon')
                    ->where('status', 'completed')
                    ->first();

                if ($exchange && $exchangeService->isExchangeValid($exchange)) {

                    $result['coupon_discount'] =
                        $exchange->exchange_data['discount_amount'] ?? 0;

                    $result['coupon_exchange_id'] = $exchange->id;

                    if ($apply) {
                        $exchangeService->markExchangeAsUsed($exchange->id);
                    }
                }
            }

            /**
             * Free delivery
             */
            if (!empty($data['point_free_delivery_exchange_id'])) {

                $exchange = PointExchange::where('id', $data['point_free_delivery_exchange_id'])
                    ->where('user_id', $userId)
                    ->where('exchange_type', 'free_delivery')
                    ->where('status', 'completed')
                    ->first();

                if ($exchange && $exchangeService->isExchangeValid($exchange)) {

                    $result['free_delivery'] = true;
                    $result['free_delivery_exchange_id'] = $exchange->id;

                    if ($apply) {
                        $exchangeService->markExchangeAsUsed($exchange->id);
                    }
                }
            }
        } catch (\Throwable $e) {

            Log::error('Point exchange failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }


    public function cancel(int $orderId)
    {
        $userId = auth('user')->id();

        $order = Order::with('items')->where('id', $orderId)
            ->where('user_id', $userId)
            ->firstOrFail();

        if (! in_array($order->status, [
            OrderStatus::PENDING->value,
            OrderStatus::WAITING_APPROVAL->value,
        ], true)) {
            throw new CustomExceptionWithMessage('custom.orders.cannot_cancel');
        }

        foreach ($order->items as $item) {
            if (! in_array($item->item_status, [
                OrderStatus::PENDING->value,
                OrderStatus::WAITING_APPROVAL->value,
            ], true)) {
                throw new CustomExceptionWithMessage('custom.orders.items_cannot_cancel');
            }
        }
        $oldStatus = $order->status;

        $order->update([
            'status' => OrderStatus::CANCELLED->value
        ]);

        $inventory = app(InventoryService::class);

        foreach ($order->items as $item) {

            if (! $item->is_external && $item->shop_product_variant_id) {
                $inventory->increaseStock(
                    (int) $item->shop_product_variant_id,
                    (int) $item->quantity
                );
            }

            $item->update([
                'item_status' => OrderStatus::CANCELLED->value
            ]);
        }

        if ($order->custom_order_request_id) {
            \App\Models\CustomOrderRequest::query()
                ->where('id', $order->custom_order_request_id)
                ->where('status', \App\Enums\CustomOrderRequestStatus::WAITING_APPROVAL->value)
                ->update(['status' => \App\Enums\CustomOrderRequestStatus::CANCELLED->value]);
        }

        event(new OrderStatusChanged(
            order: $order,
            from: $oldStatus,
            to: OrderStatus::CANCELLED->value,
            changedBy: 'user'
        ));
    }

    public function activeOrder()
    {
        $userId = auth('user')->id();
        $order = Order::with('items')
            ->where('user_id', $userId)
            ->whereNotIn('status', [
                OrderStatus::DELIVERED->value,
                OrderStatus::CANCELLED->value,
            ])
            ->latest()
            ->first();
        return $order ? OneResource::make($order) : null;
    }



    public function reorder($orderId)
    {
        $user = auth('user')->user();

        $originalOrder = Order::with(['items', 'paymentMethod'])->findOrFail($orderId);

        // Ensure the order belongs to the user
        if ($originalOrder->user_id !== $user->id) {
            throw new CustomExceptionWithMessage('custom.orders.not_found', 404);
        }

        return DB::transaction(function () use ($originalOrder, $user) {
            // Create new order with same data, but reset some fields
            $newOrder = Order::create([
                'user_id' => $user->id,
                'user_address_id' => $originalOrder->user_address_id,
                'payment_method_id' => $originalOrder->payment_method_id,
                'is_paid' => $originalOrder->paymentMethod?->isPaidOnPlacement() ?? false,
                'basket_id' => $originalOrder->basket_id,
                'basket_schedule_id' => $originalOrder->basket_schedule_id,
                'is_instant_delivery' => $originalOrder->is_instant_delivery,
                'status' => OrderStatus::PENDING->value,
                'cart_type' => $originalOrder->cart_type,
                'delivery_price' => $originalOrder->delivery_price,
                'original_delivery_price' => $originalOrder->original_delivery_price ?? $originalOrder->delivery_price,
                'total_quantity' => $originalOrder->total_quantity,
                'total' => $originalOrder->total,
                'subtotal' => $originalOrder->subtotal,
                'basket_discount' => $originalOrder->basket_discount,
                'coupon_discount' => $originalOrder->coupon_discount,
                'affiliate_id' => $originalOrder->affiliate_id,
                'affiliate_rate' => $originalOrder->affiliate_rate,
                'affiliate_source' => $originalOrder->affiliate_source,
                'affiliate_commission_type' => $originalOrder->affiliate_commission_type,
                'affiliate_fixed_commission' => $originalOrder->affiliate_fixed_commission,
                'affiliate_commission_amount' => $originalOrder->affiliate_commission_amount,
                'coupon_id' => $originalOrder->coupon_id,
                'used_coupon_exchange_id' => $originalOrder->used_coupon_exchange_id,
                'used_free_delivery_exchange_id' => $originalOrder->used_free_delivery_exchange_id,
                'coupon_discount_from_points' => $originalOrder->coupon_discount_from_points,
                'free_delivery_from_points' => $originalOrder->free_delivery_from_points,
                'subscription_id' => $originalOrder->subscription_id,
                'subscription_discount' => $originalOrder->subscription_discount,
                'subscription_free_delivery' => $originalOrder->subscription_free_delivery,
                'subscription_points_bonus' => $originalOrder->subscription_points_bonus,
                // Reset timestamps
                'pending_at' => now(),
                'preparing_at' => null,
                'out_delivery_at' => null,
                'delivered_at' => null,
                'driver_id' => null,
                'assigned_by' => null,
            ]);

            // Copy order items
            foreach ($originalOrder->items as $item) {
                $newOrder->items()->create([
                    'shop_product_variant_id' => $item->shop_product_variant_id,
                    'product_name' => $item->product_name,
                    'variant_attributes' => $item->variant_attributes,
                    'item_status' => OrderStatus::PENDING->value,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                    'unit_price' => $item->unit_price,
                    'final_price' => $item->final_price,
                    'subtotal' => $item->subtotal,
                    'extras_total' => $item->extras_total,
                    'total' => $item->total,
                    'commission_snapshot_type' => $item->commission_snapshot_type ?? 'percentage',
                    'commission_snapshot_rate' => (float) ($item->commission_snapshot_rate ?? 0),
                    'commission_snapshot_fixed' => (float) ($item->commission_snapshot_fixed ?? 0),
                    'commission_snapshot_amount' => (float) ($item->commission_snapshot_amount ?? 0),
                    'commission_snapshot_source' => $item->commission_snapshot_source ?? 'vendor',
                    'commission_snapshot_package_id' => $item->commission_snapshot_package_id,
                    'commission_snapshot_package_name' => $item->commission_snapshot_package_name,
                    'pending_at' => now(),
                    'preparing_at' => null,
                    'out_delivery_at' => null,
                    'delivered_at' => null,
                ]);
            }

            // Generate order code
            $newOrder->update(['order_code' => 'ORD-' . $newOrder->id]);

            return new OneResource($newOrder->load('items'));
        });
    }

    private function resolveVendorCommissionProfile(int $vendorId): array
    {
        if (!Schema::hasTable('vendor_subscriptions')) {
            return [
                'type' => 'percentage',
                'rate' => 0.0,
                'fixed' => 0.0,
                'source' => 'package',
                'package_id' => null,
                'package_name' => null,
            ];
        }

        $today = now()->toDateString();
        $subscription = VendorSubscription::query()
            ->with('package:id,name,commission_rate,commission_per_order')
            ->where('vendor_id', $vendorId)
            ->where('status', 'active')
            ->whereDate('starts_at', '<=', $today)
            ->whereDate('ends_at', '>=', $today)
            ->orderByDesc('ends_at')
            ->first();

        if ($subscription && $subscription->package) {
            $package = $subscription->package;
            $fixed = (float) ($package->commission_per_order ?? 0);
            $type = $fixed > 0 ? 'fixed' : 'percentage';

            return [
                'type' => $type,
                'rate' => $type === 'percentage' ? (float) ($package->commission_rate ?? 0) : 0.0,
                'fixed' => $type === 'fixed' ? $fixed : 0.0,
                'source' => 'package',
                'package_id' => $package->id,
                'package_name' => $package->name,
            ];
        }

        return [
            'type' => 'percentage',
            'rate' => 0.0,
            'fixed' => 0.0,
            'source' => 'package',
            'package_id' => null,
            'package_name' => null,
        ];
    }

    private function resolveCommissionAmount(
        float $lineTotal,
        int $quantity,
        string $commissionType,
        float $commissionRate,
        float $fixedCommission
    ): float {
        $amount = $commissionType === 'fixed'
            ? ($fixedCommission * $quantity)
            : ($lineTotal * ($commissionRate / 100));

        return round($amount, 2);
    }
}
