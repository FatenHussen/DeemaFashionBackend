<?php

namespace App\Services\User;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use App\Services\PointService;
use Illuminate\Support\Collection;

class PromotionService
{
    public const USER_SELECTABLE_TYPES = ['simple_discount', 'spend_x_discount'];

    public const FIRST_ORDER_TYPES = [
        'first_order_discount',
        'first_order_free_shipping',
        'first_order_gift',
    ];

    public const SIGNUP_TYPES = [
        'signup_discount',
        'signup_free_shipping',
        'signup_gift',
    ];

    public const AUTOMATIC_FREE_SHIPPING_TYPES = [
        'free_shipping',
        'spend_x_get_free_shipping',
        'first_order_free_shipping',
        'signup_free_shipping',
    ];

    public const AUTOMATIC_TYPES = [
        'spend_x_get_gift',
        'spend_x_get_points',
        'free_shipping',
        'spend_x_get_free_shipping',
        'first_order_discount',
        'first_order_free_shipping',
        'first_order_gift',
        'signup_discount',
        'signup_free_shipping',
        'signup_gift',
    ];

    /** Order statuses that do not consume a first-order or signup reward. */
    public const IGNORED_ORDER_STATUSES = [
        OrderStatus::CANCELLED->value,
        OrderStatus::CANCELLED_BY_ADMIN->value,
        OrderStatus::REJECTEDBYDELIVERY->value,
        OrderStatus::FAILDDELIVER->value,
        OrderStatus::RETURNED_BY_USER->value,
    ];

    public function getAvailablePromotions(float $subtotal, Collection $items): Collection
    {
        $normalizedItems = $this->normalizeOrderItems($items);

        return Promotion::query()
            ->whereIn('type', self::USER_SELECTABLE_TYPES)
            ->where('is_active', true)
            ->with(['products:id', 'categories:id', 'shops:id', 'vendors:id'])
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('id')
            ->get()
            ->filter(function (Promotion $promotion) use ($subtotal, $normalizedItems) {
                $eligibleSubtotal = $this->resolveEligibleSubtotal($promotion, $subtotal, $normalizedItems);

                if ($promotion->type === 'simple_discount') {
                    return $eligibleSubtotal > 0;
                }

                if ($promotion->type === 'spend_x_discount') {
                    return $eligibleSubtotal >= (float) ($promotion->min_spend ?? 0);
                }

                return false;
            })
            ->values()
            ->map(fn(Promotion $promotion) => [
                'id' => $promotion->id,
                'name' => $promotion->name,
                'description' => $promotion->description,
            ]);
    }

    public function applyDiscountPromotion($orderOrNull, int $promotionId, float $subtotal, ?Collection $items = null): float
    {
        $details = $this->evaluateDiscountPromotion(
            $promotionId,
            $subtotal,
            $items ?? collect()
        );

        return (float) ($details['discount'] ?? 0);
    }

    public function evaluateDiscountPromotion(int $promotionId, float $subtotal, Collection $items): array
    {
        $promotion = Promotion::query()
            ->with(['products:id', 'categories:id', 'shops:id', 'vendors:id'])
            ->find($promotionId);

        if (
            ! $promotion
            || ! $this->isPromotionActive($promotion)
            || ! in_array($promotion->type, self::USER_SELECTABLE_TYPES, true)
        ) {
            return $this->emptyDiscountPromotionResult($promotionId);
        }

        $metrics = $this->computeEligibilityMetrics($this->normalizeOrderItems($items), $promotion);
        $eligibleSubtotal = $metrics['eligible_subtotal'];

        if ($eligibleSubtotal <= 0) {
            return $this->emptyDiscountPromotionResult($promotion->id, $promotion->type, $metrics);
        }

        if (
            $promotion->type === 'spend_x_discount'
            && $eligibleSubtotal < (float) ($promotion->min_spend ?? 0)
        ) {
            return $this->emptyDiscountPromotionResult($promotion->id, $promotion->type, $metrics);
        }

        $discount = $this->calculateDiscountAmount($promotion, $eligibleSubtotal);

        return [
            'id' => $promotion->id,
            'type' => $promotion->type,
            'eligible_subtotal' => round($eligibleSubtotal, 2),
            'discount' => round($discount, 2),
            'eligible_products' => $metrics['eligible_products'],
            'ineligible_products' => $metrics['ineligible_products'],
            'applies' => $discount > 0,
        ];
    }

