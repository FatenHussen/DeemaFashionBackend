<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'email_verified_at',
        'phone_verified_at',
        'area_id',
        'image',
        'preferred_payment_method_id',
        'is_active',

        //markter
        'is_affiliate',
        'affiliate_approved',
        'affiliate_id',
        'coupon_id',
        'affiliate_rate',
        'affiliate_commission_type',
        'affiliate_fixed_commission',
        'affiliate_visit_commission_enabled',
        'affiliate_visit_commission_threshold',
        'affiliate_visit_commission_amount',
        'affiliate_visit_rewarded_steps',
        'affiliate_visits',
        'currency_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mainImage',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'is_affiliate' => 'boolean',
        'affiliate_approved' => 'boolean',
        'affiliate_fixed_commission' => 'decimal:2',
        'affiliate_visit_commission_enabled' => 'boolean',
        'affiliate_visit_commission_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'password' => 'hashed'
    ];
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    // public function images()
    // {
    //     return $this->morphMany(Image::class, 'imageable');
    // }

    // protected $appends = ['image_path'];

    // public function mainImage()
    // {
    //     return $this->morphOne(Image::class, 'imageable')->where('is_main', true);
    // }

    // public function getImagePathAttribute()
    // {
    //     return $this->mainImage?->path;
    // }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function marketerCoupon()
    {
        return $this->hasOne(Coupon::class, 'affiliate_id', 'affiliate_id')
            ->where('is_active', true)
            ->latestOfMany();
    }

    public function affiliateProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'affiliate_user_products')
            ->withTimestamps();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'user_id');
    }

    // public function orders()
    // {
    //     return $this->hasMany(Order::class, 'user_id');
    // }

    public function pointWallet()
    {
        return $this->hasOne(PointWallet::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function  getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }


    public function fcmTokens()
    {
        return $this->morphMany(UserToken::class, 'tokenable');
    }
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasActiveSubscription(): bool
    {
        $subscription = $this->relationLoaded('subscription')
            ? $this->getRelation('subscription')
            : $this->subscription()->latest('end_date')->first();

        return (bool) $subscription?->isActive();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function userBasketSchedules()
    {
        return $this->hasMany(UserBasketSchedule::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function preferredPaymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'preferred_payment_method_id');
    }
}
