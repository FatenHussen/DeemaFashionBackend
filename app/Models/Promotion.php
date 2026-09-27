<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Promotion extends Model
{
    use HasFactory, HasTranslations;

    public const TYPES = [
        'simple_discount',
        'spend_x_discount',
        'spend_x_get_gift',
        'spend_x_get_points',
        'free_shipping',
        'spend_x_get_free_shipping',
        'first_order_discount',
        'first_order_free_shipping',
        'first_order_gift',
        'signup_discount',
        'signup_free_shipping',
        'signup_gift',
    ];

    public const MIN_SPEND_TYPES = [
        'spend_x_discount',
        'spend_x_get_gift',
        'spend_x_get_points',
        'spend_x_get_free_shipping',
    ];

    public const GIFT_TYPES = [
        'spend_x_get_gift',
        'first_order_gift',
        'signup_gift',
    ];

    public const AUTO_DISCOUNT_TYPES = [
        'first_order_discount',
        'signup_discount',
    ];

    protected $fillable = [
        'name',
        'description',
        'type',
        'is_active',
        'position',
        'starts_at',
        'ends_at',
        'min_spend',
        'discount_value',
        'discount_type',
        'gift_description',
        'reward_points',
    ];

    public $translatable = ['name', 'description', 'gift_description'];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        // 'name' => 'array',
        // 'description' => 'array',
        'min_spend' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'reward_points' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function pages(): BelongsToMany
    {
        return $this->belongsToMany(Page::class)->withTimestamps();
    }

    public function popupCampaigns(): BelongsToMany
    {
        return $this->belongsToMany(PopupCampaign::class)->withTimestamps();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_products');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'promotion_categories');
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'promotion_shops');
    }

    public function restaurants(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'promotion_shops')
            ->where('shops.is_restaurant', true);
    }
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'promotion_shops')
            ->where('shops.is_restaurant', false)
            ->where('shops.is_service_provider', false);
    }

    public function serviceProviders(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'promotion_shops')
            ->where('shops.is_service_provider', true);
    }


    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'promotion_vendors');
    }

    public function shopVendorServices(): BelongsToMany
    {
        return $this->belongsToMany(ShopVendorService::class, 'promotion_shop_vendor_services');
    }

    public function hasTargeting(): bool
    {
        $this->loadMissing([
            'products:id',
            'categories:id',
            'shops:id',
            'vendors:id',
            'shopVendorServices:id',
        ]);

        return $this->products->isNotEmpty()
            || $this->categories->isNotEmpty()
            || $this->shops->isNotEmpty()
            || $this->vendors->isNotEmpty()
            || $this->shopVendorServices->isNotEmpty();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Promotions visible on a given page slug: linked to that page, or not restricted to any page.
     */
    public function scopeForPageSlug($query, string $pageSlug)
    {
        return $query->where(function ($q) use ($pageSlug) {
            $q->whereHas('pages', fn($p) => $p->where('pages.slug', $pageSlug))
                ->orWhereDoesntHave('pages');
        });
    }
}