    public function isProductEligible(Product $product, Promotion $promotion): bool
    {
        $promotion->loadMissing(['products:id', 'categories:id', 'shops:id', 'vendors:id']);

        if (! $promotion->hasTargeting()) {
            return true;
        }

        if ($promotion->products->isNotEmpty() && ! $promotion->products->contains('id', (int) $product->id)) {
            return false;
        }

        if (
            $promotion->categories->isNotEmpty()
            && ! $promotion->categories->contains('id', (int) $product->category_id)
        ) {
            return false;
        }

        if (
            $promotion->vendors->isNotEmpty()
            && ! $promotion->vendors->contains('id', (int) $product->vendor_id)
        ) {
            return false;
        }

        if ($promotion->shops->isNotEmpty()) {
            $shopIds = $promotion->shops->pluck('id')->all();

            $existsInTargetedShops = $product->variants()
                ->whereHas('shopVariants', fn($q) => $q->whereIn('shop_id', $shopIds))
                ->exists();

            if (! $existsInTargetedShops) {
                return false;
            }
        }

        return true;
    }

    public function resolveAutomaticFreeShippingDeliveryPrice(
        float $deliveryPrice,
        ?float $subtotal = null,
        ?Collection $items = null,
        ?User $user = null,
        ?int $excludeOrderId = null
    ): float {
        if ($deliveryPrice <= 0) {
            return $deliveryPrice;
        }

        if (! $this->hasActiveAutomaticFreeShipping($subtotal, $items, $user, $excludeOrderId)) {
            return $deliveryPrice;
        }

        return 0.0;
    }

    public function hasActiveAutomaticFreeShipping(
        ?float $subtotal = null,
        ?Collection $items = null,
        ?User $user = null,
        ?int $excludeOrderId = null
    ): bool {
        return $this->activeAutomaticFreeShippingPromotions($subtotal, $items, $user, $excludeOrderId)->isNotEmpty();
    }

    public function freeShippingPromotionsSnapshot(
        ?float $subtotal = null,
        ?Collection $items = null,
        ?User $user = null,
        ?int $excludeOrderId = null
    ): array {
        $normalizedItems = $this->normalizeOrderItems($items ?? collect());

        return $this->activeAutomaticFreeShippingPromotions($subtotal, $normalizedItems, $user, $excludeOrderId)
            ->map(function (Promotion $p) use ($subtotal, $normalizedItems) {
                return [
                    'promotion_id' => $p->id,
                    'type' => $p->type,
                    'min_spend' => (float) ($p->min_spend ?? 0),
                    'eligible_subtotal' => round($this->resolveEligibleSubtotal($p, $subtotal, $normalizedItems), 2),
                    'name' => $p->getTranslations('name'),
                    'description' => $p->getTranslations('description'),
                ];
            })
            ->values()
            ->all();
    }

    public function compileAutomaticPromotionsSnapshot(
        float $deliveryBeforeAutomaticFreeShipping,
        float $deliveryAfterAutomaticFreeShipping,
        array $automaticOrderPromotionsResult,
        ?float $subtotal = null,
        ?Collection $items = null,
        ?User $user = null,
        ?int $excludeOrderId = null,
        array $automaticDiscounts = []
    ): ?array {
        $freeShipping = null;
        if (
            $deliveryBeforeAutomaticFreeShipping > 0
            && $deliveryAfterAutomaticFreeShipping <= 0
            && $this->hasActiveAutomaticFreeShipping($subtotal, $items, $user, $excludeOrderId)
        ) {
            $freeShipping = [
                'waived_delivery_amount' => round($deliveryBeforeAutomaticFreeShipping, 2),
                'promotions' => $this->freeShippingPromotionsSnapshot($subtotal, $items, $user, $excludeOrderId),
            ];
        }

        $gifts = $automaticOrderPromotionsResult['gifts'] ?? [];
        $pointsAwards = $automaticOrderPromotionsResult['points_awards'] ?? [];
        $pointsAwarded = (int) ($automaticOrderPromotionsResult['points_awarded'] ?? 0);

        $pointsBlock = [
            'total_awarded' => $pointsAwarded,
            'awards' => $pointsAwards,
        ];

        $discountPromotions = $automaticDiscounts['promotions'] ?? [];

        if ($freeShipping === null && $gifts === [] && $pointsAwarded === 0 && $pointsAwards === [] && $discountPromotions === []) {
            return null;
        }

        return [
            'captured_at' => now()->toIso8601String(),
            'free_shipping' => $freeShipping,
            'gifts' => $gifts,
            'points' => $pointsBlock,
            'discounts' => $discountPromotions,
        ];
    }

