<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Currency extends Model
{
    use HasTranslations, LogsActivity;
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_default',
        'is_active',
    ];
    public $translatable = ['name'];
    protected $casts = [
        'name' => 'array',
        'exchange_rate' => 'decimal:6',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->name[$locale] ?? $this->name['en'] ?? $this->code;
    }

    // تحويل من الدولار (العملة الأساسية) للعملة الحالية
    public function convertFromBase(?float $amountInBase): ?float
    {
        if ($amountInBase === null) {
            return null;
        }

        // إذا العملة هي الدولار (الأساسية)، نرجع كما هو
        if (strtoupper((string) $this->code) === 'USD') {
            return round($amountInBase, 2);
        }

        return round($amountInBase * $this->exchange_rate, 2);
    }

    // تحويل من العملة الحالية للدولار (العملة الأساسية)
    public function convertToBase(?float $amount): ?float
    {
        if ($amount === null) {
            return null;
        }

        if (strtoupper((string) $this->code) === 'USD') {
            return round($amount, 2);
        }

        return round($amount / $this->exchange_rate, 2);
    }

    // للتوافق مع الكود القديم - تحويل من الدولار
    public function convertFromUSD(?float $amountInBase): ?float
    {
        return $this->convertFromBase($amountInBase);
    }

    // للتوافق مع الكود القديم - تحويل للدولار
    public function convertToUSD(?float $amount): ?float
    {
        return $this->convertToBase($amount);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
