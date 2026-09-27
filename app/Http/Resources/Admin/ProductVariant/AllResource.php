<?php

namespace App\Http\Resources\Admin\ProductVariant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'name' => $this->getTranslations('name'),
            'sku' => $this->sku,
            'model' => $this->model,
            'barcode' => $this->barcode,
            'price' => $this->price,
            'discount' => $this->discount,
            'price_after_discount' => $this->price_after_discount,
            'quantity' => $this->quantity,
            'product' => [
                'id' => $this->product->id,
                'product_number' => $this->product->product_number,
                'name' => $this->product->name,
                'description' => $this->product->description,
                'price' => $this->product->price,
                'image' => $this->product->image_url,
                'category' => [
                    'id' => $this->product->category->id,
                    'name' => $this->product->category->name,
                ],
                'brand' => $this->product->brand ? [
                    'id' => $this->product->brand->id,
                    'name' => $this->product->brand->name,
                ] : null,
            ],
            'variant_image' => $this->media->first()?->url ?? null,
            'attributes' => $this->getAttributesWithDetails(),
            'is_trend' => $this->is_trend,
            'is_active' => (bool) $this->is_active,
            'shop_variants' => $this->shopVariants->map(function ($shopVariant) {
                return [
                    'id' => $shopVariant->id,
                    'shop' => $shopVariant->shop ? [
                        'id' => $shopVariant->shop->id,
                        'name' => $shopVariant->shop->name,
                        'is_restaurant' => (bool) ($shopVariant->shop->is_restaurant ?? false),
                        'city_id' => $shopVariant->shop->city_id ?? $shopVariant->shop?->area?->city_id,
                    ] : null,
                    'cost_price' => $shopVariant->cost_price,
                ];
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
