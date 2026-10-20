<?php

namespace App\Helpers;

use App\Models\Currency;

class CurrencyHelper
{
    public static function formatAmount(?float $amount, int $decimals = 2): ?string
    {
        if ($amount === null) {
            return null;
        }

        $formatted = number_format($amount, $decimals, '.', ',');

        return rtrim(rtrim($formatted, '0'), '.');
    }
    /**
     * تحويل السعر من عملة اليوزر إلى الدولار (العملة الأساسية)
     */
    public static function convertToBase(float $price, ?int $currencyId = null): float
    {
        if (!$currencyId) {
            $user = auth('user')->user();
            $currencyId = $user?->currency_id;
        }

        if (!$currencyId) {
            return $price;
        }

        $currency = Currency::find($currencyId);

        if (!$currency || strtoupper((string) $currency->code) === 'USD') {
            return $price;
        }

        return (float) $currency->convertToBase($price);
    }

    /**
     * للتوافق مع الكود القديم
     */
    public static function convertToUSD(float $price, ?int $currencyId = null): float
    {
        return self::convertToBase($price, $currencyId);
    }

    /**
     * تحويل السعر من الدولار (الأساسية) إلى عملة اليوزر
     */
    public static function convertFromBase(float $priceInBase, ?int $currencyId = null): array
    {
        if (!$currencyId) {
            $user = auth('user')->user();
            $currencyId = $user?->currency_id;
        }

        if (!$currencyId) {
            $currency = Currency::default()->first();
        } else {
            $currency = Currency::find($currencyId);
        }

        // fallback للعملة الافتراضية
        if (!$currency) {
            $currency = Currency::default()->first();
        }

        if (!$currency || strtoupper((string) $currency->code) === 'USD') {
            return [
                'amount'    => round($priceInBase, 2),
                'currency'  => $currency?->code ?? 'USD',
                'symbol'    => $currency?->symbol ?? '$',
                'formatted' => ($currency?->symbol ?? '$') . ' ' . self::formatAmount($priceInBase),
            ];
        }

        $convertedAmount = $currency->convertFromBase($priceInBase);

        return [
            'amount'    => $convertedAmount,
            'currency'  => $currency->code,
            'symbol'    => $currency->symbol,
            'formatted' => $currency->symbol . ' ' . self::formatAmount($convertedAmount),
        ];
    }

    /**
     * للتوافق مع الكود القديم
     */
    public static function convertFromUSD(float $priceInBase, ?int $currencyId = null): array
    {
        return self::convertFromBase($priceInBase, $currencyId);
    }

    /**
     * الحصول على عملة اليوزر
     */
    public static function getUserCurrency(): ?Currency
    {
        $user = auth('user')->user();

        if (!$user || !$user->currency_id) {
            return Currency::default()->first();
        }

        return Currency::find($user->currency_id);
    }

    /**
     * تحويل مبلغ بالليرة السورية إلى الدولار (العملة الأساسية للتخزين).
     */
    public static function convertSypToUsd(?float $amountSyp): ?float
    {
        if ($amountSyp === null) {
            return null;
        }

        $syp = Currency::query()->where('code', 'SYP')->first();

        if (!$syp) {
            return round($amountSyp, 6);
        }

        return (float) $syp->convertToBase($amountSyp);
    }

    /**
     * إذا أُرسل السعر بالليرة فقط، حوّله إلى USD وخزّنه في الحقل الأساسي.
     * إن وُجد السعر بالدولار يُفضَّل ويُتجاهل السعر بالليرة.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function applySypPriceInputs(array $data): array
    {
        $pairs = [
            'price' => 'price_syp',
            'cost_price' => 'cost_price_syp',
        ];

        foreach ($pairs as $usdKey => $sypKey) {
            $hasUsd = array_key_exists($usdKey, $data)
                && $data[$usdKey] !== null
                && $data[$usdKey] !== '';
            $hasSyp = array_key_exists($sypKey, $data)
                && $data[$sypKey] !== null
                && $data[$sypKey] !== '';

            if (!$hasUsd && $hasSyp) {
                $data[$usdKey] = self::convertSypToUsd((float) $data[$sypKey]);
            }

            unset($data[$sypKey]);
        }

        if (isset($data['variants']) && is_array($data['variants'])) {
            foreach ($data['variants'] as $i => $variant) {
                if (!is_array($variant)) {
                    continue;
                }
                $data['variants'][$i] = self::applySypPriceInputs($variant);
            }
        }

        return $data;
    }

    /**
     * تحويل نطاق أسعار من عملة اليوزر للدولار
     */
    public static function convertPriceRangeToUSD(?float $minPrice, ?float $maxPrice, ?int $currencyId = null): array
    {
        return [
            'min' => $minPrice ? self::convertToBase($minPrice, $currencyId) : null,
            'max' => $maxPrice ? self::convertToBase($maxPrice, $currencyId) : null,
        ];
    }

    /**
     * للتوافق مع الكود القديم
     */
    public static function convertPriceRangeToBase(?float $minPrice, ?float $maxPrice, ?int $currencyId = null): array
    {
        return self::convertPriceRangeToUSD($minPrice, $maxPrice, $currencyId);
    }
}
