<?php

namespace App\Http\Resources\Admin\Category\CategoryAttribute;

use App\Support\AttributeColorHex;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'category_id' => $this->category_id,
            'root_category_id' => $this->category_id,
            'name'     => $this->name,
            'category' => $this->category?->name,
            'type' => $this->type,
            'values'   => $this->values->map(fn($value) => [
                'id'   => $value->id,
                'name' => $this->type === 'color'
                    ? ($value->color?->name ?? $value->name)
                    : $value->name,
                'color_id' => $this->type === 'color' ? $value->color_id : null,
                'hex' => $this->type === 'color' ? AttributeColorHex::forValue($value) : null,
            ]),
            'is_active' => $this->is_active,

        ];
    }
}
