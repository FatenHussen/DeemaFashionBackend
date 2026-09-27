<?php


namespace App\Http\Resources\Admin\Product;

use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class OneResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_number' => $this->product_number,

            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),
            'full_description' => $this->getTranslations('full_description'),

            ...$this->withCurrency($this->price, 'price'),
            ...$this->withCurrency($this->cost_price, 'cost_price'),
            ...$this->withCurrency($this->price_after_discount, 'price_after_discount'),
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'unit_id' => $this->unit_id,
            'unit_option' => $this->unitOption ? [
                'id' => $this->unitOption->id,
                'name' => $this->unitOption->name,
            ] : null,
            'warranty_period' => $this->warranty_period,
            'warranty_id' => $this->warranty_id,
            'warranty' => $this->warranty ? [
                'id' => $this->warranty->id,
                'name' => $this->warranty->name,
                'name_translations' => $this->warranty->getTranslations('name'),
                'description' => $this->warranty->description,
                'description_translations' => $this->warranty->getTranslations('description'),
            ] : null,
            'expiry_date' => $this->expiry_date?->format('Y-m-d'),
            'stock' => $this->stock,
            'max_purchase_quantity' => $this->max_purchase_quantity,
            'is_visible' => $this->is_visible,
            'is_active' => (bool) $this->is_active,
            'is_restaurant' => (bool) ($this->is_restaurant ?? $this->category?->is_restaurant ?? false),

            'sku' => $this->sku,
            'model' => $this->model,
            'barcode' => $this->barcode,
            'time_prepare' => optional($this->time_prepare)->format('H:i'),
            'delivery_time' => $this->effective_delivery_time,
            'bought_with'           => $this->bought_with_products_list->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                ];
            }),
            'is_instant_delivery' => $this->is_instant_delivery,

            'thumbnail' => $this->thumbnail_url,
            'image' => $this->image_url,

            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'is_restaurant' => (bool) ($this->category?->is_restaurant ?? false),
            ],

            'brand' => $this->brand ? [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
            ] : null,

            'country_id' => $this->country_id,
            'origin_country' => $this->originCountry ? [
                'id' => $this->originCountry->id,
                'name' => $this->originCountry->name,
                'code' => $this->originCountry->code,
            ] : null,

            'sale_country' => $this->saleCountry ? [
                'id' => $this->saleCountry->id,
                'name' => $this->saleCountry->name,
                'icon' => $this->saleCountry->icon_url,
            ] : null,

            'vendor' => $this->vendor ? [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
            ] : null,

            // platform = للموقع · shop = مربوط بمتجر البائع (متجر واحد، بدون فروع)
            'sale_channel' => $this->sale_channel ?? 'platform',

            'approval_status' => $this->approval_status?->value,
            'approval_status_label' => match ($this->approval_status?->value) {
                'pending' => 'قيد الانتظار',
                'approved' => 'مقبول',
                'rejected' => 'مرفوض',
                default => null,
            },
            'rejection_reason' => $this->rejection_reason,

            'variants' => ($this->variants ?? collect())->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->getTranslations('name'),
                    'sku' => $variant->sku,
                    'model' => $variant->model,
                    'barcode' => $variant->barcode,
                    'price' => $variant->price,
                    'price_currencies' => $this->dualCurrency($variant->price),
                    'discount' => $variant->discount ?? 0,
                    'discount_type' => $variant->discount_type ?? 'none',
                    'discount_amount' => $variant->discount_amount,
                    'discount_amount_currencies' => $this->dualCurrency($variant->discount_amount),
                    'price_after_discount' => $variant->price_after_discount,
                    'price_after_discount_currencies' => $this->dualCurrency($variant->price_after_discount),
                    'quantity' => $variant->quantity,
                    'is_trend' => (bool) $variant->is_trend,
                    'is_active' => (bool) $variant->is_active,
                    'attributes_values_ids' => array_values(array_map(
                        'intval',
                        $variant->attributes_values_ids ?? []
                    )),
                    'attributes' => collect($variant->getAttributesWithDetails())->map(function ($attr) {
                        return [
                            'id' => $attr['id'],
                            'attribute' => $attr['category_attribute']['name'] ?? null,
                            'value' => $attr['name'],
                            'display_name' => $attr['display_name'],
                            'type' => $attr['category_attribute']['type'] ?? null,
                            'hex' => $attr['hex'] ?? null,
                            'category_attribute_id' => $attr['category_attribute']['id'] ?? null,
                            'category_attribute' => $attr['category_attribute'] ?? null,
                        ];
                    })->values(),

                    'shops' => ($variant->shopVariants ?? collect())->map(function ($sv) {
                        return [
                            'id' => $sv->id,
                            'shop_id' => $sv->shop_id,
                            'shop_name' => $sv->shop?->name,
                            'is_restaurant' => (bool) ($sv->shop?->is_restaurant ?? false),
                            'city_id' => $sv->shop?->city_id ?? $sv->shop?->area?->city_id,
                            'cost_price' => $sv->cost_price,
                            'cost_price_currencies' => $this->dualCurrency($sv->cost_price),
                        ];
                    })->values(),

                    'images' => ($variant->media ?? collect())->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'url' => $img->url,
                        ];
                    }),
                ];
            })->values(),

            // Category Details
            'category_details' => ($this->categoryDetails ?? collect())->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'name' => $detail->categoryDetail?->name,
                    'value' => $detail->getTranslations('detail_value'),
                ];
            })->values(),

            // Extra Details
            'extra_details' => ($this->extraDetails ?? collect())->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'category' => $detail->category ? [
                        'id' => $detail->category->id,
                        'name' => $detail->category->name,
                    ] : null,
                    'key' => $detail->getTranslations('detail_key') ?? [],
                    'value' => $detail->getTranslations('detail_value') ?? [],
                    'quantity' => (int) ($detail->pivot->quantity ?? 0),
                    'price' => (float) ($detail->pivot->price ?? 0),
                    'price_currencies' => $this->dualCurrency($detail->pivot->price),
                ];
            })->values(),

            // Product Images — thumbnail fills the gallery when no media was uploaded
            'images' => $this->adminImageItems(),

            // SEO Fields
            'seo_title' => $this->getTranslations('seo_title'),
            'seo_description' => $this->getTranslations('seo_description'),
            'seo_keywords' => $this->getTranslations('seo_keywords'),
            'seo_image' => $this->seo_image ? asset('storage/' . $this->seo_image) : null,

            // Badges
            'badges' => ($this->badges ?? collect())->map(function ($badge) {
                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'icon' => $badge->icon ? asset('storage/' . $badge->icon) : null,
                ];
            })->values(),

            // Icons
            'icons' => ($this->icons ?? collect())->map(function ($icon) {
                return [
                    'id' => $icon->id,
                    'name' => $icon->name,
                    'icon' => $icon->image_url,
                    'image' => $icon->image_url,
                ];
            })->values(),

            // Rating
            'rating' => round((float) $this->average_rating, 1),
            'rating_count' => $this->ratings()->count(),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function adminImageItems()
    {
        $images = ($this->media ?? collect())->map(function ($img) {
            return [
                'id' => $img->id,
                'url' => $img->url,
            ];
        })->values();

        if ($images->isEmpty() && $this->thumbnail_url) {
            return collect([[
                'id' => null,
                'url' => $this->thumbnail_url,
            ]]);
        }

        return $images;
    }
}
