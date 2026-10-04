<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Vendor;
use App\Services\Admin\ProductService;
use Illuminate\Database\Seeder;

/**
 * System vendor for site products (sale_channel=platform).
 * Safe to re-run — does not wipe existing vendors.
 */
class PlatformVendorSeeder extends Seeder
{
    public function run(): void
    {
        $id = ProductService::PLATFORM_VENDOR_ID;

        $vendor = Vendor::withTrashed()->find($id);

        if ($vendor) {
            if ($vendor->trashed()) {
                $vendor->restore();
            }
            if (!$vendor->is_active) {
                $vendor->forceFill(['is_active' => true])->save();
            }
        } else {
            $vendor = new Vendor();
            $vendor->id = $id;
            $vendor->fill([
                'name' => [
                    'ar' => 'ديما فاشن',
                    'en' => 'Deema Fashion',
                ],
                'owner_name' => 'Deema Fashion Admin',
                'owner_phone' => '0990000000',
                'commercial_register' => 'CR-DEEMAFASHION-001',
                'contract_date' => now()->subYear()->toDateString(),
                'contract_number' => 'DEEMA-PLATFORM-001',
                'contract_duration_months' => 36,
                'commission_rate' => 10.00,
                'is_active' => true,
                'ratings_count' => 0,
                'ratings_sum' => 0,
            ]);
            $vendor->save();
        }

        $hasDefaultShop = Shop::query()
            ->where('vendor_id', $vendor->id)
            ->where('is_default', true)
            ->exists();

        if ($hasDefaultShop) {
            return;
        }

        $existing = Shop::query()->where('vendor_id', $vendor->id)->orderBy('id')->first();
        if ($existing) {
            $existing->forceFill(['is_default' => true, 'is_active' => true])->save();

            return;
        }

        Shop::create([
            'name' => [
                'ar' => 'فرع المنصة',
                'en' => 'Platform shop',
            ],
            'email' => 'platform@deemafashion.com',
            'vendor_id' => $vendor->id,
            'is_active' => true,
            'is_default' => true,
        ]);
    }
}
