<?php

namespace App\Models;

use App\Http\Resources\Product\AllResource;
use App\Traits\LogsActivity;
use App\Traits\MatchesRatingMorphTypes;
use App\Traits\NormalizesBlankStringAttributes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model implements Sectionable
{
    use HasFactory, HasTranslations, SoftDeletes, LogsActivity, MatchesRatingMorphTypes, NormalizesBlankStringAttributes;

    protected $fillable = [
        'product_number',
        'category_id',
        'name',
        'description',
        'full_description',
        'sku',
        'country_id',
        'sale_country_id',
        'model',
        'price',
        'cost_price',
        'quantity',
        'unit',
        'unit_id',
        'warranty_period',
        'warranty_id',
        'stock',
        'max_purchase_quantity',
        'barcode',
        'time_prepare',
        'delivery_time',
        'bought_with',
        'is_instant_delivery',
        'vendor_id',
        'sale_channel',
        'flash_sale_id',
        'discount',
        'discount_type',
        'brand_id',
        'approval_status',
        'rejection_reason',
        'is_visible',
        'is_active',
        'is_restaurant',
        'thumbnail',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'seo_image',
        'expiry_date',
        'expiry_notified_at',
    ];

    public array $translatable = [
        'name',
        'description',
        'full_description',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected $attributes = [
        'description' => null,
        'full_description' => null,
    ];

    protected $casts = [
        'bought_with' => 'array',
        'time_prepare' => 'datetime:H:i',
        'approval_status' => \App\Enums\ProductApprovalStatus::class,
        'is_visible' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'float',
        'cost_price' => 'float',
        'discount' => 'float',
        'is_restaurant' => 'boolean',
        'flash_sale_id' => 'integer',
        'unit_id' => 'integer',
        'expiry_date' => 'date',
        'expiry_notified_at' => 'datetime',
    ];

    protected bool $activeFlashSaleResolved = false;
    protected ?FlashSale $activeFlashSaleCache = null;

    public function setExpiryDateAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['expiry_date'] = null;
            return;
        }

        if (is_string($value)) {
            $normalized = trim($value);

            if ($normalized === '' || $normalized === '"' || $normalized === "'" || strtolower($normalized) === 'null') {
                $this->attributes['expiry_date'] = null;
                return;
            }

            try {
                $this->attributes['expiry_date'] = Carbon::parse($normalized)->format('Y-m-d');
                return;
            } catch (\Throwable $e) {
                $this->attributes['expiry_date'] = null;
                return;
            }
        }

        try {
            $this->attributes['expiry_date'] = Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            $this->attributes['expiry_date'] = null;
        }
    }

    public function setTimePrepareAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['time_prepare'] = null;
            return;
        }

        if (is_string($value)) {
            $normalized = trim($value);
            if ($normalized === '' || strtolower($normalized) === 'null') {
                $this->attributes['time_prepare'] = null;
                return;
            }

            try {
                $this->attributes['time_prepare'] = Carbon::parse($normalized)->format('H:i:s');
                return;
            } catch (\Throwable $e) {
                $this->attributes['time_prepare'] = null;
                return;
            }
        }

        try {
            $this->attributes['time_prepare'] = Carbon::parse($value)->format('H:i:s');
        } catch (\Throwable $e) {
            $this->attributes['time_prepare'] = null;
        }
    }

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

    public function setProductNumberAttribute($value): void
    {
        $this->attributes['product_number'] = $this->normalizeBlankString($value);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Hooks
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        static::saving(function (self $product) {
            if ($product->isDirty('expiry_date')) {
                $product->expiry_notified_at = null;
            }

            if (!$product->category_id) {
                return;
            }

            $isRestaurant = Category::query()
                ->whereKey($product->category_id)
                ->value('is_restaurant');

            $product->is_restaurant = (bool) $isRestaurant;

            if (!$isRestaurant) {
                return;
            }

            $product->country_id = null;
            $product->sale_country_id = null;
            $product->sku = null;
            $product->model = null;
            $product->barcode = null;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (Pricing & Discounts)
    |--------------------------------------------------------------------------
    */
    public function getFinalDiscountAttribute(): array
    {
        if ($flash = $this->getFlashSaleDiscountDetails()) {
            return $flash;
        }

        if ($product = $this->getProductDiscountDetails()) {
            return $product;
        }

        return [
            'source' => 'none',
            'type' => null,
            'value' => 0,
        ];
    }

    public function getPriceAfterDiscountAttribute()
    {
        if (!$this->price) {
            return $this->price;
        }

        $finalDiscount = $this->final_discount;

        if (!$finalDiscount['type'] || $finalDiscount['value'] <= 0) {
            return $this->price;
        }

        return $this->applyDiscount(
            $this->price,
            $finalDiscount['type'],
            $finalDiscount['value']
        );
    }

    public function getEffectiveDeliveryTimeAttribute(): ?string
    {
        // Deema Fashion vendor (id=1) always gets fixed delivery time
        if ($this->vendor_id === 1) {
            return '12-48 ساعة';
        }

        return $this->delivery_time;
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }

    public function getSoldQuantityAttribute()
    {
        return $this->completedOrderItems()->sum('order_items.quantity');
    }

    public function getBoughtWithProductsListAttribute()
    {
        if (!$this->bought_with || !is_array($this->bought_with)) {
            return collect([]);
        }

        return Product::query()
            ->whereIn('id', $this->bought_with)
            ->where('is_active', true)
            ->inCatalogCategory()
            ->get();
    }

    public function getImageUrlAttribute()
    {
        $main = $this->mainMedia();
        if ($main) {
            return $main->url;
        }

        $fromGallery = $this->relationLoaded('media')
            ? $this->media->first()?->url
            : $this->productMedia()->first()?->url;

        return $fromGallery ?? $this->thumbnail_url;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail) {
            return null;
        }

        if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://')) {
            return $this->thumbnail;
        }

        if (str_starts_with($this->thumbnail, '/storage/')) {
            return asset(ltrim($this->thumbnail, '/'));
        }

        return asset('storage/' . ltrim($this->thumbnail, '/'));
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (Ratings)
    |--------------------------------------------------------------------------
    */
    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    /*
    |--------------------------------------------------------------------------
    | Discount Helpers
    |--------------------------------------------------------------------------
    */
    protected function getFlashSaleDiscountDetails(): ?array
    {
        $flashSale = $this->resolveActiveFlashSale();

        if (!$flashSale || !$flashSale->discount) {
            return null;
        }

        return [
            'source' => 'flash_sale',
            'type' => $this->normalizeDiscountType($flashSale->discount_type),
            'value' => (float) $flashSale->discount,
        ];
    }

    protected function getProductDiscountDetails(): ?array
    {
        if (!$this->discount || !$this->discount_type || $this->discount_type === 'none') {
            return null;
        }

        return [
            'source' => 'product',
            'type' => $this->normalizeDiscountType($this->discount_type),
            'value' => (float) $this->discount,
        ];
    }

    protected function resolveActiveFlashSale(): ?FlashSale
    {
        if ($this->activeFlashSaleResolved) {
            return $this->activeFlashSaleCache;
        }

        $flashSale = $this->flashSale;

        $isValidFlashSale = $flashSale
            && $flashSale->is_active
            && $flashSale->end_date
            && $flashSale->end_date->greaterThan(now());

        $this->activeFlashSaleCache = $isValidFlashSale ? $flashSale : null;
        $this->activeFlashSaleResolved = true;

        return $this->activeFlashSaleCache;
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

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unitOption()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function warranty()
    {
        return $this->belongsTo(Warranty::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function popupCampaigns(): MorphToMany
    {
        return $this->morphToMany(PopupCampaign::class, 'attachable', 'popup_campaign_attachables');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function originCountry()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function country()
    {
        return $this->originCountry();
    }

    public function saleCountry()
    {
        return $this->belongsTo(SaleCountry::class, 'sale_country_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function shopVariants()
    {
        return $this->hasMany(ShopProductVariant::class);
    }

    public function categoryDetails()
    {
        return $this->hasMany(ProductCategoryDetail::class);
    }

    public function extraDetails()
    {
        return $this->belongsToMany(ProductExtraDetail::class, 'product_extra_detail_options')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    public function media()
    {
        return $this->morphMany(ProductMedia::class, 'mediable')
            ->where('collection', 'product')
            ->orderBy('order');
    }

    public function productMedia()
    {
        return $this->media()->where('collection', ProductMedia::COLLECTION_PRODUCT);
    }

    public function mainMedia()
    {
        return $this->morphMany(ProductMedia::class, 'mediable')
            ->where('collection', 'main')
            ->orderBy('order')
            ->first();
    }

    /**
     * Gallery URLs, or the thumbnail when the gallery is empty.
     */
    public function listedImageUrls()
    {
        $media = $this->relationLoaded('media') ? $this->media : $this->media()->get();

        $urls = $media->map(fn ($item) => $item->url)->filter()->values();

        if ($urls->isEmpty() && $this->thumbnail_url) {
            $urls->push($this->thumbnail_url);
        }

        return $urls;
    }

    public function badges()
    {
        return $this->morphToMany(Badge::class, 'badgeable')->withPivot('position');
    }

    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }

    public function orderItems()
    {
        return $this->hasManyThrough(
            OrderItem::class,
            ShopProductVariant::class,
            'product_variant_id',
            'shop_product_variant_id',
            'id',
            'id'
        );
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function icons()
    {
        return $this->belongsToMany(Icon::class, 'icon_product');
    }

    /*
    |--------------------------------------------------------------------------
    | Business Helpers
    |--------------------------------------------------------------------------
    */
    public function getRatingBreakdown(): array
    {
        $breakdown = [];

        // Initialize all star ratings with 0 count
        for ($i = 1; $i <= 5; $i++) {
            $breakdown[$i] = 0;
        }

        // Get actual rating counts
        $ratingCounts = $this->ratings()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        // Merge actual counts with initialized array
        foreach ($ratingCounts as $rating => $count) {
            $breakdown[(int) $rating] = (int) $count;
        }

        return $breakdown;
    }
    public function getFinalPriceAttribute(): float
    {
        // 1. Base price (shop overrides product price)
        $price = (float)$this->price;


        // 2. Get product final discount
        $discount = $this->final_discount;

        if (!$discount || !$discount['type'] || $discount['value'] <= 0) {
            return round($price, 2);
        }

        // 3. Apply discount
        if ($discount['type'] === 'percentage') {
            $price -= ($price * ($discount['value'] / 100));
        }

        if ($discount['type'] === 'fixed') {
            $price -= $discount['value'];
        }

        return (float) round(max(0, $price), 2);
    }
    public function syncQuantityFromVariants(): void
    {
        $total = (int) $this->variants()->sum('quantity');

        if ((int) $this->quantity === $total) {
            return;
        }

        $this->update([
            'quantity' => $total,
        ]);
    }

    public function totalSoldQuantity()
    {
        return $this->completedOrderItems()->sum('quantity');
    }

    public function completedOrderItems()
    {
        return $this->orderItems()
            ->whereHas('order', function ($q) {
                $q->where('status', 'completed');
            });
    }

    /**
     * Get shops that have this product (simple version - ID and name only)
     */
    public function getAvailableShops()
    {
        return Shop::whereHas('productVariants.productVariant', function ($query) {
            $query->where('product_id', $this->id);
        })
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();
    }

    public function toSectionArray()
    {
        return AllResource::make($this);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    /**
     * Public catalog: keep products with no category, and products whose category
     * is active and not sitting under an inactive or deleted ancestor.
     */
    public function scopeInCatalogCategory($query)
    {
        $hiddenCategoryIds = Category::idsHiddenFromCatalog();

        if ($hiddenCategoryIds === []) {
            return $query;
        }

        $column = $query->getModel()->getTable() . '.category_id';

        return $query->where(function ($q) use ($hiddenCategoryIds, $column) {
            $q->whereNull($column)
                ->orWhereNotIn($column, $hiddenCategoryIds);
        });
    }

    public function scopeDeepSearch($query, $search)
    {
        $locale = app()->getLocale();
        $keywords = collect(explode(' ', $search))->filter();

        return $query->where(function ($q) use ($keywords, $locale) {

            foreach ($keywords as $word) {

                $q->where(function ($subQuery) use ($word, $locale) {

                    $subQuery
                        // Product name & description
                        ->where("name->$locale", 'like', "%{$word}%")
                        ->orWhere("description->$locale", 'like', "%{$word}%")
                        ->orWhere('sku', 'like', "%{$word}%")
                        ->orWhere('barcode', 'like', "%{$word}%")

                        // Brand
                        ->orWhereHas('brand', function ($brandQuery) use ($word, $locale) {
                            $brandQuery->where("name->$locale", 'like', "%{$word}%");
                        })

                        // Category
                        ->orWhereHas('category', function ($catQuery) use ($word, $locale) {
                            $catQuery->where("name->$locale", 'like', "%{$word}%");
                        })

                        // Vendor
                        ->orWhereHas('vendor', function ($vendorQuery) use ($word, $locale) {
                            $vendorQuery->where("name->$locale", 'like', "%{$word}%");
                        })

                        // Variant attributes
                        // ->orWhereHas('variants.attributeValues', function ($attrQuery) use ($word, $locale) {
                        //     $attrQuery->where("name->$locale", 'like', "%{$word}%");
                        // })

                        // Extra details
                        // ->orWhereHas('extraDetails', function ($extraQuery) use ($word, $locale) {
                        //     $extraQuery
                        //         ->where("detail_key->$locale", 'like', "%{$word}%")
                        //         ->orWhere("detail_value->$locale", 'like', "%{$word}%");
                        // })
                    ;
                });
            }
        });
        // ->where('approval_status', 'approved');
    }
}
