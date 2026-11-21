<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * exchange_rate = كم وحدة من هذه العملة تساوي 1 دولار أمريكي.
     * الدولار الأمريكي هو عملة التخزين الأساسية (exchange_rate = 1).
     * عملة العرض الافتراضية يحددها الداشبورد وقد تكون عملة أخرى.
     *
     * مثال التحويل:
     *   سعر بالدولار × exchange_rate = سعر بالعملة الأخرى
     *   سعر بالعملة الأخرى ÷ exchange_rate = سعر بالدولار
     */
    public function run(): void
    {
        $currencies = [
            [
                'code'          => 'USD',
                'name'          => ['en' => 'US Dollar', 'ar' => 'دولار أمريكي'],
                'symbol'        => '$',
                'exchange_rate' => 1.000000,   // العملة الأساسية
                'is_default'    => true,
                'is_active'     => true,
            ],
            [
                'code'          => 'SYP',
                'name'          => ['en' => 'Syrian Pound', 'ar' => 'ليرة سورية'],
                'symbol'        => 'ل.س',
                'exchange_rate' => 13000.000000,   // 1 USD ≈ 13000 SYP
                'is_default'    => false,
                'is_active'     => true,
            ],
            [
                'code'          => 'AED',
                'name'          => ['en' => 'UAE Dirham', 'ar' => 'درهم إماراتي'],
                'symbol'        => 'د.إ',
                'exchange_rate' => 3.672500,   // 1 USD ≈ 3.6725 AED
                'is_default'    => false,
                'is_active'     => true,
            ],
            [
                'code'          => 'SAR',
                'name'          => ['en' => 'Saudi Riyal', 'ar' => 'ريال سعودي'],
                'symbol'        => 'ر.س',
                'exchange_rate' => 3.750000,   // 1 USD ≈ 3.75 SAR
                'is_default'    => false,
                'is_active'     => true,
            ],
            [
                'code'          => 'EGP',
                'name'          => ['en' => 'Egyptian Pound', 'ar' => 'جنيه مصري'],
                'symbol'        => 'ج.م',
                'exchange_rate' => 50.000000,   // 1 USD ≈ 50 EGP
                'is_default'    => false,
                'is_active'     => true,
            ],
            [
                'code'          => 'TRY',
                'name'          => ['en' => 'Turkish Lira', 'ar' => 'ليرة تركية'],
                'symbol'        => '₺',
                'exchange_rate' => 38.000000,   // 1 USD ≈ 38 TRY
                'is_default'    => false,
                'is_active'     => true,
            ],
        ];

        DB::transaction(function () use ($currencies) {
            Currency::orderBy('id')->lockForUpdate()->get();
            foreach ($currencies as $currency) {
                // Deployment seed reruns preserve dashboard defaults, rates and status.
                Currency::firstOrCreate(
                    ['code' => $currency['code']],
                    [...$currency, 'is_default' => false]
                );
            }

            if (!Currency::active()->default()->exists()) {
                Currency::where('is_default', true)->update(['is_default' => false]);
                Currency::where('code', 'USD')->update(['is_default' => true, 'is_active' => true]);
            }
        }, 3);
    }
}
