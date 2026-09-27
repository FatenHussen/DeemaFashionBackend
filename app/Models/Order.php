<?php

namespace App\Models;

use App\Enums\CartType;
use App\Enums\OrderStatus;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    use LogsActivity, SoftDeletes;
    protected $fillable = [
        'user_id',
        'driver_id',
        'user_address_id',
        'payment_method_id',
        'is_paid',
        'custom_order_request_id',
        'has_external_items',
        'price_variance_type',
        'price_variance_value',
        'approximate_total',
        'basket_id',
        'basket_schedule_id',
        'is_instant_delivery',
        'scheduled_delivery_at',
        'status',
        'cart_type',
        'delivery_price',
        'original_delivery_price',
        'total_quantity',
        'total',
        'subtotal',
        'basket_discount',            // خصم السلة
        'coupon_discount',            // خصم الكوبون
        'coupon_discount_from_points', // خصم نقاط من الكوبون
        'free_delivery_from_points',  // توصيل مجاني من النقاط
        'coupon_id',
        'coupon_code',
        'subscription_discount',
        'subscription_free_delivery',
        'subscription_points_bonus',
        'promotion_id',
        'promotion_discount',
        'automatic_promotions_snapshot',
        'pause_at',
        'start_todelivery',
        'order_code',
        'assigned_by',
        'rejection_reason',
        'affiliate_id',
        'affiliate_rate',
        'affiliate_source',
        'affiliate_commission_type',
        'affiliate_fixed_commission',
        'affiliate_commission_amount',

    ];
    protected $casts = [
        'automatic_promotions_snapshot' => 'array',
        'is_instant_delivery' => 'boolean',
        'scheduled_delivery_at' => 'datetime',
        'is_paid' => 'boolean',
        'has_external_items' => 'boolean',
        'start_todelivery' => 'boolean',
        'subscription_free_delivery' => 'boolean',
        'pause_at' => 'datetime',
        'waiting_approval_at' => 'datetime',
        'delivery_price' => 'float',
        'original_delivery_price' => 'float',
        'subtotal' => 'float',
        'basket_discount' => 'float',
        'coupon_discount' => 'float',
        'total' => 'float',
        'approximate_total' => 'float',
        'price_variance_value' => 'float',
        'affiliate_rate' => 'decimal:2',
        'affiliate_fixed_commission' => 'decimal:2',
        'affiliate_commission_amount' => 'decimal:2',
    ];

    protected $appends = ['affiliate_commission'];

    //affiliate_commission
    protected function affiliateCommission(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->affiliate_commission_amount !== null) {
                    return round((float) $this->affiliate_commission_amount, 2);
                }

                return $this->affiliate_id && $this->affiliate_rate
                    ? round($this->total * ($this->affiliate_rate / 100), 2)
                    : 0;
            }
        );
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }


    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function customOrderRequest()
    {
        return $this->belongsTo(CustomOrderRequest::class);
    }

    public function driverWalletTransactions()
    {
        return $this->hasMany(DriverWalletTransaction::class);
    }

    public function basket()
    {
        return $this->belongsTo(Basket::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function subscriptionUsageLogs()
    {
        return $this->hasMany(SubscriptionUsageLog::class);
    }

    public function basketSchedule()
    {
        return $this->belongsTo(BasketSchedule::class);
    }

    public function getNextRunDateAttribute(): ?Carbon
    {
        if (
            $this->cart_type !== CartType::SCHEDULE_ADMIN_CART->value ||
            !$this->created_at ||
            !$this->basketSchedule
        ) {
            return null;
        }

        $intervalDays = (int) $this->basketSchedule->number_of_days;

        if ($intervalDays <= 0) {
            return null;
        }

        $startDate = $this->created_at->copy()->startOfDay();
        $today = Carbon::today();

        if ($startDate->greaterThan($today)) {
            return $startDate;
        }

        $daysPassed = $startDate->diffInDays($today);
        $cycles = intdiv($daysPassed, $intervalDays) + 1;

        return $startDate->copy()->addDays($cycles * $intervalDays);
    }

    public function usedCouponExchange()
    {
        return $this->belongsTo(PointExchange::class, 'used_coupon_exchange_id');
    }

    public function usedFreeDeliveryExchange()
    {
        return $this->belongsTo(PointExchange::class, 'used_free_delivery_exchange_id');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    protected static function booted()
    {
        static::created(function ($order) {

            if (!$order->order_code) {

                $order->order_code = 'ORD-' .
                    now()->format('ymd') . '-' .
                    str_pad($order->id, 5, '0', STR_PAD_LEFT);

                $order->saveQuietly();
            }
        });


        static::updating(function ($order) {
            if ($order->isDirty('status')) {
                // Store old status before update
                // $order->_oldStatus = $order->getOriginal('status');

                $timestampsMap = [
                    OrderStatus::PENDING->value      => 'pending_at',
                    OrderStatus::WAITING_APPROVAL->value => 'waiting_approval_at',
                    OrderStatus::PREPARING->value    => 'preparing_at',
                    OrderStatus::OUT_DELIVERY->value => 'out_delivery_at',
                    OrderStatus::DELIVERED->value    => 'delivered_at',
                    OrderStatus::RETURNED_BY_USER->value => 'returned_by_user_at',
                ];

                $field = $timestampsMap[$order->status] ?? null;

                if ($field && is_null($order->$field)) {
                    $order->$field = now();
                }

                if (
                    $order->status === OrderStatus::DELIVERED->value
                    && !$order->is_paid
                ) {
                    $order->loadMissing('paymentMethod');

                    if ($order->paymentMethod?->isCash()) {
                        $order->is_paid = true;
                    }
                }
            }
        });
    }
}
