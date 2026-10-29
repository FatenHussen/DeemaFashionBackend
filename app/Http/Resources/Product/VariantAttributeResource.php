<?php

namespace App\Http\Resources\Product;

use App\Support\AttributeColorHex;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantAttributeResource extends JsonResource
{
    public function toArray($request)
    {
        $attribute = $this->categoryAttribute;
        $isColorType = AttributeColorHex::isColorAttribute(
            $attribute?->type,
            $attribute?->getTranslations('name') ?? []
        );

        return [
            'id'        => $this->id,
            'attribute' => $this->categoryAttribute?->name,
            'value'     => $isColorType
                ? ($this->color?->name ?? $this->name)
                : $this->name,
            'type'      => $this->categoryAttribute?->type,
            'hex'       => $isColorType ? AttributeColorHex::forValue($this->resource) : null,
        ];
    }
}
