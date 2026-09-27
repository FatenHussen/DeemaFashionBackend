<?php

namespace App\Http\Resources\Admin\UserBasketSchedule;

use App\Http\Resources\UserBasketSchedule\BasketItemResource;
use App\Http\Resources\UserBasketSchedule\ScheduleResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request): array
    {
        $totalPrice = $this->items?->sum(fn($item) => $item->price * $item->quantity) ?? 0;
        $discountValue = $this->schedule?->discount_value ?? 0;
        $discountType = $this->schedule?->discount_type ?? null;

        $discountAmount = 0;
        if ($discountValue > 0) {
            if ($discountType === 'percentage' || $discountType === 'percent') {
                $discountAmount = round($totalPrice * $discountValue / 100, 2);
            } else {
                $discountAmount = round(min($discountValue, $totalPrice), 2);
            }
        }

        return [
            'id' => $this->id,

            // User info (additional for admin)
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
                'image' => $this->user?->image_url,
            ],

            // Basket info (same as user resource)
            'name' => $this->name,
            'is_active' => $this->is_active,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'delivery_time' => $this->formattedDeliveryTime(),
            'next_run_date' => $this->next_run_date?->format('Y-m-d'),
            'num_varieties' => $this->items?->count() ?? 0,
            'original_price' => round($totalPrice, 2),
            'discount_value' => $discountValue,
            'discount_type' => $discountType,
            'discount_amount' => $discountAmount,
            'final_price' => round($totalPrice - $discountAmount, 2),

            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),
            'items' => BasketItemResource::collection($this->whenLoaded('items')),

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
