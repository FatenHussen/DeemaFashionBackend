<?php

namespace App\Helpers;

use App\Models\SystemSetting;
use App\Models\Setting;

class SettingsHelper
{
    /**
     * Get setting value from Setting model (settings table)
     */
    public static function getSetting(string $key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get setting value from SystemSetting model (system_settings table)
     */
    public static function get(string $key, $default = null)
    {
        return SystemSetting::get($key, $default);
    }

    /**
     * Set setting value in SystemSetting
     */
    public static function set(string $key, $value, string $type = 'string'): bool
    {
        return SystemSetting::set($key, $value, $type);
    }

    /**
     * Get points system settings
     * currency_rate comes from Setting model (settings table)
     */
    public static function getPointsSettings(): array
    {
        return [
            'enabled'              => self::get('points_enabled', true),
            'currency_rate'        => (float) self::getSetting('point_to_currency_rate', 100),
            'min_exchange_points'  => self::get('min_exchange_points', 100),
            'max_exchange_points'  => self::get('max_exchange_points', 10000),
            'points_expiry_months' => self::get('points_expiry_months', 12),
            'exchange_enabled'     => self::get('points_exchange_enabled', true),
        ];
    }

    /**
     * Get exchange settings
     */
    public static function getExchangeSettings(): array
    {
        return [
            'min_points'           => self::get('min_exchange_points', 100),
            'max_points'           => self::get('max_exchange_points', 10000),
            'coupon_enabled'       => self::get('exchange_coupon_enabled', true),
            'free_delivery_enabled'=> self::get('exchange_free_delivery_enabled', true),
            'gifts_enabled'        => self::get('exchange_gifts_enabled', true),
            'free_delivery_points' => self::get('free_delivery_points_cost', 200),
            'coupon_discount_rate' => self::get('coupon_discount_rate', 0.01),
        ];
    }

    /**
     * Get delivery settings
     */
    public static function getDeliverySettings(): array
    {
        return [
            'default_fee'    => self::get('default_delivery_fee', 5.00),
            'free_threshold' => self::get('free_delivery_threshold', 50.00),
            'tikmart_min_order_amount' => self::get('tikmart_min_order_amount', 0),
            'tikmart_delivery_min_hours' => self::get('tikmart_delivery_min_hours', 24),
            'tikmart_delivery_max_hours' => self::get('tikmart_delivery_max_hours', 48),
        ];
    }

    /**
     * Get app settings
     */
    public static function getAppSettings(): array
    {
        return [
            'name'             => self::get('app_name', 'Deema Fashion'),
            'version'          => self::get('app_version', '1.0.0'),
            'maintenance_mode' => self::get('maintenance_mode', false),
        ];
    }

    public static function isPointsEnabled(): bool
    {
        return (bool) self::get('points_enabled', true);
    }

    public static function isMaintenanceMode(): bool
    {
        return (bool) self::get('maintenance_mode', false);
    }

    public static function formatPointsToCurrency(int $points): array
    {
        $rate = (float) self::getSetting('point_to_currency_rate', 100);
        $value = $points * $rate;
        $symbol = self::get('currency_symbol', '$');

        return [
            'value'     => $value,
            'formatted' => $symbol . ' ' . number_format($value, 2),
        ];
    }
}
