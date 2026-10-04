<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Vendor;
use App\Models\Area;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $vendor = Vendor::first();
        $areas  = Area::all();

        if (! $vendor || $areas->isEmpty()) {
            return;
        }

        $shops = [
            'Deema Fashion Damascus',
            'Deema Fashion Aleppo',
            'Deema Fashion Homs',
        ];

        foreach ($shops as $index => $shopName) {
            $shop =  Shop::create([
                'name' => [
                    'ar' => 'ديما فاشن - فرع ' . ($index + 1),
                    'en' => $shopName,
                ],

                'description' => [
                    'ar' => 'المتجر الرسمي لمنصة ديما فاشن',
                    'en' => 'Official Deema Fashion store',
                ],

                'address' => [
                    'ar' => 'سوريا - دمشق',
                    'en' => 'Syria - Damascus',
                ],

                'phone' => '011000000' . $index,
                'mobile' => '099000000' . $index,
                'email' => 'shop' . ($index + 1) . '@deemafashion.com',

                'lat' => 33.5138,
                'lng' => 36.2765,

                'area_id' => $areas->random()->id,

                'working_hours' => [
                    'monday'    => ['open' => '09:00', 'close' => '22:00'],
                    'tuesday'   => ['open' => '09:00', 'close' => '22:00'],
                    'wednesday' => ['open' => '09:00', 'close' => '22:00'],
                    'thursday'  => ['open' => '09:00', 'close' => '22:00'],
                    'friday'    => ['open' => '16:00', 'close' => '23:00'],
                    'saturday'  => ['open' => '09:00', 'close' => '23:00'],
                    'sunday'    => ['closed' => true],
                ],

                'logo' => 'shops/logos/image.jpg',

                'cover_images' => [
                    'shops/covers/image.png',
                    'shops/covers/image.png',
                ],

                'is_active' => true,
                'ratings_count' => rand(0, 100),
                'ratings_sum'   => rand(0, 500),

                'vendor_id' => $vendor->id,
                'is_free_delivery' => 0
            ]);


            $shop->badges()->sync([1, 2, 3]);
        }
    }
}
