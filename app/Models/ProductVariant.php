<?php

namespace App\Models;

use App\Http\Resources\Product\VariantAttributeResource;
use App\Support\AttributeColorHex;
use App\Traits\NormalizesBlankStringAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ProductVariant extends Model
{
    use HasFactory, HasTranslations, SoftDeletes, NormalizesBlankStringAttributes;

    public array $translatable = ['name'];

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'model',
        'barcode',
        'price',
        'discount',
        'discount_type',
        'quantity',
        'attributes_values_ids',
        'is_trend',
        'is_active',
    ];

    protected $casts = [
        'attributes_values_ids' => 'array',
        'is_active' => 'boolean',
        'price' => 'float',
        'discount' => 'float',
        'quantity' => 'integer',
    ];

    public function setSkuAttribute($value): void
    {
        $this->attributes['sku'] = $this->normalizeBlankString($value);
    }

    public function setModelAttribute($value): void
    {
        $this->attributes['model'] = $this->normalizeBlankString($value);
    }

    public function setBarcodeAttribute($value): void
    {
        $this->attributes['barcode'] = $this->normalizeBlankString($value);
    }

    /**
     * Boot the model and register event listeners
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($productVariant) {
            // Cascade to basket_items
            $productVariant->basketItems()->delete();

            // Cascade to shop_product_variants
            $productVariant->shopVariants()->each(function ($shopVariant) {
                $shopVariant->delete();
            });

            // Media is kept on soft delete so a restore keeps its images
            if ($productVariant->isForceDeleting()) {
                $productVariant->media()->each(function ($media) {
                    (new \App\Services\Base\MediaService())->delete($media);
                });
            }
        });

        static::saved(function ($productVariant) {
            $productVariant->product?->syncQuantityFromVariants();
        });

        static::deleted(function ($productVariant) {
            $productVariant->product?->syncQuantityFromVariants();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shopVariants()
    {
        return $this->hasMany(ShopProductVariant::class, 'product_variant_id');
    }

    public function basketItems()
    {
        return $this->hasMany(BasketItem::class, 'variant_id');
    }

    public function getAttributesWithDetails()
    {
        if (empty($this->attributes_values_ids)) {
            return [];
        }

        $attributeValues = AttributeValue::with(['categoryAttribute', 'color'])
            ->whereIn('id', $this->attributes_values_ids)
            ->get();

        return $attributeValues->map(function ($attributeValue) {
            $attribute = $attributeValue->categoryAttribute;
            if (!$attribute) {
                return null;
            }

            $isColorType = AttributeColorHex::isColorAttribute(
                $attribute->type ?? null,
                $attribute->getTranslations('name')
            );
            $attributeName = $attribute->name ?? null;

            if ($isColorType) {
                $colorName = $attributeValue->color?->getTranslation('name', app()->getLocale(), false)
                    ?: $attributeValue->color?->getTranslation('name', 'en', false)
                    ?: $attributeValue->color?->getTranslation('name', 'ar', false)
                    ?: $attributeValue->name;
                $hex = AttributeColorHex::forValue($attributeValue);

                return [
                    'id' => $attributeValue->id,
                    'name' => $colorName,
                    'display_name' => $hex ? ($colorName . ' (' . $hex . ')') : $colorName,
                    'hex' => $hex,
                    'color' => $attributeValue->color ? [
                        'id' => $attributeValue->color->id,
                        'name' => $colorName,
                        'hex' => $hex,
                    ] : null,
                    'category_attribute' => [
                        'id' => $attribute->id,
                        'name' => $attributeName,
                        'type' => $attribute->type ?? null,
                    ],
                ];
            }

            return [
                'id' => $attributeValue->id,
                'name' => $attributeValue->name,
                'display_name' => $attributeValue->name,
                'hex' => null,
                'color' => null,
                'category_attribute' => [
                    'id' => $attribute->id,
                    'name' => $attributeName,
                    'type' => $attribute->type ?? null,
                ],
            ];
        })->filter()->values();
    }

    public function attributeValues()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            null,
            'product_variant_id',
            'attribute_value_id'
        )->whereIn('attribute_values.id', $this->attributes_values_ids ?? []);
    }

    public function shops()
    {
        return $this->hasMany(ShopProductVariant::class, 'product_variant_id');
    }
    public function media()
    {
        return $this->morphMany(\App\Models\ProductMedia::class, 'mediable');
    }

    public function getImagesAttribute()
    {
        return $this->media->count()
            ? $this->media
            : $this->product->media;
    }

    public function getAttributesValuesAttribute()
    {
        return VariantAttributeResource::collection(
            AttributeValue::with(['categoryAttribute', 'color'])
                ->whereIn('id', $this->attributes_values_ids ?? [])
                ->get()
        );
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }

    public function getFinalPriceAttribute(): float
    {
        $price = (float) $this->price;
        $discount = $this->resolveEffectiveDiscount();

        if (!$discount['type'] || $discount['value'] <= 0) {
            return round($price, 2);
        }

        return (float) round(max(0, $this->applyDiscount($price, $discount['type'], $discount['value'])), 2);
    }

    public function getDiscountAmountAttribute(): float
    {
        return (float) round(max(0, ((float) $this->price) - $this->final_price), 2);
    }

    /**
     * Variant discount takes priority; falls back to product flash sale / product discount.
     */
    protected function resolveEffectiveDiscount(): array
    {
        if ($this->discount && $this->discount_type && $this->discount_type !== 'none') {
            return [
                'source' => 'variant',
                'type' => $this->normalizeDiscountType($this->discount_type),
                'value' => (float) $this->discount,
            ];
        }

        $productDiscount = $this->product?->final_discount;

        if ($productDiscount && ($productDiscount['type'] ?? null) && ($productDiscount['value'] ?? 0) > 0) {
            return $productDiscount;
        }

        return [
            'source' => 'none',
            'type' => null,
            'value' => 0,
        ];
    }

    protected function normalizeDiscountType(?string $type): ?string
    {
        if (!$type) {
            return null;
        }

        return $type === 'percent' ? 'percentage' : $type;
    }

    protected function applyDiscount(float $price, string $type, float $value): float
    {
        if ($type === 'percentage') {
            return round($price - ($price * ($value / 100)), 2);
        }

        if ($type === 'fixed') {
            return max(0, round($price - $value, 2));
        }

        return $price;
    }

    public function getPriceAfterDiscountAttribute(): float
    {
        return $this->final_price;
    }
}
