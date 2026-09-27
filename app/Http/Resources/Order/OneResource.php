<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Address\AllResource as AddressOneResource;
use App\Http\Resources\Basket\AllResource as BasketAllResource;
use App\Http\Resources\BasketSchedule\AllResource as BasketScheduleAllResource;
use App\Http\Resources\Driver\AllResource as DriverAllResource;
use App\Http\Resources\EndUser\AllResource;
use App\Http\Resources\SectionItem\AllResource as SectionItemAllResource;
use App\Traits\HasCurrencyConversion;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    use HasCurrencyConversion;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $status = $this->status;
        $statusEnum = is_string($status) ? OrderStatus::tryFrom($status) : null;

        return [
            'id' => $this->id,
            'order_code' => $this->order_code ?? $this->id,
            'status' => $status,
            'status_label' => $statusEnum?->labelAr(),
            'rejection_reason' => $this->rejection_reason,
            'cart_type' => $this->cart_type,
            'custom_order_request_id' => $this->custom_order_request_id,
            'has_external_items' => (bool) $this->has_external_items,
            'price_variance_type' => $this->price_variance_type,
            'price_variance_value' => $this->price_variance_value,
            ...(
                $this->approximate_total !== null
                    ? $this->withCurrency($this->approximate_total, 'approximate_total')
                    : ['approximate_total' => null]
            ),
            'is_instant_delivery' => $this->is_instant_delivery,
            'scheduled_delivery_at' => $this->scheduled_delivery_at?->format('Y-m-d H:i'),
            ...$this->deliverySummary(),
            ...$this->withCurrency($this->delivery_price, 'delivery_price'),
            ...$this->withCurrency($this->subtotal, 'subtotal'),
            ...$this->withCurrency($this->total, 'total'),
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
            'promotion_discount' => $this->promotion_discount,
            'automatic_promotions_snapshot' => $this->when(
                ! empty($this->automatic_promotions_snapshot),
                $this->automatic_promotions_snapshot
            ),
            'subscription_discount' => $this->subscription_discount ?? 0,
            'coupon_discount_from_points' => $this->coupon_discount_from_points ?? 0,
            ...$this->withCurrency($this->promotion_discount, 'promotion_discount'),
            ...$this->withCurrency($this->subscription_discount ?? 0, 'subscription_discount'),
            ...$this->withCurrency($this->coupon_discount_from_points ?? 0, 'coupon_discount_from_points'),

            'assigned_by' => $this->assigned_by,

            // Point exchanges used
            'free_delivery_from_points' => $this->free_delivery_from_points ?? false,
            'use_coupon_exchange_id' => $this->used_coupon_exchange_id,
            'use_free_delivery_exchange_id' => $this->used_free_delivery_exchange_id,

            // Subscription benefits used

            'subscription_free_delivery' => $this->subscription_free_delivery ?? false,


            'created_at' => $this->created_at?->toDateTimeString(),
            'affiliate' => [
                'affiliate_rate' => $this->affiliate_rate,
                'affiliate_source' => $this->affiliate_source,
                'affiliate_commission_type' => $this->affiliate_commission_type,
                'affiliate_fixed_commission' => $this->affiliate_fixed_commission,
                'affiliate_commission_amount' => $this->affiliate_commission_amount,
                'affiliate_commission' => $this->affiliate_commission,
            ],
            'timestamps' => [
                'pending_at' => $this->pending_at,
                'preparing_at' => $this->preparing_at,
                'out_delivery_at' => $this->out_delivery_at,
                'delivered_at' => $this->delivered_at,
                'returned_by_user_at' => $this->returned_by_user_at,
            ],
            'user' => AllResource::make($this->user),
            'driver' => DriverAllResource::make($this->driver),
            'user_address' => AddressOneResource::make($this->address),
            'payment_method_id' => $this->payment_method_id,
            'is_paid' => (bool) $this->is_paid,
            'payment_method' => $this->paymentMethod ? [
                'id' => $this->paymentMethod->id,
                'name' => $this->paymentMethod->name,
                'icon' => $this->paymentMethod->icon,

            ] : null,
            // 'baskes' => $this->basket ? BasketAllResource::make($this->basket) : null,
            // 'basket_schedule' => $this->basket_schedule_id ? BasketScheduleAllResource::make($this->basketSchedule) : null,
            'items' => OrderItemResource::collection(
                $this->items
            ),
        ];
    }
}
