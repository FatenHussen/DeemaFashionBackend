<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserBasketSchedule extends Model implements Sectionable
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        // 'category_id',
        'schedule_id',
        'name',
        'is_active',
        'is_draft',
        'start_date',
        'delivery_time',
        'next_run_date',
        'paused_at',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'is_draft'      => 'boolean',
        'start_date'    => 'date',
        'next_run_date' => 'date',
        'paused_at'     => 'datetime',
    ];

    // ================= Relations =================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // public function category(): BelongsTo
    // {
    //     return $this->belongsTo(Category::class);
    // }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(UserBasketScheduleItem::class);
    }

    // ================= Helper =================
    // public function canAddProduct($product_id): bool
    // {
    //     return $product->category_id === $this->category_id;
    // }

    public function getNextRunDateAttribute()
    {
        if (!$this->start_date || !$this->schedule) {
            return null;
        }

        $startDate = Carbon::parse($this->start_date);
        $today = Carbon::today();

        if ($startDate->greaterThan($today)) {
            return $startDate;
        }

        $intervalDays = (int) $this->schedule->interval_days;

        if ($intervalDays <= 0) {
            return null;
        }

        $daysPassed = $startDate->diffInDays($today);
        $cycles = intdiv($daysPassed, $intervalDays) + 1;

        return $startDate->addDays($cycles * $intervalDays);
    }
    public function toSectionArray(): array
    {
        $totalPrice = $this->items?->sum(
            fn($item) => $item->price * $item->quantity
        ) ?? 0;

        $discountValue = $this->schedule?->discount_value ?? 0;
        $discountType  = $this->schedule?->discount_type ?? null;
        $discountAmount = \App\Support\ScheduleDiscount::amount(
            $totalPrice,
            $discountType,
            $discountValue,
        );

        $finalPrice = round($totalPrice - $discountAmount, 2);
        $itemsCount = $this->items?->count() ?? 0;

        return [
            'id'    => $this->id,

            // section basics
            'title' => $this->name,
            'desc'  => $this->schedule?->name ?? '',

            'image' => null,

            'price' => round($totalPrice, 2),
            'price_after_discount' => $finalPrice,
            'discount' => $discountValue,

            // 'top_badges' => [],
            // 'bottom_badges' => [],
            'items_count' => $itemsCount,

        ];
    }

    // ================= Scopes =================
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_draft', false)->whereNull('paused_at');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('is_draft', false);
    }

    public function scopeDraft($query)
    {
        return $query->where('is_draft', true);
    }

    public function scopePaused($query)
    {
        return $query->whereNotNull('paused_at');
    }

    // ================= Helpers =================
    public function formattedDeliveryTime(): ?string
    {
        $time = $this->getAttributes()['delivery_time'] ?? null;
        if (!is_string($time) || trim($time) === '') {
            return null;
        }

        return Carbon::parse($time)->format('H:i');
    }

    public function isPaused(): bool
    {
        return $this->paused_at !== null;
    }

    public function canResume(): bool
    {
        return $this->isPaused();
    }

    public function canPause(): bool
    {
        return !$this->isPaused();
    }
}
