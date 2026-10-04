<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Service;
use App\Models\Shop;
use App\Models\ShopProductVariant;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class RestaurantAndServiceProviderSeeder extends Seeder
{
    public function run(): void
    {
        $vendor = Vendor::query()->first();
        $area = Area::query()->first();

        if (!$vendor || !$area) {
            return;
        }

        $restaurantCategory = Category::query()
            ->where('name->en', 'Restaurants')
            ->orWhere('name->ar', 'مطاعم')
            ->first();

        $fastFoodCategory = Category::query()
            ->where('name->en', 'Fast Food')
            ->orWhere('name->ar', 'وجبات سريعة')
            ->first();

        $dessertsCategory = Category::query()
            ->where('name->en', 'Desserts')
            ->orWhere('name->ar', 'حلويات')
            ->first();

        foreach ([$restaurantCategory, $fastFoodCategory, $dessertsCategory] as $category) {
            if ($category) {
                $category->update(['is_restaurant' => true]);
            }
        }

        $restaurantShops = [
            [
                'email' => 'restaurant1@deemafashion.com',
                'name' => ['ar' => 'مطعم ديما فاشن - الشام', 'en' => 'Deema Fashion Restaurant Damascus'],
                'description' => ['ar' => 'مطعم يقدم وجبات سريعة يومية', 'en' => 'Restaurant serving daily fast meals'],
                'address' => ['ar' => 'دمشق - المزة', 'en' => 'Damascus - Mezzeh'],
                'phone' => '0112100001',
                'mobile' => '0992100001',
                'lat' => 33.5138,
                'lng' => 36.2765,
            ],
            [
                'email' => 'restaurant2@deemafashion.com',
                'name' => ['ar' => 'مطعم ديما فاشن - حلب', 'en' => 'Deema Fashion Restaurant Aleppo'],
                'description' => ['ar' => 'مطعم يقدم مشويات وسندويش', 'en' => 'Restaurant for grills and sandwiches'],
                'address' => ['ar' => 'حلب - الفرقان', 'en' => 'Aleppo - Al Furqan'],
                'phone' => '0212200002',
                'mobile' => '0992200002',
                'lat' => 36.2021,
                'lng' => 37.1343,
            ],
        ];

        $providerShops = [
            [
                'email' => 'provider1@deemafashion.com',
                'name' => ['ar' => 'خدمات ديما فاشن المنزلية', 'en' => 'Deema Fashion Home Services'],
                'description' => ['ar' => 'مزود خدمات صيانة وتنظيف', 'en' => 'Service provider for maintenance and cleaning'],
                'address' => ['ar' => 'دمشق - أبو رمانة', 'en' => 'Damascus - Abu Rummaneh'],
                'phone' => '0112300001',
                'mobile' => '0992300001',
                'lat' => 33.5160,
                'lng' => 36.2900,
            ],
            [
                'email' => 'provider2@deemafashion.com',
                'name' => ['ar' => 'خدمات ديما فاشن التقنية', 'en' => 'Deema Fashion Technical Services'],
                'description' => ['ar' => 'مزود خدمات تركيب وصيانة', 'en' => 'Service provider for installation and support'],
                'address' => ['ar' => 'حمص - الوعر', 'en' => 'Homs - Al Waer'],
                'phone' => '0312400002',
                'mobile' => '0992400002',
                'lat' => 34.7324,
                'lng' => 36.7137,
            ],
        ];

        $restaurantEntities = collect($restaurantShops)->map(function (array $payload) use ($vendor, $area) {
            return Shop::query()->updateOrCreate(
                ['email' => $payload['email']],
                [
                    'name' => $payload['name'],
                    'description' => $payload['description'],
                    'address' => $payload['address'],
                    'phone' => $payload['phone'],
                    'mobile' => $payload['mobile'],
                    'lat' => $payload['lat'],
                    'lng' => $payload['lng'],
                    'area_id' => $area->id,
                    'vendor_id' => $vendor->id,
                    'is_active' => true,
                    'is_free_delivery' => true,
                    'is_restaurant' => true,
                    'is_service_provider' => false,
                    'pricing_tier' => 'medium',
                    'is_recommended' => true,
                    'payment_methods' => ['cash_on_delivery'],
                    'working_hours' => $this->defaultWorkingHours(),
                    'logo' => 'shops/logos/image.jpg',
                ]
            );
        });

        $serviceEntities = collect($providerShops)->map(function (array $payload) use ($vendor, $area) {
            return Shop::query()->updateOrCreate(
                ['email' => $payload['email']],
                [
                    'name' => $payload['name'],
                    'description' => $payload['description'],
                    'address' => $payload['address'],
                    'phone' => $payload['phone'],
                    'mobile' => $payload['mobile'],
                    'lat' => $payload['lat'],
                    'lng' => $payload['lng'],
                    'area_id' => $area->id,
                    'vendor_id' => $vendor->id,
                    'is_active' => true,
                    'is_free_delivery' => false,
                    'is_restaurant' => false,
                    'is_service_provider' => true,
                    'pricing_tier' => 'medium',
                    'is_recommended' => true,
                    'payment_methods' => ['cash_on_delivery'],
                    'working_hours' => $this->defaultWorkingHours(),
                    'logo' => 'shops/logos/image.jpg',
                ]
            );
        });

        $serviceIds = Service::query()->limit(3)->pluck('id')->all();
        foreach ($serviceEntities as $shop) {
            if (!empty($serviceIds)) {
                $shop->services()->syncWithoutDetaching($serviceIds);
            }
        }

        $restaurantProducts = [
            [
                'name' => ['ar' => 'وجبة برغر دجاج', 'en' => 'Chicken Burger Meal'],
                'description' => ['ar' => 'وجبة برغر مع بطاطا ومشروب', 'en' => 'Burger meal with fries and drink'],
                'price' => 35000,
                'category_id' => $fastFoodCategory?->id ?? $restaurantCategory?->id,
                'variants' => [
                    [
                        'sku' => 'REST-VAR-BURGER-REG',
                        'name' => ['ar' => 'حجم عادي', 'en' => 'Regular Size'],
                        'price_delta' => 0,
                    ],
                    [
                        'sku' => 'REST-VAR-BURGER-LG',
                        'name' => ['ar' => 'حجم كبير', 'en' => 'Large Size'],
                        'price_delta' => 7000,
                    ],
                ],
            ],
            [
                'name' => ['ar' => 'سندويش شاورما', 'en' => 'Shawarma Sandwich'],
                'description' => ['ar' => 'سندويش شاورما دجاج مع صوص خاص', 'en' => 'Chicken shawarma sandwich with special sauce'],
                'price' => 22000,
                'category_id' => $fastFoodCategory?->id ?? $restaurantCategory?->id,
                'variants' => [
                    [
                        'sku' => 'REST-VAR-SHAWARMA-SINGLE',
                        'name' => ['ar' => 'سندويش مفرد', 'en' => 'Single Sandwich'],
                        'price_delta' => 0,
                    ],
                    [
                        'sku' => 'REST-VAR-SHAWARMA-COMBO',
                        'name' => ['ar' => 'وجبة كومبو', 'en' => 'Combo Meal'],
                        'price_delta' => 6000,
                    ],
                ],
            ],
            [
                'name' => ['ar' => 'تشيز كيك', 'en' => 'Cheese Cake'],
                'description' => ['ar' => 'قطعة تشيز كيك طازجة', 'en' => 'Fresh cheese cake slice'],
                'price' => 18000,
                'category_id' => $dessertsCategory?->id ?? $restaurantCategory?->id,
                'variants' => [
                    [
                        'sku' => 'REST-VAR-CAKE-SLICE',
                        'name' => ['ar' => 'قطعة', 'en' => 'Slice'],
                        'price_delta' => 0,
                    ],
                    [
                        'sku' => 'REST-VAR-CAKE-BOX',
                        'name' => ['ar' => 'علبة 4 قطع', 'en' => 'Box of 4'],
                        'price_delta' => 45000,
                    ],
                ],
            ],
        ];

        foreach ($restaurantProducts as $index => $data) {
            if (!$data['category_id']) {
                continue;
            }

            $product = Product::query()->updateOrCreate(
                ['name->en' => $data['name']['en']],
                [
                    'category_id' => $data['category_id'],
                    'vendor_id' => $vendor->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'full_description' => $data['description'],
                    'price' => $data['price'],
                    'cost_price' => max(1, (int) floor($data['price'] * 0.7)),
                    'discount' => 0,
                    'discount_type' => 'none',
                    'quantity' => 200,
                    'unit' => 'piece',
                    'is_instant_delivery' => true,
                    'is_visible' => true,
                    'is_active' => true,
                    'approval_status' => 'approved',
                    'time_prepare' => '00:20:00',
                ]
            );

            foreach ($data['variants'] as $variantIndex => $variantData) {
                $basePrice = $data['price'] + (int) ($variantData['price_delta'] ?? 0);

                $variant = ProductVariant::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'sku' => $variantData['sku'],
                    ],
                    [
                        'name' => $variantData['name'],
                        'attributes_values_ids' => [],
                        'price' => $basePrice,
                        'quantity' => 70 + ($variantIndex * 10),
                        'is_trend' => $variantIndex === 0,
                        'is_active' => true,
                    ]
                );

                foreach ($restaurantEntities as $restaurantShop) {
                    ShopProductVariant::query()->updateOrCreate(
                        [
                            'shop_id' => $restaurantShop->id,
                            'product_variant_id' => $variant->id,
                        ],
                        [
                            'cost_price' => max(1, (int) floor($basePrice * 0.72)),
                        ]
                    );
                }
            }
        }
    }

    private function defaultWorkingHours(): array
    {
        return [
            'monday' => ['open' => '09:00', 'close' => '23:00'],
            'tuesday' => ['open' => '09:00', 'close' => '23:00'],
            'wednesday' => ['open' => '09:00', 'close' => '23:00'],
            'thursday' => ['open' => '09:00', 'close' => '23:00'],
            'friday' => ['open' => '09:00', 'close' => '23:59'],
            'saturday' => ['open' => '09:00', 'close' => '23:59'],
            'sunday' => ['open' => '10:00', 'close' => '22:00'],
        ];
    }
}
