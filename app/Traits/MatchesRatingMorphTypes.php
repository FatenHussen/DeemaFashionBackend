<?php

namespace App\Traits;

use App\Models\Rating;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Product, shop, recipe, and basket are registered in the morph map, so
 * Eloquent looks up ratings by the short alias (for example "product").
 * Ratings are stored with the full class name. Match both so averages
 * include every saved review.
 */
trait MatchesRatingMorphTypes
{
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'rateable_id')
            ->whereIn('ratings.rateable_type', $this->ratingTypeValues());
    }

    public function ratingTypeValues(): array
    {
        return array_values(array_unique(array_filter([
            $this->getMorphClass(),
            static::class,
        ])));
    }
}
