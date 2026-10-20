<?php

namespace App\Traits;

use App\Helpers\CurrencyHelper;
use App\Models\Currency;

trait HasCurrencyConversion
{
    /**
     * تحويل السعر من الدولار (الأساسية) للعملة المطلوبة
     */
    public function convertPrice($priceInBase, $currencyId = null)
    {
        if ($priceInBase === null) {
            $defaultCurrency = Currency::default()->first();
            return [
                'amount'    => null,
                'currency'  => $defaultCurrency?->code ?? 'USD',
                'symbol'    => $defaultCurrency?->symbol ?? '$',
                'formatted' => null,
            ];
        }

        $priceInBase = (float) $priceInBase;

        if (!$currencyId) {
            $user = auth('user')->user();
            $currencyId = $user?->currency_id;
        }

        $currency = $currencyId
            ? Currency::find($currencyId)
            : Currency::default()->first();

        if (!$currency) {
            $currency = Currency::default()->first();
        }

        if (!$currency || strtoupper((string) $currency->code) === 'USD') {
            return [
                'amount'    => round($priceInBase, 2),
                'currency'  => $currency?->code ?? 'USD',
                'symbol'    => $currency?->symbol ?? '$',
                'formatted' => ($currency?->symbol ?? '$') . ' ' . CurrencyHelper::formatAmount($priceInBase),
            ];
        }

        $convertedAmount = $currency->convertFromBase($priceInBase);

        return [
            'amount'    => $convertedAmount,
            'currency'  => $currency->code,
            'symbol'    => $currency->symbol,
            'formatted' => $currency->symbol . ' ' . CurrencyHelper::formatAmount($convertedAmount),
        ];
    }

    public function convertFormattedPrice($priceInBase, $currencyId = null)
    {
        $converted = $this->convertPrice($priceInBase, $currencyId);
        return $converted['formatted'];
    }

    /**
     * تحويل مصفوفة أسعار
     */
    public function convertPrices(array $prices, $currencyId = null)
    {
        $converted = [];
        foreach ($prices as $key => $price) {
            $converted[$key] = $this->convertPrice($price, $currencyId);
        }
        return $converted;
    }

    /**
     * إضافة معلومات العملة للـ Resource
     */
    protected function withCurrency($priceInBase, $key = 'price')
    {
        $converted = $this->convertPrice($priceInBase);

        return [
            $key                  => $converted['amount'],
            'currency'            => $converted['currency'],
            'currency_symbol'     => $converted['symbol'],
            $key . '_formatted'   => $converted['formatted'],
            $key . '_currencies'  => $this->dualCurrency($priceInBase),
        ];
    }

    /**
     * إرجاع السعر بعملتين ثابتتين (USD و SYP)
     */
    protected function dualCurrency($priceInBase): array
    {
        if ($priceInBase === null) {
            return [
                'USD' => [
                    'amount' => null,
                    'currency' => 'USD',
                    'symbol' => '$',
                    'formatted' => null,
                ],
                'SYP' => [
                    'amount' => null,
                    'currency' => 'SYP',
                    'symbol' => 'SYP',
                    'formatted' => null,
                ],
            ];
        }

        $priceInBase = (float) $priceInBase;

        return [
            'USD' => $this->convertPriceByCode($priceInBase, 'USD', '$'),
            'SYP' => $this->convertPriceByCode($priceInBase, 'SYP', 'SYP'),
        ];
    }

    protected function convertPriceByCode(float $priceInBase, string $code, string $fallbackSymbol): array
    {
        $currency = $this->getCurrencyByCode($code);

        if (!$currency) {
            return [
                'amount' => round($priceInBase, 2),
                'currency' => $code,
                'symbol' => $fallbackSymbol,
                'formatted' => $fallbackSymbol . ' ' . CurrencyHelper::formatAmount($priceInBase),
            ];
        }

        $amount = strtoupper((string) $currency->code) === 'USD'
            ? round($priceInBase, 2)
            : $currency->convertFromBase($priceInBase);

        return [
            'amount' => $amount,
            'currency' => $currency->code,
            'symbol' => $currency->symbol,
            'formatted' => $currency->symbol . ' ' . CurrencyHelper::formatAmount($amount),
        ];
    }

    protected function getCurrencyByCode(string $code): ?Currency
    {
        static $cache = [];

        if (!array_key_exists($code, $cache)) {
            $cache[$code] = Currency::where('code', $code)->first();
        }

        return $cache[$code];
    }
}
