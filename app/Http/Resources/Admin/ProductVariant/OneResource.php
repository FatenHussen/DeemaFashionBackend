<?php

namespace App\Http\Resources\Admin\ProductVariant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
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
            'discount' => $this->discount ?? 0,
            'discount_type' => $this->discount_type ?? 'none',
            'discount_amount' => $this->discount_amount,
            'price_after_discount' => $this->price_after_discount,
            'quantity' => $this->quantity,
            'product' => [
                'id' => $this->product->id,
                'product_number' => $this->product->product_number,
                'name' => $this->product->name,
                'description' => $this->product->description,
                'price' => $this->product->price,
                'discount' => $this->product->discount,
                'country' => $this->product->country,
                'image' => $this->product->image_url,
                'images' => $this->product->listedImageUrls(),
                'category' => [
                    'id' => $this->product->category->id,
                    'name' => $this->product->category->name,
                ],
                'brand' => $this->product->brand ? [
                    'id' => $this->product->brand->id,
                    'name' => $this->product->brand->name,
                    'logo' => $this->product->brand->logo,
                ] : null,
            ],
            'images' => ($this->media ?? collect())->map(function ($img) {
                return [
                    'id' => $img->id,
                    'url' => $img->url,
                ];
            })->values(),
            'attributes' => $this->getAttributesWithDetails(),
            'attributes_values_ids' => $this->attributes_values_ids,
            'is_trend' => $this->is_trend,
            'is_active' => (bool) $this->is_active,
            'shop_variants' => $this->shopVariants->map(function ($shopVariant) {
                return [
                    'id' => $shopVariant->id,
                    'shop' => [
                        'id' => $shopVariant->shop->id,
                        'name' => $shopVariant->shop->name,
                        'logo' => $shopVariant->shop->logo,
                        'address' => $shopVariant->shop->address,
                        'is_restaurant' => (bool) ($shopVariant->shop->is_restaurant ?? false),
                        'city_id' => $shopVariant->shop->city_id ?? $shopVariant->shop?->area?->city_id,
                    ],
                    'cost_price' => $shopVariant->cost_price,
                    'sku' => $shopVariant->sku,
                    'barcode' => $shopVariant->barcode,
                ];
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