    /**
     * Automatic discount for first order or account creation.
     * Capped so the combined discount does not exceed the cart subtotal.
     *
     * @return array{discount: float, promotions: list<array<string, mixed>>}
     */
    public function evaluateAutomaticTriggerDiscounts(
        ?User $user,
        float $subtotal,
        Collection $items,
        ?int $excludeOrderId = null,
        float $alreadyAppliedDiscount = 0
    ): array {
        $normalizedItems = $this->normalizeOrderItems($items);
        $remaining = max(0, round($subtotal - $alreadyAppliedDiscount, 2));
        $totalDiscount = 0.0;
        $applied = [];

        if ($remaining <= 0 || $user === null) {
            return ['discount' => 0.0, 'promotions' => []];
        }

        $promotions = $this->activePromotions(Promotion::AUTO_DISCOUNT_TYPES);

        foreach ($promotions as $promotion) {
            if (! $this->qualifiesForTrigger($promotion, $user, $excludeOrderId)) {
                continue;
            }

            $eligibleSubtotal = $this->resolveEligibleSubtotal($promotion, $subtotal, $normalizedItems);
            if ($eligibleSubtotal <= 0) {
                continue;
            }

            $amount = $this->calculateDiscountAmount($promotion, $eligibleSubtotal);
            $amount = min($amount, max(0, $remaining - $totalDiscount));
            if ($amount <= 0) {
                continue;
            }

            $totalDiscount += $amount;
            $applied[] = [
                'promotion_id' => $promotion->id,
                'type' => $promotion->type,
                'discount' => round($amount, 2),
                'discount_type' => $promotion->discount_type,
                'discount_value' => $promotion->discount_value,
                'eligible_subtotal' => round($eligibleSubtotal, 2),
                'name' => $promotion->getTranslations('name'),
            ];
        }

        return [
            'discount' => round($totalDiscount, 2),
            'promotions' => $applied,
        ];
    }

    public function applyAutomaticOrderPromotions(
        $orderOrNull,
        ?int $userId,
        float $subtotal,
        bool $isPreview,
        ?Collection $items = null
    ): array {
        $gifts = [];
        $pointsExpected = 0;
        $pointsAwarded = 0;
        $pointsAwards = [];
        $normalizedItems = $this->normalizeOrderItems($items ?? collect());
        $user = $userId ? User::query()->find($userId) : null;
        $excludeOrderId = $orderOrNull?->id;

        $promotions = $this->activePromotions([
            'spend_x_get_gift',
            'spend_x_get_points',
            'first_order_gift',
            'signup_gift',
        ]);

        foreach ($promotions as $promotion) {
            if (! $promotion instanceof Promotion) {
                continue;
            }

            if (! $this->isPromotionActive($promotion)) {
                continue;
            }

            $eligibleSubtotal = $this->resolveEligibleSubtotal($promotion, $subtotal, $normalizedItems);

            if ($eligibleSubtotal <= 0) {
                continue;
            }

            if (
                in_array($promotion->type, ['spend_x_get_gift', 'spend_x_get_points'], true)
                && $eligibleSubtotal < (float) ($promotion->min_spend ?? 0)
            ) {
                continue;
            }

            if (! $this->qualifiesForTrigger($promotion, $user, $excludeOrderId)) {
                continue;
            }

            if (in_array($promotion->type, Promotion::GIFT_TYPES, true)) {
                $gifts[] = [
                    'promotion_id' => $promotion->id,
                    'type' => $promotion->type,
                    'promotion_title' => $promotion->name,
                    'promotion_name' => $promotion->getTranslations('name'),
                    'gift_description' => $promotion->getTranslations('gift_description'),
                    'eligible_subtotal' => round($eligibleSubtotal, 2),
                ];
            }

            if ($promotion->type === 'spend_x_get_points') {
                $pts = (int) ($promotion->reward_points ?? 0);
                if ($pts <= 0) {
                    continue;
                }

                if ($isPreview) {
                    $pointsExpected += $pts;
                    continue;
                }

                if ($orderOrNull && $userId) {
                    $awarded = $this->awardSpendXGetPointsOnce($orderOrNull, $promotion);
                    $pointsAwarded += $awarded;
                    if ($awarded > 0) {
                        $pointsAwards[] = [
                            'promotion_id' => $promotion->id,
                            'points' => $awarded,
                            'eligible_subtotal' => round($eligibleSubtotal, 2),
                            'name' => $promotion->getTranslations('name'),
                        ];
                    }
                }
            }
        }

        return [
            'gifts' => $gifts,
            'points_expected' => $pointsExpected,
            'points_awarded' => $pointsAwarded,
            'points_awards' => $pointsAwards,
        ];
    }

