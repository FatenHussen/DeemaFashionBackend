<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // cache()->forget('active_locales');
        // cache()->forget('languages_formatted');

        $languages = [
            [
                'code' => 'en',
                'native_name' => 'English',
                'direction' => 'ltr',
                'is_active' => true,
                'is_default' => true,
                'order' => 1,
                'flag_icon' => '🇺🇸',
            ],
            [
                'code' => 'ar',
                'native_name' => 'العربية',
                'direction' => 'rtl',
                'is_active' => true,
                'is_default' => false,
                'order' => 2,
                'flag_icon' => '🇸🇦',
            ],
            // [
            //     'code' => 'fr',
            //     'native_name' => 'Français',
            //     'direction' => 'ltr',
            //     'is_active' => true,
            //     'is_default' => false,
            //     'order' => 3,
            //     'flag_icon' => '🇫🇷',
            // ],
           
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }

        // if (app()->environment('local')) {
        //     Language::factory()->count(5)->create();
        // }
    }
}
