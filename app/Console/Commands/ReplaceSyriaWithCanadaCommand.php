<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Currency;
use App\Models\SaleCountry;
use App\Models\Setting;
use Database\Seeders\GovernorateSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReplaceSyriaWithCanadaCommand extends Command
{
    protected $signature = 'locations:replace-canada
                            {--force : Run without confirmation}';

    protected $description = 'Remove Syria geography data and seed Canada provinces/cities/areas. Remaps address FKs so orders are kept.';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will DELETE all governorates/cities/areas and remap addresses to Canada. Continue?')) {
            $this->warn('Cancelled.');

            return self::FAILURE;
        }

        DB::transaction(function () {
            Schema::disableForeignKeyConstraints();

            try {
                $this->info('1) Clearing area references (nullable + pivots)…');
                $this->nullNullableAreaRefs();

                $this->info('2) Wiping governorates / cities / areas…');
                $this->wipeGeography();

                $this->info('3) Seeding Canada provinces / cities / areas…');
                $this->callSilent(GovernorateSeeder::class);

                $defaultAreaId = Area::query()->orderBy('id')->value('id');
                if (! $defaultAreaId) {
                    throw new \RuntimeException('Canada areas were not seeded.');
                }

                $this->info("4) Remapping user addresses / shops to area #{$defaultAreaId}…");
                $this->remapRequiredAreaRefs((int) $defaultAreaId);

                $this->info('5) Setting Canada as default sale country…');
                $this->setCanadaSaleCountry();

                $this->info('6) Ensuring CAD currency + contact phone…');
                $this->ensureCadAndPhone();
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        });

        $this->newLine();
        $this->info('Done. Syria locations removed; Canada is live.');
        $this->line('Governorates: ' . DB::table('governorates')->count());
        $this->line('Cities: ' . DB::table('cities')->count());
        $this->line('Areas: ' . DB::table('areas')->count());

        return self::SUCCESS;
    }

    private function nullNullableAreaRefs(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'area_id')) {
            DB::table('users')->update(['area_id' => null]);
        }

        if (Schema::hasTable('shops') && Schema::hasColumn('shops', 'area_id')) {
            DB::table('shops')->update(['area_id' => null]);
        }

        if (Schema::hasTable('area_pricings')) {
            DB::table('area_pricings')->delete();
        }

        if (Schema::hasTable('store_area')) {
            DB::table('store_area')->delete();
        }
    }

    private function wipeGeography(): void
    {
        // Addresses keep rows (orders depend on them); area_id remapped after seed.
        if (Schema::hasTable('user_addresses') && Schema::hasColumn('user_addresses', 'area_id')) {
            DB::table('user_addresses')->update(['area_id' => 0]);
        }

        DB::table('areas')->delete();
        DB::table('cities')->delete();
        DB::table('governorates')->delete();
    }

    private function remapRequiredAreaRefs(int $defaultAreaId): void
    {
        if (Schema::hasTable('user_addresses') && Schema::hasColumn('user_addresses', 'area_id')) {
            DB::table('user_addresses')->update(['area_id' => $defaultAreaId]);
        }

        if (Schema::hasTable('shops') && Schema::hasColumn('shops', 'area_id')) {
            DB::table('shops')->whereNull('area_id')->update(['area_id' => $defaultAreaId]);
        }
    }

    private function setCanadaSaleCountry(): void
    {
        $canada = SaleCountry::query()
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) = ?", ['Canada'])
            ->first();

        if (! $canada) {
            $canada = SaleCountry::create([
                'name' => ['ar' => 'كندا', 'en' => 'Canada'],
                'icon' => '🇨🇦',
                'is_active' => true,
            ]);
        } else {
            $canada->update(['is_active' => true, 'icon' => $canada->icon ?: '🇨🇦']);
        }

        SaleCountry::query()
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) = ?", ['Syria'])
            ->update(['is_active' => false]);

        // Point products that used Syria to Canada
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'sale_country_id')) {
            $syriaId = SaleCountry::query()
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) = ?", ['Syria'])
                ->value('id');

            if ($syriaId) {
                DB::table('products')
                    ->where('sale_country_id', $syriaId)
                    ->update(['sale_country_id' => $canada->id]);
            }
        }
    }

    private function ensureCadAndPhone(): void
    {
        Currency::firstOrCreate(
            ['code' => 'CAD'],
            [
                'name' => ['en' => 'Canadian Dollar', 'ar' => 'دولار كندي'],
                'symbol' => 'C$',
                'exchange_rate' => 1.370000,
                'is_default' => false,
                'is_active' => true,
            ]
        );

        Currency::query()->where('code', 'CAD')->update(['is_active' => true]);
        Currency::query()->where('code', 'SYP')->update(['is_active' => false]);

        Setting::updateOrCreate(
            ['key' => 'phone'],
            ['value' => '+14165550100', 'type' => 'string']
        );
    }
}
