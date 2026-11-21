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
            $defaultCurrency = $currencyId
                ? Currency::active()->find($currencyId)
                : CurrencyHelper::getUserCurrency();
            $defaultCurrency ??= Currency::active()->default()->first();
            return [
                'amount'    => null,
                'currency'  => $defaultCurrency?->code ?? 'USD',
                'symbol'    => $defaultCurrency?->symbol ?? '$',
                'formatted' => null,
            ];
        }

        return CurrencyHelper::convertFromBase((float) $priceInBase, $currencyId);
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
            $amount = $code === 'USD' ? round($priceInBase, 2) : null;

            return [
                'amount' => $amount,
                'currency' => $code,
                'symbol' => $fallbackSymbol,
                'formatted' => $amount === null ? null : $fallbackSymbol . ' ' . CurrencyHelper::formatAmount($amount),
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
        // Read current rates even under long-lived HTTP workers.
        return Currency::where('code', $code)->first();
    }
}
