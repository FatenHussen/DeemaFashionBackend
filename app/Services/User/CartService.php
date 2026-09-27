<?php

namespace App\Services\User;

use App\Exceptions\CustomExceptionWithMessage;
use App\Exceptions\NotFoundException;
use App\Http\Resources\User\Cart\CartItemResource;
use App\Models\CartItem;
use App\Models\ShopProductVariant;
use App\Models\User;
use App\Traits\HasCurrencyConversion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class CartService
{
    use HasCurrencyConversion;

    public function __construct(private CheckoutPolicyService $checkoutPolicy)
    {
    }

    public function list(int $userId): AnonymousResourceCollection
    {
        $items = CartItem::query()
            ->where('user_id', $userId)
            ->with($this->itemRelations())
            ->orderByDesc('id')
            ->get();

        return CartItemResource::collection($items);
    }

    public function checkoutSummary(int $userId): array
    {
        $items = CartItem::query()
            ->where('user_id', $userId)
            ->with(['shopProductVariant.productVariant.product', 'shopProductVariant.shop'])
            ->get();

        $subtotal = $items->sum(function (CartItem $item) {
            $price = (float) ($item->shopProductVariant?->productVariant?->final_price ?? 0);

            return $price * (int) $item->quantity;
        });

        $shops = $items
            ->map(fn (CartItem $item) => $item->shopProductVariant?->shop)
            ->filter()
            ->unique('id')
            ->values();

        $summary = $this->checkoutPolicy->summarize($this->checkoutPolicy->forShops($shops), $subtotal);
        $summary['delivery_price'] = $this->resolveCartDeliveryPrice($userId, $items);

        return $this->presentCheckout($summary);
    }

    public function presentCheckout(array $summary): array
    {
        $remaining = (float) $summary['remaining_amount'];

        $payload = [
            'source' => $summary['source'],
            'shop_id' => $summary['shop_id'],
            'min_order_amount' => $this->convertPrice($summary['min_order_amount']),
            'subtotal' => $this->convertPrice($summary['subtotal']),
            'remaining_amount' => $this->convertPrice($remaining),
            'can_checkout' => (bool) $summary['can_checkout'],
            'delivery_min_hours' => (int) $summary['delivery_min_hours'],
            'delivery_max_hours' => (int) $summary['delivery_max_hours'],
            'earliest_delivery_at' => $summary['earliest_delivery_at'],
            'instant_only' => (bool) ($summary['instant_only'] ?? false),
            'fulfillment' => $summary['fulfillment'] ?? null,
            'message' => $remaining > 0
                ? __('custom.checkout.min_order_remaining', [
                    'min' => $this->convertFormattedPrice($summary['min_order_amount']),
                    'remaining' => $this->convertFormattedPrice($remaining),
                ])
                : null,
        ];

        if (array_key_exists('delivery_price', $summary)) {
            $payload['delivery_price'] = $summary['delivery_price'] === null
                ? null
                : $this->convertPrice($summary['delivery_price']);
        }

        return $payload;
    }

    /**
     * Distance fee for the cart, using the customer's default address.
     * A restaurant is a shop: same fee, and free delivery returns 0.
     * Hours are not part of this number.
     */
    private function resolveCartDeliveryPrice(int $userId, Collection $items): ?float
    {
        if ($items->isEmpty()) {
            return 0.0;
        }

        $user = User::query()->find($userId);
        if (! $user || ! $user->addresses()->where('is_default', true)->exists()) {
            return null;
        }

        $variantIds = $items->pluck('shop_product_variant_id')->filter()->unique()->values()->all();
        if ($variantIds === []) {
            return null;
        }

        try {
            return CalculateDeliveryPriceService::handle($user, $variantIds);
        } catch (CustomExceptionWithMessage|ModelNotFoundException) {
            return null;
        }
    }

    public function add(int $userId, array $data): CartItemResource
    {
        $shopVariant = $this->findShopVariant((int) $data['shop_product_variant_id']);
        $quantity = (int) ($data['quantity'] ?? 1);

        $item = CartItem::query()->firstOrNew([
            'user_id' => $userId,
            'shop_product_variant_id' => $shopVariant->id,
        ]);

        $nextQty = ($item->exists ? (int) $item->quantity : 0) + $quantity;
        $this->assertQuantity($shopVariant, $nextQty);

        $item->quantity = $nextQty;
        if (array_key_exists('note', $data)) {
            $item->note = $data['note'];
        }
        $item->save();

        return new CartItemResource($item->load($this->itemRelations()));
    }

    public function update(int $userId, int $itemId, array $data): CartItemResource
    {
        $item = $this->findItem($userId, $itemId);
        $shopVariant = $this->findShopVariant((int) $item->shop_product_variant_id);

        if (isset($data['quantity'])) {
            $this->assertQuantity($shopVariant, (int) $data['quantity']);
            $item->quantity = (int) $data['quantity'];
        }

        if (array_key_exists('note', $data)) {
            $item->note = $data['note'];
        }

        $item->save();

        return new CartItemResource($item->load($this->itemRelations()));
    }

    public function remove(int $userId, int $itemId): bool
    {
        $this->findItem($userId, $itemId)->delete();

        return true;
    }

    private function findItem(int $userId, int $itemId): CartItem
    {
        $item = CartItem::query()
            ->where('user_id', $userId)
            ->whereKey($itemId)
            ->first();

        if (!$item) {
            throw new NotFoundException();
        }

        return $item;
    }

    private function findShopVariant(int $id): ShopProductVariant
    {
        $shopVariant = ShopProductVariant::query()
            ->with(['productVariant.product', 'shop'])
            ->find($id);

        if (!$shopVariant) {
            throw new NotFoundException();
        }

        return $shopVariant;
    }

    private function assertQuantity(ShopProductVariant $shopVariant, int $quantity): void
    {
        $stock = (int) ($shopVariant->productVariant?->quantity ?? 0);

        if ($stock < 1 || $quantity > $stock) {
            throw new CustomExceptionWithMessage('custom.cart_quantity_unavailable');
        }

        $maxPurchase = $shopVariant->productVariant?->product?->max_purchase_quantity;
        if ($maxPurchase !== null && $quantity > (int) $maxPurchase) {
            throw new CustomExceptionWithMessage('custom.cart_quantity_unavailable');
        }
    }

    private function itemRelations(): array
    {
        return [
            'shopProductVariant.productVariant.product.media',
            'shopProductVariant.shop',
        ];
    }
}
