<?php

namespace App\Http\Resources\Product;

use App\Models\AttributeValue;
use App\Support\AttributeColorHex;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class AttributeMapResource extends JsonResource
{
    public function toArray($request)
    {
        $map = [];

        /** @var Collection $variants */
        $variants = $this->resource instanceof Collection
            ? $this->resource
            : collect([$this->resource]);

        $valueIds = $variants
            ->filter(fn ($variant) => $variant->is_active !== false)
            ->flatMap(fn ($variant) => $variant->attributes_values_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $values = $valueIds === []
            ? collect()
            : AttributeValue::with(['categoryAttribute', 'color'])
                ->whereIn('id', $valueIds)
                ->get()
                ->keyBy('id');

        foreach ($variants as $variant) {
            if ($variant->is_active === false) {
                continue;
            }

            foreach ($variant->attributes_values_ids ?? [] as $valueId) {
                $attr = $values->get((int) $valueId);
                $attribute = $attr?->categoryAttribute;
                if (!$attr || !$attribute) {
                    continue;
                }

                $attributeLabel = $attribute->getTranslation('name', app()->getLocale(), false)
                    ?: $attribute->getTranslation('name', 'ar', false)
                    ?: $attribute->getTranslation('name', 'en', false);

                if (!is_string($attributeLabel) || $attributeLabel === '') {
                    continue;
                }

                $isColorType = AttributeColorHex::isColorAttribute(
                    $attribute->type ?? null,
                    $attribute->getTranslations('name')
                );
                $valueName = $isColorType
                    ? ($attr->color?->name ?? $attr->name)
                    : $attr->name;

                if (!is_string($valueName) || $valueName === '') {
                    continue;
                }

                $key = $attribute->id;

                if (!isset($map[$key])) {
                    $map[$key] = [
                        'id'        => $attribute->id,
                        'attribute' => $attributeLabel,
                        'type'      => $attribute->type,
                        'values'    => [],
                        'options'   => [],
                    ];
                }

                if (!in_array($valueName, $map[$key]['values'], true)) {
                    $map[$key]['values'][] = $valueName;
                }

                $already = collect($map[$key]['options'])->contains(fn ($option) => $option['id'] === $attr->id);
                if (!$already) {
                    $map[$key]['options'][] = [
                        'id'   => $attr->id,
                        'name' => $valueName,
                        'hex'  => $isColorType ? AttributeColorHex::forValue($attr) : null,
                    ];
                }
            }
        }

        return array_values($map);
    }
}
