<?php

namespace Database\Seeders;

use App\Models\SaleCountry;
use Illuminate\Database\Seeder;

class SaleCountrySeeder extends Seeder
{
    /**
     * Seed sale countries with flag icons.
     * Canada is first / default market.
     * Safe to re-run: matches by English name.
     */
    public function run(): void
    {
        foreach ($this->saleCountries() as $country) {
            $existing = SaleCountry::query()
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) = ?", [$country['name']['en']])
                ->first();

            if ($existing) {
                // لا تلمس is_active أو الاسم إذا الأدمن عدّلهم — عبّي icon فقط إن فاضي
                if (blank($existing->icon) && filled($country['icon'])) {
                    $existing->update(['icon' => $country['icon']]);
                }
                continue;
            }

            SaleCountry::create($country);
        }
    }

    /**
     * @return list<array{name: array{ar: string, en: string}, icon: string, is_active: bool}>
     */
    private function saleCountries(): array
    {
        return [
            [
                'name' => ['ar' => 'كندا', 'en' => 'Canada'],
                'icon' => '🇨🇦',
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'السعودية', 'en' => 'Saudi Arabia'],
                'icon' => '🇸🇦',
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'الإمارات', 'en' => 'UAE'],
                'icon' => '🇦🇪',
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'الكويت', 'en' => 'Kuwait'],
                'icon' => '🇰🇼',
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'قطر', 'en' => 'Qatar'],
                'icon' => '🇶🇦',
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'البحرين', 'en' => 'Bahrain'],
                'icon' => '🇧🇭',
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'عمان', 'en' => 'Oman'],
                'icon' => '🇴🇲',
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'الأردن', 'en' => 'Jordan'],
                'icon' => '🇯🇴',
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'لبنان', 'en' => 'Lebanon'],
                'icon' => '🇱🇧',
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
                'icon' => '🇪🇬',
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'العراق', 'en' => 'Iraq'],
                'icon' => '🇮🇶',
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'تركيا', 'en' => 'Turkey'],
                'icon' => '🇹🇷',
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'سوريا', 'en' => 'Syria'],
                'icon' => '🇸🇾',
                'is_active' => false,
            ],
        ];
    }
}
