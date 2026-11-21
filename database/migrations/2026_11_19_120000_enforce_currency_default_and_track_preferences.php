<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->default(null)->change();
            $table->string('currency_preference_source', 16)->nullable();
        });

        // Legacy currency_id was also assigned automatically. Preserve its value
        // for reconciliation without inventing evidence of an explicit choice.
        DB::transaction(function () {
            $currencies = DB::table('currencies')->orderBy('id')->lockForUpdate()->get();
            $active = $currencies->where('is_active', true);
            $default = $active->firstWhere('is_default', true)
                ?? $active->firstWhere('code', 'USD')
                ?? $active->first()
                ?? $currencies->firstWhere('code', 'USD')
                ?? $currencies->first();

            DB::table('currencies')->where('is_default', true)->update(['is_default' => false]);
            if ($default) {
                DB::table('currencies')->where('id', $default->id)->update([
                    'is_active' => true,
                    'is_default' => true,
                ]);
            }
        });

        Schema::table('currencies', function (Blueprint $table) {
            // NULL values can repeat; only the active default has guard value 1.
            $table->unsignedTinyInteger('active_default_guard')
                ->nullable()
                ->virtualAs('case when is_default = 1 and is_active = 1 then 1 else null end');
            $table->unique('active_default_guard');
        });
    }

    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropUnique(['active_default_guard']);
            $table->dropColumn('active_default_guard');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('currency_preference_source');
            $table->unsignedBigInteger('currency_id')->nullable()->default(1)->change();
        });
    }
};
