<?php

namespace App\Http\Resources\Point;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PointRule;
use App\Models\PointTransaction;
use App\Models\Currency;
use App\Helpers\CurrencyHelper;

class PointSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pointsSettings = \App\Helpers\SettingsHelper::getPointsSettings();
        $balance = $this->resource['balance'];

        $currency = CurrencyHelper::getUserCurrency()
            ?? Currency::active()->default()->first();

        // تحويل قيمة النقطة للعملة المختارة (currency_rate مخزن بالدولار)
        $currencyRateBase = $pointsSettings['currency_rate']; // قيمة النقطة بالدولار
        $currencyRate = $currency->convertFromBase($currencyRateBase);
        $currencySymbol = $currency->symbol;
        $currencyCode = $currency->code;

        $estimatedValue = $balance * $currencyRate;

        $nextReward = $balance > 0 ? (ceil($balance / 1000) * 1000) : 1000;

        $nextExpiry = $this->resource['expire_at'];

        $earningRules = $this->getEarningRules();

        return [
            'points' =>  $balance,
            'value' => [
                'point_value'              => "1 pt = " . CurrencyHelper::formatAmount($currencyRate) . " {$currencySymbol}",
                'point_value_base'         => "1 pt = " . CurrencyHelper::formatAmount($currencyRateBase) . " USD",
                'estimated_value'          => CurrencyHelper::formatAmount($estimatedValue) . " {$currencySymbol}",
                'estimated_value_formatted'=> "{$currencySymbol} " . CurrencyHelper::formatAmount($estimatedValue),
                'currency_code'            => $currencyCode,
                'currency_symbol'          => $currencySymbol,
            ],
            'next_reward' => "Next reward at " . number_format($nextReward, 0) . " pts",

            'expiry' => $nextExpiry->format('Y-m-d'),
            'earning_rules' => $earningRules,
        ];
    }

    private function getEarningRules(): array
    {
        $rules = PointRule::where('is_active', true)
            ->orderBy('id')
            ->get();

        return $rules->map(function ($rule) {
            return $rule->title;
        })->toArray();
    }
}
