<?php

namespace App\Models;

use App\Enums\RateableType;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Rating extends Model
{
    use LogsActivity;
    protected $fillable = [
        'user_id',
        'rating',
        'order_id',
        'comment',
        'image',
        'is_verified',
        'rateable_type',
        'rateable_id',
    ];

    public function rateable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function getTypeAttribute(): ?string
    {
        return match ($this->rateable_type) {
            'product', 'App\Models\Product' => RateableType::PRODUCT->value,
            'App\Models\Brand' => RateableType::BRAND->value,
            'shop', 'App\Models\Shop' => RateableType::SHOP->value,
            'App\Models\Delivery', 'App\Models\Driver' => RateableType::DELIVERY->value,
            'recipe', 'App\Models\Recipe' => RateableType::RECIPE->value,
            'basket', 'App\Models\Basket' => RateableType::BASKET->value,
            'App\Models\BasketSchedule' => RateableType::SCHEDULED_BASKET->value,
            'App\Models\Order' => RateableType::ORDER->value,
            default => null,
        };
    }
}
