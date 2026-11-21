<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('languages')->where('code', 'en')->exists()) {
            return;
        }

        DB::table('languages')->where('is_default', true)->update(['is_default' => false]);
        DB::table('languages')->where('code', 'en')->update([
            'is_default' => true,
            'is_active' => true,
        ]);

        Cache::forget('active_locales');
        Cache::forget('languages_formatted');
    }

    public function down(): void
    {
        if (!DB::table('languages')->where('code', 'ar')->exists()) {
            return;
        }

        DB::table('languages')->where('is_default', true)->update(['is_default' => false]);
        DB::table('languages')->where('code', 'ar')->update(['is_default' => true]);

        Cache::forget('active_locales');
        Cache::forget('languages_formatted');
    }
};
