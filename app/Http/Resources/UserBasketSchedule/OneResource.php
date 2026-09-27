<?php

namespace App\Http\Resources\UserBasketSchedule;

use App\Http\Resources\ScheduledBasketAlertResource;
use App\Support\ScheduleDiscount;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request): array
    {
        $items = $this->items ?? collect();
        $totalPrice = $items->sum(fn ($item) => ((float) $item->price) * (int) $item->quantity);
        $totalQuantity = (int) $items->sum('quantity');
        $discountType = $this->schedule?->discount_type;
        $discountValue = (float) ($this->schedule?->discount_value ?? 0);
        $discountAmount = ScheduleDiscount::amount($totalPrice, $discountType, $discountValue);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_draft' => (bool) $this->is_draft,
            'is_active' => $this->is_active,
            'is_paused' => $this->isPaused(),
            'paused_at' => $this->paused_at?->format('Y-m-d H:i:s'),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'delivery_time' => $this->resource->formattedDeliveryTime(),
            'next_run_date' => $this->next_run_date?->format('Y-m-d'),
            'availability' => $this->availability_summary ? [
                'status' => $this->availability_summary['status'],
                'has_issue' => $this->availability_summary['has_issue'],
                'unavailable_items_count' => $this->availability_summary['unavailable_items_count'],
                'warning_items_count' => $this->availability_summary['warning_items_count'],
            ] : null,
            'active_alert' => $this->active_alert
                ? ScheduledBasketAlertResource::make($this->active_alert)
                : null,
            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),
            'items' => BasketItemResource::collection($this->whenLoaded('items')),
            'summary' => [
                'items_count' => $items->count(),
                'total_quantity' => $totalQuantity,
                ...$this->withCurrency($totalPrice, 'original_price'),
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                ...$this->withCurrency($discountAmount, 'discount_amount'),
                ...$this->withCurrency($discountAmount, 'savings'),
                ...$this->withCurrency($totalPrice - $discountAmount, 'final_price'),
            ],
        ];
    }
}
