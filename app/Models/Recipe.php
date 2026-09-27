<?php

namespace App\Models;

use App\Http\Resources\Recipe\AllResource;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Models\Favorite;
use App\Traits\LogsActivity;
use App\Traits\MatchesRatingMorphTypes;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Models\Media;

class Recipe extends Model implements Sectionable
{
    use HasTranslations, LogsActivity, MatchesRatingMorphTypes;

    public $translatable = ['name', 'description', 'video_title', 'video_desc'];

    protected $fillable = [
        'name',
        'description',
        'image',
        'video_url',
        'video_title',
        'video_desc',
        'discount',
        'rating',
        'orders_count',
        'is_active',
        'delivery_price',
        'serves',
        'prepare_time'
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
        'video_title' => 'array',
        'video_desc' => 'array',
    ];


    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function popupCampaigns(): MorphToMany
    {
        return $this->morphToMany(PopupCampaign::class, 'attachable', 'popup_campaign_attachables');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable')
            ->where('collection', 'recipe')
            ->orderBy('order');
    }
    public function items()
    {
        return $this->hasMany(RecipeItem::class);
    }
    public function variants()
    {
        return $this->hasManyThrough(
            ShopProductVariant::class,
            RecipeItem::class,
            'recipe_id',              // FK في recipe_items
            'id',                     // PK في shop_product_variants
            'id',                     // PK في recipes
            'shop_product_variant_id' // FK في recipe_items
        );
    }
    public function steps()
    {
        return $this->hasMany(RecipeStep::class)->orderBy('step_number');
    }
    public function getTotalItemsPrice(): float
    {
        return round($this->items->sum(function ($item) {
            return (float) $item->shopProductVariant?->productVariant?->price * $item->quantity;
        }), 2);
    }

    public function getTotalAfterDiscount(): float
    {
        $total_before_discount = $this->getTotalItemsPrice();
        $discount_percentage = $this->discount ?? 0;

        $total_after_discount = $total_before_discount * (1 - $discount_percentage / 100);

        return round($total_after_discount, 2);
    }

    public function  getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
    public function getImageUrlsAttribute(): array
    {
        $mediaItems = $this->relationLoaded('media')
            ? $this->media
            : $this->media()->get();

        $urls = $mediaItems
            ->pluck('url')
            ->filter()
            ->values()
            ->all();

        if (!empty($urls)) {
            return $urls;
        }

        return !empty($this->image)
            ? [asset('storage/' . $this->image)]
            : [];
    }

    public function badges()
    {
        return $this->morphToMany(Badge::class, 'badgeable')->withPivot('position');
    }
    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }
    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }
    public function toSectionArray()
    {
        // return [
        //     'id'       => $this->id,
        //     'title'     => $this->title,
        //     'desc'     => $this->description,
        //     'image'    => $this->image_url,
        //     'price' => $this->getTotalItemsPrice(),
        //     'price_after_discount' => $this->getTotalAfterDiscount(),
        //     'discount' => $this->discount,
        //     'top_badges' => [],
        //     'bottom_badges' => [],
        // ];
        return AllResource::make($this);
    }

    public function scopeDeepSearch($query, $search)
    {
        $locale = app()->getLocale();
        $keywords = collect(explode(' ', $search))->filter();

        return $query->where(function ($q) use ($keywords, $locale) {

            foreach ($keywords as $word) {

                $q->where(function ($subQuery) use ($word, $locale) {
                    $subQuery
                        ->where("name->$locale", 'like', "%{$word}%")
                        ->orWhere("description->$locale", 'like', "%{$word}%");
                });
            }
        });
    }
}
