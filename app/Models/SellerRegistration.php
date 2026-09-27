<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SellerRegistration extends Model
{
    use HasFactory;

    protected $table = 'seller_registrations';

    protected $fillable = [
        'email',
        'phone',
        'password',
        'seller_name',
        'store_name',
        'registered_at',
        'address',
        'commercial_register_number',
        'commercial_register_date',
        'country_id',
        'city_id',
        'governorate_id',
        'logo',
        'is_service_provider',
        'is_restaurant',
        'min_order_amount',
        'delivery_min_hours',
        'delivery_max_hours',
        'service_type_ids',
        'status',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'registered_at'            => 'datetime',
        'commercial_register_date' => 'date',
        'is_active'                => 'boolean',
        'is_service_provider'      => 'boolean',
        'is_restaurant'            => 'boolean',
        'min_order_amount'         => 'float',
        'delivery_min_hours'       => 'integer',
        'delivery_max_hours'       => 'integer',
        'service_type_ids'         => 'array',
    ];
    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
