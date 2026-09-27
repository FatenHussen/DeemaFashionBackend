<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\Request;
use Spatie\Translatable\HasTranslations;

class PopupCampaign extends Model
{
    use HasTranslations;

    public const TYPE_MODAL = 'modal';
    public const TYPE_SLIDE_IN = 'slide_in';
    public const TYPE_FULLSCREEN = 'fullscreen';

    public const TYPES = [
        self::TYPE_MODAL,
        self::TYPE_SLIDE_IN,
        self::TYPE_FULLSCREEN,
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_PAUSED,
        self::STATUS_ARCHIVED,
    ];

    public const BUTTON_CLAIM_OFFER = 'Claim Offer';
    public const BUTTON_SHOP_NOW = 'Shop Now';
    public const BUTTON_SUBSCRIBE = 'Subscribe';
    public const BUTTON_REVEAL_MY_DEAL = 'Reveal My Deal';

    public const BUTTON_OPTIONS = [
        self::BUTTON_CLAIM_OFFER,
        self::BUTTON_SHOP_NOW,
        self::BUTTON_SUBSCRIBE,
        self::BUTTON_REVEAL_MY_DEAL,
    ];

    // Kept for backward compatibility with existing seed data.
    public const CTA_URL = 'url';
    public const CTA_PRODUCT = 'product';
    public const CTA_CATEGORY = 'category';
    public const CTA_FORM = 'form';
    public const CTA_COUPON = 'coupon';

    public const CTA_TYPES = [
        self::CTA_URL,
        self::CTA_PRODUCT,
        self::CTA_CATEGORY,
        self::CTA_FORM,
        self::CTA_COUPON,
    ];

    public const MEDIA_IMAGE = 'image';
    public const MEDIA_VIDEO = 'video';
    public const MEDIA_GIF = 'gif';

    public const MEDIA_TYPES = [
        self::MEDIA_IMAGE,
        self::MEDIA_VIDEO,
        self::MEDIA_GIF,
    ];

    public const AUDIENCE_ALL = 'all_visitors';
    public const AUDIENCE_GUESTS = 'guests_only';
    public const AUDIENCE_LOGGED_IN = 'logged_in_only';
    public const AUDIENCE_NEW = 'new_visitors';
    public const AUDIENCE_RETURNING = 'returning_visitors';

    public const AUDIENCE_TYPES = [
        self::AUDIENCE_ALL,
        self::AUDIENCE_GUESTS,
        self::AUDIENCE_LOGGED_IN,
        self::AUDIENCE_NEW,
        self::AUDIENCE_RETURNING,
    ];

    public const TRIGGER_ON_LOAD = 'on_load';
    public const TRIGGER_DELAY = 'delay';
    public const TRIGGER_SCROLL = 'scroll';
    public const TRIGGER_EXIT_INTENT = 'exit_intent';

    public const TRIGGER_TYPES = [
        self::TRIGGER_ON_LOAD,
        self::TRIGGER_DELAY,
        self::TRIGGER_SCROLL,
        self::TRIGGER_EXIT_INTENT,
    ];

    public const DEFAULT_MAX_IMPRESSIONS = 1;

    public const DEFAULT_SHOW_EVERY = 0;

    protected $fillable = [
        'title',
        'headline',
        'subheadline',
        'description',
        'slug',
        'type',
        'status',
        'priority',

        'button_text',
        'button_url',
        'secondary_button_text',
        'media_type',
        'media_path',
        'form_enabled',
        'form_fields',
        'audience_type',
        'trigger_type',
        'trigger_value',
        'show_every',
        'max_impressions',
    ];

    protected $casts = [
        'title' => 'array',
        'headline' => 'array',
        'subheadline' => 'array',
        'description' => 'array',
        'form_fields' => 'array',
        'form_enabled' => 'boolean',
        'priority' => 'integer',
        'trigger_value' => 'integer',
        'show_every' => 'integer',
        'max_impressions' => 'integer',
    ];

    public array $translatable = [
        'title',
        'headline',
        'subheadline',
        'description',
    ];

    public function getMediaPathAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        return asset('storage/' . $value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOrderedByPriority(Builder $query): Builder
    {
        return $query->orderByDesc('priority');
    }

    public function pages(): BelongsToMany
    {
        return $this->belongsToMany(Page::class)->withTimestamps();
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class)->withTimestamps();
    }

