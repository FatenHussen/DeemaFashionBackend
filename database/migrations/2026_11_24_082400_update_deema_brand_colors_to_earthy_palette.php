<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Apply Deema Fashion earthy visual identity to stored UI color settings.
     */
    public function up(): void
    {
        $colors = [
            'main_color' => '#5A3A2E',
            'second_color' => '#8B5E4A',
            'text_color' => '#1F1F1F',
            'dark_main_color' => '#D4B39A',
            'dark_second_color' => '#A9B07A',
            'dark_text_color' => '#F7F5F2',
        ];

        foreach ($colors as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => 'string',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        $previous = [
            'main_color' => '#c720a4',
            'second_color' => '#ff1493',
            'text_color' => '#1F2937',
            'dark_main_color' => '#1a0a16',
            'dark_second_color' => '#a020f0',
            'dark_text_color' => '#fce7f3',
        ];

        foreach ($previous as $key => $value) {
            DB::table('settings')->where('key', $key)->update([
                'value' => $value,
                'updated_at' => now(),
            ]);
        }
    }
};
