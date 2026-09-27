<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Points System Settings
            [
                'key' => 'point_to_currency_rate',
                'value' => '10',
                'type' => 'number',
                'group' => 'points',
                'title' => 'Point to Currency Rate',
                'description' => 'How much currency value each point represents in USD (e.g., 10 means 1 point = 10 USD)',
                'is_active' => true,
            ],
            [
                'key' => 'currency_symbol',
                'value' => '$',
                'type' => 'string',
                'group' => 'points',
                'title' => 'Currency Symbol',
                'description' => 'The symbol to display for currency (e.g., $, €, SYP)',
                'is_active' => true,
            ],
            [
                'key' => 'currency_code',
                'value' => 'USD',
                'type' => 'string',
                'group' => 'points',
                'title' => 'Currency Code',
                'description' => 'The 3-letter currency code (e.g., USD, EUR, SYP)',
                'is_active' => true,
            ],
            [
                'key' => 'points_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'points',
                'title' => 'Enable Points System',
                'description' => 'Enable or disable the entire points system',
                'is_active' => true,
            ],

            // General App Settings
            [
                'key' => 'app_name',
                'value' => 'Tikmool',
                'type' => 'string',
                'group' => 'general',
                'title' => 'Application Name',
                'description' => 'The name of the application',
                'is_active' => true,
            ],
            [
                'key' => 'app_version',
                'value' => '1.0.0',
                'type' => 'string',
                'group' => 'general',
                'title' => 'Application Version',
                'description' => 'Current version of the application',
                'is_active' => true,
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'general',
                'title' => 'Maintenance Mode',
                'description' => 'Put the application in maintenance mode',
                'is_active' => true,
            ],

            // Delivery Settings
            [
                'key' => 'default_delivery_fee',
                'value' => '5.00',
                'type' => 'number',
                'group' => 'delivery',
                'title' => 'Default Delivery Fee',
                'description' => 'Default delivery fee for orders',
                'is_active' => true,
            ],
            [
                'key' => 'free_delivery_threshold',
                'value' => '50.00',
                'type' => 'number',
                'group' => 'delivery',
                'title' => 'Free Delivery Threshold',
                'description' => 'Minimum order amount for free delivery',
                'is_active' => true,
            ],
            [
                'key' => 'tikmart_min_order_amount',
                'value' => '0',
                'type' => 'number',
                'group' => 'delivery',
                'title' => 'TikMart minimum order',
                'description' => 'Minimum cart amount for TikMart orders. Set from the dashboard.',
                'is_active' => true,
            ],
            [
                'key' => 'tikmart_delivery_min_hours',
                'value' => '24',
                'type' => 'number',
                'group' => 'delivery',
                'title' => 'TikMart earliest delivery (hours)',
                'description' => 'Soonest delivery window for TikMart, for example 24 hours.',
                'is_active' => true,
            ],
            [
                'key' => 'tikmart_delivery_max_hours',
                'value' => '48',
                'type' => 'number',
                'group' => 'delivery',
                'title' => 'TikMart latest delivery (hours)',
                'description' => 'Latest delivery window for TikMart, for example 48 hours.',
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('System settings seeded successfully.');
    }
}
