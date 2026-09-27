<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\EndUser\AllResource as EndUserAllResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Address\AllResource as AddressOneResource;
use App\Enums\OrderStatus;

class AllResource extends JsonResource
{
    use HasCurrencyConversion;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $groupedItems = $this->items
            ->filter(fn ($item) => $item->shopProductVariant?->shop)
            ->groupBy(fn ($item) => $item->shopProductVariant->shop_id)
            ->map(function ($items) {
                $shop = $items->first()->shopProductVariant->shop;

                return [
                    'id' => $shop->id,
                    'shop' => $shop->name,
                    'lat' => $shop->lat,
                    'lng' => $shop->lng,
                ];
            })
            ->values();

        $status = $this->status;
        $statusEnum = is_string($status) ? OrderStatus::tryFrom($status) : null;

        return [
            'id' => $this->id,
            'order_code' => $this->order_code ?? $this->id,
            'status' => $status,
            'status_label' => $statusEnum?->labelAr(),
            'rejection_reason' => $this->rejection_reason,
            'cart_type' => $this->cart_type,
            'is_instant_delivery' => $this->is_instant_delivery,
            'scheduled_delivery_at' => $this->scheduled_delivery_at?->format('Y-m-d H:i'),
            ...$this->deliverySummary(),
            ...$this->withCurrency($this->delivery_price, 'delivery_price'),
            ...$this->withCurrency($this->total, 'total'),
            ...$this->withCurrency($this->subtotal, 'subtotal'),
            // 'total_with_delivery' =>  $this->total + $this->delivery_price,
            'total_quantity' => $this->total_quantity,
            ...$this->withCurrency($this->basket_discount, 'basket_discount'),
            ...$this->withCurrency($this->coupon_discount, 'coupon_discount'),
            'coupon' => $this->when(
                !empty($this->coupon_id) || !empty($this->coupon_code),
                fn() => [
                    'id' => $this->coupon?->id ?? $this->coupon_id,
                    'code' => $this->coupon?->code ?? $this->coupon_code,
                    'name' => $this->coupon?->name,
                    'discount_type' => $this->coupon?->discount_type,
                    'discount_value' => $this->coupon?->discount_value,
                ]
            ),

            'automatic_promotions_snapshot' => $this->when(
                ! empty($this->automatic_promotions_snapshot),
                $this->automatic_promotions_snapshot
            ),

            // Subscription benefits used
            'subscription_discount' => $this->subscription_discount ?? 0,
            'subscription_free_delivery' => $this->subscription_free_delivery ?? false,

            'created_at' => $this->created_at?->toDateTimeString(),
            'assigned_by' => $this->assigned_by,
            'affiliate_rate' => $this->affiliate_rate,
            'affiliate_source' => $this->affiliate_source,
            'affiliate_commission_type' => $this->affiliate_commission_type,
            'affiliate_fixed_commission' => $this->affiliate_fixed_commission,
            'affiliate_commission_amount' => $this->affiliate_commission_amount,
            'affiliate_commission' => $this->affiliate_commission,
            'user' => EndUserAllResource::make($this->user),
            'user_address' => AddressOneResource::make($this->address),
            'shops' => $groupedItems,
            'payment_method' => $this->paymentMethod ? [
                'id' => $this->paymentMethod->id,
                'name' => $this->paymentMethod->name,
                'icon' => $this->paymentMethod->image_url,

            ] : null,

        ];
    }
}
