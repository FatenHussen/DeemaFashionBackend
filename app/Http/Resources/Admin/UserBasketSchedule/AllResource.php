<?php

namespace App\Http\Resources\Admin\UserBasketSchedule;

use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray($request)
    {
        // Calculate pricing (same logic as user resource)
        $totalPrice = $this->items?->sum(fn($item) => $item->price * $item->quantity) ?? 0;
        $itemsPreview = ($this->items ?? collect())
            ->map(fn($item) => $item->product?->name)
            ->filter()
            ->take(3)
            ->values();

        $discountValue = $this->schedule?->discount_value ?? 0;
        $discountType = $this->schedule?->discount_type ?? null;

        $discountAmount = 0;

        if ($discountValue > 0) {
            if ($discountType === 'percentage') {
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
            ],

            // Basket info (same as user resource)
            'name' => $this->name,
            'image' => null,
            'num_varieties' => $this->items?->count() ?? 0,
            'items_preview' => $itemsPreview,
            'original_price' => round($totalPrice, 2),
            'discount_value' => $discountValue,
            'discount_type' => $discountType,
            'discount_amount' => $discountAmount,
            'final_price' => round($totalPrice - $discountAmount, 2),

            // Schedule info
            'schedule' => [
                'id' => $this->schedule?->id,
                'name' => $this->schedule?->name,
                'interval_days' => $this->schedule?->interval_days,
            ],

            // Status
            'is_active' => (bool) $this->is_active,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'delivery_time' => $this->formattedDeliveryTime(),
            'next_run_date' => $this->next_run_date?->format('Y-m-d'),

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
