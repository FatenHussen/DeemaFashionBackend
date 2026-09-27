<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->decimal('min_order_amount', 10, 2)->nullable()->after('is_free_delivery');
            $table->unsignedSmallInteger('delivery_min_hours')->nullable()->after('min_order_amount');
            $table->unsignedSmallInteger('delivery_max_hours')->nullable()->after('delivery_min_hours');
        });

        Schema::table('seller_registrations', function (Blueprint $table) {
            $table->decimal('min_order_amount', 10, 2)->nullable()->after('is_restaurant');
            $table->unsignedSmallInteger('delivery_min_hours')->nullable()->after('min_order_amount');
            $table->unsignedSmallInteger('delivery_max_hours')->nullable()->after('delivery_min_hours');
        });

        $now = now();
        $settings = [
            [
                'key' => 'tikmart_min_order_amount',
                'value' => '0',
                'type' => 'number',
                'group' => 'delivery',
                'title' => 'TikMart minimum order',
                'description' => 'Minimum cart amount for TikMart orders. Set from the dashboard.',
            ],
            [
                'key' => 'tikmart_delivery_min_hours',
                'value' => '24',
                'type' => 'number',
                'group' => 'delivery',
                'title' => 'TikMart earliest delivery (hours)',
                'description' => 'Soonest delivery window for TikMart, for example 24 hours.',
            ],
            [
                'key' => 'tikmart_delivery_max_hours',
                'value' => '48',
                'type' => 'number',
                'group' => 'delivery',
                'title' => 'TikMart latest delivery (hours)',
                'description' => 'Latest delivery window for TikMart, for example 48 hours.',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting + [
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['min_order_amount', 'delivery_min_hours', 'delivery_max_hours']);
        });

        Schema::table('seller_registrations', function (Blueprint $table) {
            $table->dropColumn(['min_order_amount', 'delivery_min_hours', 'delivery_max_hours']);
        });

        DB::table('system_settings')->whereIn('key', [
            'tikmart_min_order_amount',
            'tikmart_delivery_min_hours',
            'tikmart_delivery_max_hours',
        ])->delete();
    }
};