    public function attachableLinks(): HasMany
    {
        return $this->hasMany(PopupCampaignAttachable::class);
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'attachable', 'popup_campaign_attachables');
    }

    public function shops(): MorphToMany
    {
        return $this->morphedByMany(Shop::class, 'attachable', 'popup_campaign_attachables');
    }
    public function restaurants(): MorphToMany
    {
        return $this->morphedByMany(Shop::class, 'attachable', 'popup_campaign_attachables')
            ->where('shops.is_restaurant', true);
    }
    public function serviceProviders(): MorphToMany
    {
        return $this->morphedByMany(Shop::class, 'attachable', 'popup_campaign_attachables')
            ->where('shops.is_service_provider', true);
    }
    public function stores(): MorphToMany
    {
        return $this->morphedByMany(Shop::class, 'attachable', 'popup_campaign_attachables')
            ->where('shops.is_restaurant', false)
            ->where('shops.is_service_provider', false);
    }

    public function recipes(): MorphToMany
    {
        return $this->morphedByMany(Recipe::class, 'attachable', 'popup_campaign_attachables');
    }

    public function baskets(): MorphToMany
    {
        return $this->morphedByMany(Basket::class, 'attachable', 'popup_campaign_attachables');
    }

    public function shopVendorServices(): MorphToMany
    {
        return $this->morphedByMany(ShopVendorService::class, 'attachable', 'popup_campaign_attachables');
    }

    public function loadAttachablesForUserApi(): self
    {
        $this->loadMissing([
            'pages',
            'promotions' => fn($q) => $q->active()->with([
                'pages',
                'products' => fn($q) => $q->where('products.is_active', true)->inCatalogCategory()->with([
                    'category',
                    'vendor',
                    'media',
                    'badges',
                    'country',
                    'variants.shopVariants',
                ]),
                'categories' => fn($q) => $q->visibleToUsers(),
                'stores' => fn($q) => $q->with(['vendor', 'badges']),
                'restaurants' => fn($q) => $q->with(['vendor', 'badges']),
                'serviceProviders' => fn($q) => $q->with(['vendor', 'badges']),
                'vendors' => fn($q) => $q->with(['badges']),
                'shopVendorServices' => fn($q) => $q->with(['shop', 'vendorService.type']),
            ]),
            'products' => fn($q) => $q->where('products.is_active', true)->inCatalogCategory()->with([
                'category',
                'vendor',
                'media',
                'badges',
                'country',
                'variants.shopVariants',
            ]),
            'stores' => fn($q) => $q->with(['vendor', 'badges']),
            'restaurants' => fn($q) => $q->with(['vendor', 'badges']),
            'serviceProviders' => fn($q) => $q->with(['vendor', 'badges']),
            'recipes' => fn($q) => $q->with(['items.shopProductVariant', 'badges', 'media']),
            'baskets' => fn($q) => $q->with([
                'category',
                'categories',
                'items',
                'badges',
                'basketImages',
                'defaultSchedule',
            ]),
            'shopVendorServices' => fn($q) => $q->with(['shop', 'vendorService.type']),
        ]);

        return $this;
    }

    /**
     * When no attachables are linked, the campaign applies everywhere (subject to pages/audience).
     * When attachables exist and no context IDs are provided, allow a generic match so
     * APIs like /popups/active?page_type=home can still return seeded entity payloads.
     * If IDs are provided, require a strict attachable match.
     */
    public function matchesAttachableContext(Request $request): bool
    {
        $linkedCount = (int) ($this->attachable_links_count ?? $this->attachableLinks()->count());
        if ($linkedCount === 0) {
            return true;
        }

        $pairs = array_filter([
            $this->morphPair(Product::class, self::positiveIntOrNull($request->query('product_id'))),
            $this->morphPair(Shop::class, self::positiveIntOrNull($request->query('shop_id'))),
            $this->morphPair(Recipe::class, self::positiveIntOrNull($request->query('recipe_id'))),
            $this->morphPair(Basket::class, self::positiveIntOrNull($request->query('basket_id'))),
            $this->morphPair(ShopVendorService::class, self::positiveIntOrNull($request->query('shop_vendor_service_id'))),
        ]);

        if ($pairs === []) {
            return true;
        }

        return $this->attachableLinks()
            ->where(function ($query) use ($pairs) {
                foreach ($pairs as [$type, $id]) {
                    $query->orWhere(function ($q) use ($type, $id) {
                        $q->where('attachable_type', $type)
                            ->where('attachable_id', $id);
                    });
                }
            })
            ->exists();
    }

    /**
     * @return array{0: string, 1: int}|null
     */
    protected function morphPair(string $class, ?int $id): ?array
    {
        if ($id === null) {
            return null;
        }

        return [(new $class)->getMorphClass(), $id];
    }

    protected static function positiveIntOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $id = (int) $value;

        return $id > 0 ? $id : null;
    }
}
