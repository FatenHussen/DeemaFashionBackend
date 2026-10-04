<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(['key' => 'payment_default'], [
            'value' => '1',
            'type'  => 'integer',
        ]);

        // إعدادات النقاط
        Setting::updateOrCreate(['key' => 'point_to_currency_rate'], [
            'value' => '10',   // كل 100 ليرة سورية = نقطة واحدة
            'type'  => 'integer',
        ]);

        // جهات الاتصال
        Setting::updateOrCreate(['key' => 'whts'], [
            'value' => '0999999999',
            'type'  => 'string',
        ]);

        Setting::updateOrCreate(['key' => 'phone'], [
            'value' => '+963940404018',
            'type'  => 'string',
        ]);

        Setting::updateOrCreate(['key' => 'email'], [
            'value' => 'info@deemafashion.com',
            'type'  => 'string',
        ]);

        Setting::updateOrCreate(['key' => 'instagram'], [
            'value' => 'https://instagram.com/deemafashion',
            'type'  => 'string',
        ]);

        Setting::updateOrCreate(['key' => 'facebook'], [
            'value' => 'https://facebook.com/deemafashion',
            'type'  => 'string',
        ]);

        // واجهة اللوجين
        Setting::updateOrCreate(['key' => 'login_image'], [
            'value' => 'settings/logo.png',
            'type'  => 'file',
        ]);

        Setting::updateOrCreate(['key' => 'login_link'], [
            'value' => 'https://example.com/login',
            'type'  => 'string',
        ]);

        Setting::updateOrCreate(['key' => 'quick_action_image'], [
            'value' => 'settings/quick-action.png',
            'type'  => 'file',
        ]);

        // واجهة الترحيب
        Setting::updateOrCreate(['key' => 'welcome_image'], [
            'value' => 'settings/welcome.png',
            'type'  => 'file',
        ]);

        Setting::updateOrCreate(['key' => 'welcome_text'], [
            'value' => [
                'en' => 'Hello',
                'ar' => 'مرحبا',
            ],
            'type' => 'json',
        ]);

        // ألوان الواجهة — Deema Fashion rebrand (magenta / pink)
        Setting::updateOrCreate(['key' => 'main_color'], [
            'value' => '#c720a4',
            'type'  => 'string',
        ]);

        Setting::updateOrCreate(['key' => 'text_color'], [
            'value' => '#1F2937',
            'type'  => 'string',
        ]);

        Setting::updateOrCreate(['key' => 'second_color'], [
            'value' => '#ff1493',
            'type'  => 'string',
        ]);

        Setting::updateOrCreate(['key' => 'dark_main_color'], [
            'value' => '#1a0a16',
            'type'  => 'string',
        ]);

        Setting::updateOrCreate(['key' => 'dark_text_color'], [
            'value' => '#fce7f3',
            'type'  => 'string',
        ]);

        Setting::updateOrCreate(['key' => 'dark_second_color'], [
            'value' => '#a020f0',
            'type'  => 'string',
        ]);

        // قسم الطلب السريع — أضف المفاتيح الناقصة فقط (لا تعِد كتابة قيم الأدمن)
        $quickOrderDefaults = [
            ['key' => 'quick_order_enabled', 'value' => true, 'type' => 'boolean'],
            ['key' => 'quick_order_header_enabled', 'value' => true, 'type' => 'boolean'],
            ['key' => 'quick_order_background_image', 'value' => null, 'type' => 'file'],
            ['key' => 'quick_order_background_color', 'value' => '#FFE8D6', 'type' => 'string'],
            ['key' => 'quick_order_card_background_color', 'value' => '#FFFFFF', 'type' => 'string'],
            ['key' => 'quick_order_card_variant', 'value' => 'horizontal', 'type' => 'string'],
            ['key' => 'quick_order_badge', 'value' => ['ar' => 'طلب عاجل', 'en' => 'Urgent order'], 'type' => 'json'],
            ['key' => 'quick_order_title', 'value' => ['ar' => 'تحتاجه الآن؟', 'en' => 'Need it now?'], 'type' => 'json'],
            ['key' => 'quick_order_subtitle', 'value' => [
                'ar' => 'اكتب ما تريده مثل قائمة السوق. نسعّره، توافق، ونوصّله للباب.',
                'en' => 'Write what you want like a market list. We price it, you approve, we bring it to the door.',
            ], 'type' => 'json'],
            ['key' => 'quick_order_cta', 'value' => ['ar' => 'اطلب الآن', 'en' => 'Order now'], 'type' => 'json'],
            ['key' => 'quick_order_page_ids', 'value' => $this->defaultQuickOrderPageIds(), 'type' => 'json'],
            ['key' => 'quick_order_steps', 'value' => [
                [
                    'number' => 1,
                    'title' => ['ar' => 'اكتبه', 'en' => 'Write it'],
                    'description' => ['ar' => 'قائمتك، بكلماتك', 'en' => 'Your list, in your words'],
                    'icon' => 'edit',
                ],
                [
                    'number' => 2,
                    'title' => ['ar' => 'نسعّره', 'en' => 'We price it'],
                    'description' => ['ar' => 'أسعار واضحة قبل الدفع', 'en' => 'Clear prices before you pay'],
                    'icon' => 'price',
                ],
                [
                    'number' => 3,
                    'title' => ['ar' => 'نوصّل', 'en' => 'We deliver'],
                    'description' => ['ar' => 'للباب بسرعة', 'en' => 'To your door, fast'],
                    'icon' => 'delivery',
                ],
            ], 'type' => 'json'],
        ];

        foreach ($quickOrderDefaults as $row) {
            Setting::firstOrCreate(
                ['key' => $row['key']],
                ['value' => $row['value'], 'type' => $row['type']]
            );
        }
    }

    /**
     * @return list<int>
     */
    private function defaultQuickOrderPageIds(): array
    {
        $homeId = Page::query()->where('slug', 'home')->value('id');

        return $homeId ? [(int) $homeId] : [];
    }
}
