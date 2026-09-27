<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Models\ProductMedia;
use Spatie\Translatable\HasTranslations;
use App\Models\Favorite;
use App\Support\ScheduleDiscount;
use App\Traits\LogsActivity;
use App\Traits\MatchesRatingMorphTypes;

class Basket extends Model implements Sectionable
{
    use HasFactory, HasTranslations, LogsActivity, MatchesRatingMorphTypes;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'num_varieties',
        'offer_ends_at',
        'price',
        'discount',
        'discount_type',
        'rating',
        'num_sold',
        'image',
        'is_schedule',
        'schedule_id',
        'is_active',
        'delivery_price',
        'has_custom_discount',
    ];

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function popupCampaigns(): MorphToMany
    {
        return $this->morphToMany(PopupCampaign::class, 'attachable', 'popup_campaign_attachables');
    }

    public $translatable = ['name', 'description'];

    protected $casts = [
        'offer_ends_at' => 'date',
        'is_schedule' => 'boolean',
        'has_custom_discount' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'float',
        'discount' => 'float',
        'delivery_price' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'basket_category');
    }

    public function items()
    {
        return $this->hasMany(BasketItem::class, 'basket_id', 'id');
    }

    public function getCalculatedPriceAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    public function getDiscountAmountAttribute()
    {
        return ScheduleDiscount::amount(
            $this->calculated_price,
            $this->resolvedDiscountType(),
            $this->resolvedDiscountValue(),
        );
    }

    public function catalogSchedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function resolvedDiscountType(): ?string
    {
        if ($this->is_schedule && $this->has_custom_discount) {
            return $this->discount_type;
        }

        if ($this->is_schedule) {
            return $this->catalogSchedule?->discount_type
                ?? $this->defaultSchedule?->discount_type
                ?? $this->discount_type;
        }

        return $this->discount_type;
    }

    public function resolvedDiscountValue(): float
    {
        if ($this->is_schedule && $this->has_custom_discount) {
            return (float) $this->discount;
        }

        if ($this->is_schedule) {
            return (float) ($this->catalogSchedule?->discount_value
                ?? $this->defaultSchedule?->discount_value
                ?? $this->discount
                ?? 0);
        }

        return (float) ($this->discount ?? 0);
    }

    public function scheduleIntervalDays(): ?int
    {
        if (!$this->is_schedule) {
            return null;
        }

        $days = $this->catalogSchedule?->interval_days
            ?? $this->defaultSchedule?->number_of_days;

        return $days !== null ? (int) $days : null;
    }

    public function catalogScheduleArray(): ?array
    {
        $schedule = $this->catalogSchedule;

        if (!$this->is_schedule || !$schedule) {
            return null;
        }

        return [
            'id' => $schedule->id,
            'name' => $schedule->name,
            'interval_days' => (int) $schedule->interval_days,
            'discount_type' => $schedule->discount_type,
            'discount_value' => $schedule->discount_value !== null ? (float) $schedule->discount_value : 0,
            'is_active' => (bool) $schedule->is_active,
        ];
    }

    public function getFinalPriceAttribute()
    {
        return $this->calculated_price - $this->discount_amount;
    }

    protected static function booted()
    {
        static::saving(function ($basket) {
            $basket->price = $basket->calculated_price;
        });
    }
    public function  getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        $firstMedia = $this->relationLoaded('basketImages')
            ? $this->basketImages->first()
            : $this->basketImages()->first();

        return $firstMedia?->url;
    }

    public function getImageUrlsAttribute(): array
    {
        $urls = [];

        if ($this->image) {
            $urls[] = asset('storage/' . $this->image);
        }

        $mediaUrls = ($this->relationLoaded('basketImages')
            ? $this->basketImages
            : $this->basketImages()->get())
            ->map(fn($media) => $media->url)
            ->values()
            ->all();

        return array_values(array_unique(array_merge($urls, $mediaUrls)));
    }

    public function media(): MorphMany
    {
        return $this->morphMany(ProductMedia::class, 'mediable');
    }

    public function basketImages(): MorphMany
    {
        return $this->media()
            ->where('collection', 'basket')
            ->orderBy('order');
    }
    public function schedules()
    {
        return $this->hasMany(BasketSchedule::class);
    }

    public function activeSchedule(): HasMany
    {
        return $this->hasMany(BasketSchedule::class)->where('is_active', true);
    }

    public function defaultSchedule()
    {
        return $this->hasOne(BasketSchedule::class)->where('is_default', true);
    }
    public function toSectionArray(): array
    {
        $nextDelivery = null;
        $intervalDays = $this->scheduleIntervalDays();

        if ($this->is_schedule && $intervalDays) {
            $nextDelivery = now()->addDays($intervalDays)->format('Y-m-d');
        }
        $itemsCount = $this->items?->count() ?? 0;

        $categoryNames = $this->categories->pluck('name')->filter()->implode(' - ');

        return [
            'id' => $this->id,

            // naming for section
            'title' => $this->name,
            'desc'  => $this->description,

            // media
            'image' => $this->image_url,
            'images' => $this->image_urls,

            // category
            'category' => $categoryNames !== '' ? $categoryNames : $this->category?->name,

            // pricing
            'original_price' => round($this->calculated_price, 2),
            'discount_value' => $this->resolvedDiscountValue(),
            'discount_type'  => $this->resolvedDiscountType(),
            'discount_amount' => round($this->discount_amount, 2),
            'price_after_discount' => round($this->final_price, 2),

            // stats
            'rating'   => (float) $this->average_rating,
            'num_sold' => (int) $this->num_sold,
            'saving'   => round($this->discount_amount, 2),

            // offer
            'is_on_offer' => $this->offer_ends_at && $this->offer_ends_at->isFuture(),
            'offer_ends_at' => $this->offer_ends_at?->format('Y-m-d'),

            // delivery
            'next_delivery_date' => $nextDelivery,
            'items_count' => $itemsCount,
            'delivery_price' => $this->delivery_price,
            'is_favorite' => (bool) ($this->is_favorite ?? false),

        ];
    }
    public function badges()
    {
        return $this->morphToMany(Badge::class, 'badgeable')->withPivot('position');
    }


    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }

    public function scopeDeepSearch($query, $search)
    {
        $locale = app()->getLocale();
        $keywords = collect(explode(' ', $search))->filter();

        return $query->where(function ($q) use ($keywords, $locale) {

            foreach ($keywords as $word) {

                $q->where(function ($subQuery) use ($word, $locale) {

                    $subQuery
                        ->where("name->$locale", 'like', "%{$word}%");
                });
            }
        });
    }
}
