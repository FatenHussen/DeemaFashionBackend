<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PaymentMethod extends Model
{
    protected static bool $resolvedDefaultIdLoaded = false;
    protected static ?int $resolvedDefaultIdCache = null;

    protected $fillable = [
        'name',
        'code',
        'icon',
        'is_active',
        'config',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];
    public function  getImageUrlAttribute()
    {
        return asset('storage/' . $this->icon);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function configuredDefaultId(): ?int
    {
        $value = Setting::query()
            ->where('key', 'payment_default')
            ->value('value');

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        if (is_array($value)) {
            $value = $value['id'] ?? $value['value'] ?? null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    public static function resolvedDefaultId(): ?int
    {
        if (static::$resolvedDefaultIdLoaded) {
            return static::$resolvedDefaultIdCache;
        }

        $configuredId = static::configuredDefaultId();

        $defaultId = null;

        if ($configuredId) {
            $defaultId = static::query()
                ->active()
                ->whereKey($configuredId)
                ->value('id');
        }

        if (!$defaultId) {
            $defaultId = static::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->value('id');
        }

        static::$resolvedDefaultIdCache = $defaultId ? (int) $defaultId : null;
        static::$resolvedDefaultIdLoaded = true;

        return static::$resolvedDefaultIdCache;
    }

    public static function resolveDefault(): ?self
    {
        $defaultId = static::resolvedDefaultId();

        return $defaultId
            ? static::query()->active()->find($defaultId)
            : null;
    }

    public function isDefault(): bool
    {
        return $this->id === static::resolvedDefaultId();
    }

    public function isCash(): bool
    {
        return $this->code === 'cash';
    }

    public function isStripe(): bool
    {
        return $this->code === 'stripe';
    }

    /**
     * Methods that collect money outside the app and must not be marked paid
     * until a gateway (Stripe webhook / confirmation) says so.
     */
    public function requiresOnlineConfirmation(): bool
    {
        return $this->isStripe();
    }

    public function isPaidOnPlacement(): bool
    {
        if ($this->requiresOnlineConfirmation()) {
            return false;
        }

        return ! $this->isCash();
    }
}