    protected function awardSpendXGetPointsOnce($order, Promotion $promotion): int
    {
        $points = (int) ($promotion->reward_points ?? 0);

        if ($points <= 0 || ! $order->user_id) {
            return 0;
        }

        $pointService = app(PointService::class);

        $transaction = $pointService->addPointsToWallet(
            $order->user_id,
            $points,
            null,
            'promotion',
            'earned',
            'order',
            $order->id,
            null,
            null,
            "Spend X Get Points promotion #{$promotion->id}"
        );

        return $transaction ? abs($transaction->points) : 0;
    }

    protected function isPromotionActive(Promotion $promotion): bool
    {
        if (! $promotion->is_active) {
            return false;
        }

        if ($promotion->starts_at && $promotion->starts_at->isFuture()) {
            return false;
        }

        if ($promotion->ends_at && $promotion->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    protected function calculateDiscountAmount(Promotion $promotion, float $eligibleSubtotal): float
    {
        if ($eligibleSubtotal <= 0) {
            return 0;
        }

        $discount = 0;

        switch ($promotion->discount_type) {
            case 'percentage':
                $discount = $eligibleSubtotal * ((float) $promotion->discount_value / 100);
                break;
            case 'fixed':
                $discount = (float) $promotion->discount_value;
                break;
            default:
                $discount = 0;
                break;
        }

        return round(max(0, min($discount, $eligibleSubtotal)), 2);
    }

    protected function emptyDiscountPromotionResult(
        int $promotionId,
        ?string $type = null,
        ?array $metrics = null
    ): array {
        return [
            'id' => $promotionId,
            'type' => $type,
            'eligible_subtotal' => round((float) ($metrics['eligible_subtotal'] ?? 0), 2),
            'discount' => 0.0,
            'eligible_products' => $metrics['eligible_products'] ?? [],
            'ineligible_products' => $metrics['ineligible_products'] ?? [],
            'applies' => false,
        ];
    }

    protected function normalizeOrderItems(Collection $items): Collection
    {
        return $items
            ->map(function ($item) {
                $row = is_array($item) ? $item : (array) $item;

                return [
                    'product_id' => isset($row['product_id']) ? (int) $row['product_id'] : null,
                    'category_id' => isset($row['category_id']) ? (int) $row['category_id'] : null,
                    'shop_id' => isset($row['shop_id']) ? (int) $row['shop_id'] : null,
                    'vendor_id' => isset($row['vendor_id']) ? (int) $row['vendor_id'] : null,
                    'quantity' => isset($row['quantity']) ? (float) $row['quantity'] : 0,
                    'unit_price' => isset($row['unit_price']) ? (float) $row['unit_price'] : null,
                    'subtotal' => isset($row['subtotal']) ? (float) $row['subtotal'] : null,
                ];
            })
            ->filter(fn(array $row) => ! empty($row['product_id']))
            ->values();
    }

    protected function computeEligibilityMetrics(Collection $items, Promotion $promotion): array
    {
        if ($items->isEmpty()) {
            return [
                'eligible_subtotal' => 0.0,
                'eligible_products' => [],
                'ineligible_products' => [],
                'eligible_count' => 0,
            ];
        }

        $eligibleSubtotal = 0.0;
        $eligibleProducts = [];
        $ineligibleProducts = [];
        $eligibleCount = 0;

        foreach ($items as $item) {
            if ($this->isOrderItemEligible($item, $promotion)) {
                $eligibleCount++;
                $eligibleSubtotal += $this->resolveItemSubtotal($item);
                $eligibleProducts[] = (int) $item['product_id'];
            } else {
                $ineligibleProducts[] = (int) $item['product_id'];
            }
        }

        return [
            'eligible_subtotal' => round($eligibleSubtotal, 2),
            'eligible_products' => array_values(array_unique($eligibleProducts)),
            'ineligible_products' => array_values(array_unique($ineligibleProducts)),
            'eligible_count' => $eligibleCount,
        ];
    }

    protected function isOrderItemEligible(array $item, Promotion $promotion): bool
    {
        $promotion->loadMissing(['products:id', 'categories:id', 'shops:id', 'vendors:id']);

        if (! $promotion->hasTargeting()) {
            return true;
        }

        if ($promotion->products->isNotEmpty() && ! $promotion->products->contains('id', (int) $item['product_id'])) {
            return false;
        }

        if (
            $promotion->categories->isNotEmpty()
            && ! $promotion->categories->contains('id', (int) ($item['category_id'] ?? 0))
        ) {
            return false;
        }

        if (
            $promotion->shops->isNotEmpty()
            && ! $promotion->shops->contains('id', (int) ($item['shop_id'] ?? 0))
        ) {
            return false;
        }

        if (
            $promotion->vendors->isNotEmpty()
            && ! $promotion->vendors->contains('id', (int) ($item['vendor_id'] ?? 0))
        ) {
            return false;
        }

        return true;
    }

    protected function resolveItemSubtotal(array $item): float
    {
        if (isset($item['unit_price']) && isset($item['quantity'])) {
            return round((float) $item['unit_price'] * (float) $item['quantity'], 2);
        }

        return round((float) ($item['subtotal'] ?? 0), 2);
    }

    protected function resolveEligibleSubtotal(Promotion $promotion, ?float $fallbackSubtotal, Collection $items): float
    {
        if ($items->isEmpty()) {
            return $promotion->hasTargeting() ? 0.0 : (float) ($fallbackSubtotal ?? 0);
        }

        return (float) $this->computeEligibilityMetrics($items, $promotion)['eligible_subtotal'];
    }

    /**
     * @return \Illuminate\Support\Collection<int, Promotion>
     */
    protected function activeAutomaticFreeShippingPromotions(
        ?float $subtotal = null,
        ?Collection $items = null,
        ?User $user = null,
        ?int $excludeOrderId = null
    ): Collection {
        $normalizedItems = $this->normalizeOrderItems($items ?? collect());

        return $this->activePromotions(self::AUTOMATIC_FREE_SHIPPING_TYPES)
            ->filter(function (Promotion $promotion) use ($subtotal, $normalizedItems, $user, $excludeOrderId) {
                $eligibleSubtotal = $this->resolveEligibleSubtotal($promotion, $subtotal, $normalizedItems);

                if ($eligibleSubtotal <= 0) {
                    return false;
                }

                $minSpend = (float) ($promotion->min_spend ?? 0);
                if ($minSpend > 0 && $eligibleSubtotal < $minSpend) {
                    return false;
                }

                return $this->qualifiesForTrigger($promotion, $user, $excludeOrderId);
            })
            ->values();
    }

    /**
     * @param  list<string>  $types
     * @return Collection<int, Promotion>
     */
    protected function activePromotions(array $types): Collection
    {
        return Promotion::query()
            ->whereIn('type', $types)
            ->where('is_active', true)
            ->with(['products:id', 'categories:id', 'shops:id', 'vendors:id'])
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('id')
            ->get();
    }

    protected function qualifiesForTrigger(Promotion $promotion, ?User $user, ?int $excludeOrderId = null): bool
    {
        if (in_array($promotion->type, self::FIRST_ORDER_TYPES, true)) {
            return $user !== null && $this->isFirstOrder($user, $excludeOrderId);
        }

        if (in_array($promotion->type, self::SIGNUP_TYPES, true)) {
            return $user !== null
                && $this->registeredDuringPromotion($user, $promotion)
                && $this->isFirstOrder($user, $excludeOrderId);
        }

        return true;
    }

    protected function isFirstOrder(User $user, ?int $excludeOrderId = null): bool
    {
        return ! Order::query()
            ->where('user_id', $user->id)
            ->when($excludeOrderId, fn ($query) => $query->where('id', '!=', $excludeOrderId))
            ->whereNotIn('status', self::IGNORED_ORDER_STATUSES)
            ->exists();
    }

    protected function registeredDuringPromotion(User $user, Promotion $promotion): bool
    {
        $registeredAt = $user->created_at;
        if ($registeredAt === null) {
            return false;
        }

        $startsAt = $promotion->starts_at ?? $promotion->created_at;
        if ($startsAt && $registeredAt->lt($startsAt)) {
            return false;
        }

        if ($promotion->ends_at && $registeredAt->gt($promotion->ends_at)) {
            return false;
        }

        return true;
    }
}
