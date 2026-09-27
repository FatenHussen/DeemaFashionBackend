<?php

namespace App\Http\Resources\UserBasketSchedule;

use App\Http\Resources\Badge\OneResource as BadgeOneResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray($request): array
    {
        $badges = $this->badges ?? collect();

        return [
            'id' => $this->id,
            'name' => $this->localizedText('name'),
            'description' => $this->localizedText('description'),
            'image' => $this->image_url,
            'images' => $this->image_urls ?? [],
            'interval_days' => (int) $this->interval_days,
            'discount_type' => $this->discount_type,
            'discount_value' => round((float) ($this->discount_value ?? 0), 2),
            'is_active' => (bool) $this->is_active,
            'top_badges' => BadgeOneResource::collection(
                $badges->filter(fn ($badge) => ($badge->pivot->position ?? 'top') === 'top')->values()
            ),
            'bottom_badges' => BadgeOneResource::collection(
                $badges->filter(fn ($badge) => ($badge->pivot->position ?? '') === 'bottom')->values()
            ),
        ];
    }

    /** Current locale, then Arabic, then English — empty locale must not drop the schedule. */
    private function localizedText(string $field): ?string
    {
        foreach ([app()->getLocale(), 'ar', 'en'] as $locale) {
            $value = $this->resource->getTranslation($field, $locale, false);
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return null;
    }
}
