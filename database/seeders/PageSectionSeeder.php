<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\VariantSection;
use App\Models\{
    Page,
    Section,
    PageSection,
    Banner,
    SectionItem,
    Product
};
use App\Support\DisplayTypeCatalog;

class PageSectionSeeder extends Seeder
{
    public function run()
    {
        /*
        |--------------------------------------------------------------------------
        | Display Types (fixed IDs — see DisplayTypeSeeder)
        |--------------------------------------------------------------------------
        */
        $bannerDisplayTypeId = DisplayTypeCatalog::idFor('banner');
        $productDisplayTypeId = DisplayTypeCatalog::idFor('product');
        $shopDisplayTypeId = DisplayTypeCatalog::idFor('shop');
        $basketsDisplayTypeId = DisplayTypeCatalog::idFor('basket');
        $scheduleBasketsDisplayTypeId = DisplayTypeCatalog::idFor('schedule-basket');
        $brandsDisplayTypeId = DisplayTypeCatalog::idFor('brand');
        $recipeDisplayTypeId = DisplayTypeCatalog::idFor('recipe');
        $bannerDisplayType2Id = DisplayTypeCatalog::idFor('banner', 'welcome');
        $introBannerDisplayTypeId = DisplayTypeCatalog::idFor('banner', 'intro');

        $defaultVariant = VariantSection::Horizontal->value;
        $defaultPageSectionColors = [
            'background_color' => '#f8f9ff',
            'background_card_color' => '#ffffff',
        ];
        $colorPalette = [
            ['background_color' => '#f9fbff', 'background_card_color' => '#ffffff'],
            ['background_color' => '#fff7f0', 'background_card_color' => '#ffe5d9'],
            ['background_color' => '#f5fbf7', 'background_card_color' => '#e7fff1'],
            ['background_color' => '#f1f5ff', 'background_card_color' => '#f2f9ff'],
            ['background_color' => '#fff3f8', 'background_card_color' => '#ffe9f3'],
            ['background_color' => '#f7f4ff', 'background_card_color' => '#efe9ff'],
            ['background_color' => '#fef9f3', 'background_card_color' => '#fff1e0'],
            ['background_color' => '#f6fff7', 'background_card_color' => '#e8fff0'],
            ['background_color' => '#fffaf2', 'background_card_color' => '#fff5e6'],
            ['background_color' => '#eef8ff', 'background_card_color' => '#e5f2ff'],
            ['background_color' => '#fff4f4', 'background_card_color' => '#ffe5e5'],
            ['background_color' => '#f3f9ff', 'background_card_color' => '#e8f0ff'],
            ['background_color' => '#fefaf1', 'background_card_color' => '#fff2de'],
            ['background_color' => '#f2fbff', 'background_card_color' => '#e6f5ff'],
            ['background_color' => '#f5f4ff', 'background_card_color' => '#ece9ff'],
        ];
        $paletteIndex = 0;
        $nextSectionColors = function () use (&$paletteIndex, $colorPalette) {
            $colors = $colorPalette[$paletteIndex % count($colorPalette)];
            $paletteIndex++;
            return $colors;
        };
        $fillPageSectionColors = function (array $attributes, array $customColors = []) use ($defaultPageSectionColors, $nextSectionColors) {
            $sectionColors = $customColors ?: $nextSectionColors();
            return array_merge($defaultPageSectionColors, $sectionColors, $attributes);
        };

        /*
        |--------------------------------------------------------------------------
        | Banners
        |--------------------------------------------------------------------------
        */
        $banner1 = Banner::create([
            'title' => ['en' => 'New arrivals', 'ar' => 'وصل حديثا'],
            'description' => ['en' => 'New arrivals', 'ar' => 'وصل حديثا'],
            'image' => 'banner/image.png',
            'link'  => 'https://deemafashion.com/shops',
            'expires_at' => now()->addMonth(),
        ]);

        $banner2 = Banner::create([
            'title' => ['en' => 'Browse our stores', 'ar' => 'تصفح متاجرنا'],
            'description' => ['en' => 'Browse our stores', 'ar' => 'تصفح متاجرنا'],
            'image' => 'banner/image.png',
            'link'  => 'https://deemafashion.com/categories?category=13',
            'expires_at' => now()->addMonth(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Pages
        |--------------------------------------------------------------------------
        */


        $pages = [
            ['title' => 'Home', 'slug' => 'home'],

            ['title' => 'Recipes', 'slug' => 'recipes'],
            ['title' => 'recipe details', 'slug' => 'recipe_details'],
            ['title' => 'brands', 'slug' => 'brands'],
            ['title' => 'brands details', 'slug' => 'brand_details'],
            ['title' => 'baskets', 'slug' => 'baskets'],
            ['title' => 'basket details', 'slug' => 'basket_details'],
            ['title' => 'products', 'slug' => 'products'],
            ['title' => 'categories', 'slug' => 'categories'],
            ['title' => 'product details', 'slug' => 'product_details'],
            ['title' => 'shops', 'slug' => 'shops'],
            ['title' => 'shop_details', 'slug' => 'shop_details'],
            ['title' => 'brands', 'slug' => 'brands'],
            ['title' => 'brand_details', 'slug' => 'brand_details'],
            ['title' => 'Orders', 'slug' => 'orders'],
            ['title' => 'Order details', 'slug' => 'order_details'],

        ];

        /*
        |--------------------------------------------------------------------------
        | Manual Banners Section
        |--------------------------------------------------------------------------
        */
        $bannerSection = Section::firstOrCreate(
            ['type' => 'manual', 'manual_model' => 'banner'],
            ['name' => ['en' => 'Banners', 'ar' => 'إعلانات']]
        );

        foreach ([$banner1, $banner2] as $index => $banner) {
            SectionItem::firstOrCreate([
                'section_id' => $bannerSection->id,
                'item_type'  => Banner::class,
                'item_id'    => $banner->id,
            ], [
                'order' => $index + 1
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Manual Banners Section
        |--------------------------------------------------------------------------
        */
        $banner3 = Banner::create([
            'title' => ['en' => 'Banner for category 5 ', 'ar' => 'بنر خاص للتصنيف 5'],
            'description' => ['en' => 'Banner for category 5', 'ar' => 'بنر خاص للتصنيف المختار'],
            'image' => 'banner/image.png',
            'link'  => 'https://deemafashion.com/categories?category=13',
            'expires_at' => now()->addMonth(2),
        ]);
        $bannerSection2 = Section::Create(
            [
                'type' => 'manual',
                'manual_model' => 'banner',
                'name' => ['en' => 'Banner for category ', 'ar' => 'بنر خاص للتصنيف المختار']
            ]
        );

        foreach ([$banner3] as $index => $banner) {
            SectionItem::firstOrCreate([
                'section_id' => $bannerSection2->id,
                'item_type'  => Banner::class,
                'item_id'    => $banner->id,
            ], [
                'order' => $index + 1
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Pages Banner Placement
        |--------------------------------------------------------------------------
        */
        $bannerPositions = [
            'store-details'   => 'before',
            'category'        => 'before',
            'categories'      => 'before',
            'products'        => 'before',
            'brand-products'  => 'after',
            'cooking-recipes' => 'before',
            'subscription-packages' => 'after',
            'search-results'  => 'middle',
            'all-baskets'     => 'middle',
            'cart'            => 'before',
            'checkout'        => 'before',
            'review-order'    => 'after',
        ];

        foreach ($pages as $pageData) {
            $page = Page::firstOrCreate(
                ['slug' => $pageData['slug']],
                ['title' => $pageData['title']]
            );

            PageSection::firstOrCreate([
                'page_id' => $page->id,
                'section_id' => $bannerSection->id,
            ], $fillPageSectionColors([
                'display_type_id' => $bannerDisplayTypeId,
                'position' => $bannerPositions[$page->slug] ?? 'before',
                'variant' => $defaultVariant,
            ]));
        }

        $homePage = Page::firstOrCreate(
            ['slug' => 'home'],
            ['title' => 'Home']
        );



        $welcomePage = Page::firstOrCreate(
            ['slug' => 'welcome'],
            ['title' => 'Welcome']
        );
        PageSection::firstOrCreate([
            'page_id' => $welcomePage->id,
            'section_id' => $bannerSection->id,
        ], $fillPageSectionColors([
            'display_type_id' => $bannerDisplayType2Id,
            'position' => 'after',
            'variant' => $defaultVariant,
        ]));


        $introPage = Page::firstOrCreate(
            ['slug' => 'intro'],
            ['title' => 'intro']
        );
        PageSection::firstOrCreate([
            'page_id' => $introPage->id,
            'section_id' => $bannerSection->id,
        ], $fillPageSectionColors([
            'display_type_id' => $introBannerDisplayTypeId,
            'position' => 'after',
            'variant' => $defaultVariant,
        ]));


        $introPage = Page::firstOrCreate(
            ['slug' => 'cart'],
            ['title' => 'cart']
        );
        $ordersPage = Page::firstOrCreate(
            ['slug' => 'orders'],
            ['title' => 'orders']
        );
        $orderDetailsPage = Page::firstOrCreate(
            ['slug' => 'order_details'],
            ['title' => 'order_details']
        );

        /*
        |--------------------------------------------------------------------------
        | Manual Products Section
        |--------------------------------------------------------------------------
        */


        /*
        |--------------------------------------------------------------------------
        | Brands Section
        |--------------------------------------------------------------------------
        */
        $brandsSection = Section::create([
            'name' => ['en' => 'Brands', 'ar' => 'قسم البراندات'],
            'type' => 'api',
            'api_method' => 'brands',
            'filters' => [],
            'see_more' => true,
            'see_more_slug' => 'brands',
            'details_slug' => 'brand_details',
            'manual_model' => 'brand'
        ]);

        PageSection::create($fillPageSectionColors([
            'page_id' => $homePage->id,
            'section_id' => $brandsSection->id,
            'display_type_id' => $brandsDisplayTypeId,
            'position' => 'before',
            'variant' => VariantSection::Vertical->value,
            'order' => 4,
            'filters' => []
        ]));
        /*
        |--------------------------------------------------------------------------
        | Recipes Section
        |--------------------------------------------------------------------------
        */
        $recipeSection = Section::create([
            'name' => ['en' => 'recipe', 'ar' => 'قسم الطبخة'],
            'type' => 'api',
            'api_method' => 'recipes',
            'filters' => [],
            'see_more' => true,
            'see_more_slug' => 'recipes',
            'details_slug' => 'recipe_details',
            'manual_model' => 'recipe'

        ]);

        PageSection::create($fillPageSectionColors([
            'page_id' => $homePage->id,
            'section_id' => $recipeSection->id,
            'display_type_id' => $recipeDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Square->value,
            'order' => 5,
            'filters' => []
        ]));


        /*
        |--------------------------------------------------------------------------
        | Baskets Section
        |--------------------------------------------------------------------------
        */
        $basketSection = Section::create([
            'name' => ['en' => 'basket', 'ar' => 'قسم السلات'],
            'type' => 'api',
            'api_method' => 'baskets',
            'see_more' => true,
            'see_more_slug' => 'baskets',
            'details_slug' => 'basket_details',
            'filters' => [],
            'manual_model' => 'basket'
        ]);


        PageSection::create($fillPageSectionColors([
            'page_id' => $homePage->id,
            'section_id' => $basketSection->id,
            'display_type_id' => $basketsDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Horizontal->value,
            'order' => 6,
            'filters' => []
        ]));
        $schedulebasketSection = Section::create([
            'name' => ['en' => 'schedule basket', 'ar' => 'قسم السلات المجدولة'],
            'type' => 'api',
            'api_method' => 'schedule-basket',
            'see_more' => true,
            'see_more_slug' => 'baskets',
            'details_slug' => 'basket_details',
            'filters' => [],
            'manual_model' => 'schedule-basket'
        ]);

        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Scheduled baskets for the week', 'ar' => 'قسم السلات المجدولة ل شهر'],
            'page_id' => $homePage->id,
            'section_id' => $schedulebasketSection->id,
            'display_type_id' => $scheduleBasketsDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Square->value,
            'order' => 7,
            'filters' => [
                'schedule_days' => 30
            ]
        ]));
        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Baskets scheduled for two weeks', 'ar' => 'قسم السلات المجدولة ل أسبوعين'],
            'page_id' => $homePage->id,
            'section_id' => $schedulebasketSection->id,
            'display_type_id' => $scheduleBasketsDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Vertical->value,
            'order' => 7,
            'filters' => [
                'schedule_days' => 15
            ]
        ]));
        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Scheduled baskets for the week ', 'ar' => ' السلات المجدولة ل أسبوع'],
            'page_id' => $homePage->id,
            'section_id' => $schedulebasketSection->id,
            'display_type_id' => $scheduleBasketsDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Horizontal->value,
            'order' => 7,
            'filters' => [
                'schedule_days' => 30
            ]
        ]));

        /*
        |--------------------------------------------------------------------------
        | Trending Products Section
        |--------------------------------------------------------------------------
        */
        $productsSection = Section::create([
            'name' => ['en' => 'Products', 'ar' => 'المنتجات'],
            'type' => 'api',
            'api_method' => 'products',
            'filters' => [
                'category_id' => ['type' => 'select', 'url' => 'admin/categories'],
                // 'price_max'   => ['type' => 'number'],
                // 'price_min' =>  ['type' => 'number'],
                'shop_id' => ['type' => 'select', 'url' => 'admin/shops'],
                'brand_id' => ['type' => 'select', 'url' => 'admin/brands'],
                'type' => [
                    'type' => 'select',
                    'items' => [
                        'new',
                        'trend',
                        'top_rated',
                        'offers',
                        'latest_flash_sale',
                        'recommended',
                        'for_you',
                        'search_based',
                    ]
                ],
            ],
            'see_more' => true,
            'see_more_slug' => 'products',
            'details_slug'  => 'product_details',
            'manual_model' => 'product'
        ]);

        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Trend Products', 'ar' => 'المنتجات التريند'],
            'page_id' => $homePage->id,
            'section_id' => $productsSection->id,
            'display_type_id' => $productDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Vertical->value,
            'order' => 5,
            'filters' => [
                'type' => 'trend',
            ]
        ]));
        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'New arrival products', 'ar' => 'منتجات وصلت حديثا'],
            'page_id' => $homePage->id,
            'section_id' => $productsSection->id,
            'display_type_id' => $productDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Horizontal->value,
            'order' => 6,
            'filters' => [
                'type' => 'new',
            ]
        ]));
        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Top rated products', 'ar' => 'منتجات اعلى تقييما'],
            'page_id' => $homePage->id,
            'section_id' => $productsSection->id,
            'display_type_id' => $productDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Square->value,
            'order' => 7,
            'filters' => [
                'type' => 'top_rated',
            ]
        ]));

        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Offers', 'ar' => 'العروض'],
            'page_id' => $homePage->id,
            'section_id' => $productsSection->id,
            'display_type_id' => $productDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Vertical->value,
            'order' => 8,
            'filters' => [
                'type' => 'offers',
            ]
        ]));

        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Summer Offers', 'ar' => 'عروض الصيف'],
            'page_id' => $homePage->id,
            'section_id' => $productsSection->id,
            'display_type_id' => $productDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Horizontal->value,
            'order' => 9,
            'filters' => [
                'type' => 'latest_flash_sale',
            ]
        ]));

        $productsPage = Page::updateOrCreate(
            ['slug' => 'products'],
            [
                'title' => 'products',
                'filters' => [
                    'category_id' => ['type' => 'select', 'url' => 'admin/categories'],
                    'shop_id' => ['type' => 'select', 'url' => 'admin/shops'],
                    'brand_id' => ['type' => 'select', 'url' => 'admin/brands'],

                    'type' => [
                        'type' => 'select',
                        'items' => [
                            'new',
                            'trend',
                            'top_rated',
                            'offers',
                            'latest_flash_sale',
                            'recommended',
                            'for_you',
                            'search_based',
                        ],
                    ],
                ],
            ]
        );

        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Banner for category 5', 'ar' => ' اعلان للتصنيف 5'],
            'page_id' => $productsPage->id,
            'section_id' => $bannerSection2->id,
            'display_type_id' => $bannerDisplayTypeId,
            'position' => 'before',
            'variant' =>  VariantSection::Horizontal->value,
            'order' => 1,
            'show_when' => [
                'category_id' => 5,
            ],
        ]));

        $categoriesPage = Page::updateOrCreate(
            ['slug' => 'categories'],
            [
                'title' => 'categories',
                'filters' => [
                    'category_id' => ['type' => 'select', 'url' => 'admin/categories'],
                    'type' => [
                        'type' => 'select',
                        'items' => [
                            'new',
                            'trend',
                            'top_rated',
                            'offers',
                            'latest_flash_sale',
                            'recommended',
                            'for_you',
                            'search_based',
                        ],
                    ],
                ],
            ]
        );

        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Banner for category 5', 'ar' => ' اعلان للتصنيف 5'],
            'page_id' => $categoriesPage->id,
            'section_id' => $bannerSection2->id,
            'display_type_id' => $bannerDisplayTypeId,
            'position' => 'before',
            'variant' => VariantSection::Horizontal->value,
            'order' => 1,
            'show_when' => [
                'category_id' => 5,
            ],
        ]));

        //   $suggestedSection = Section::create([
        //     'name' => ['en' => 'Suggested', 'ar' => 'المقترحات'],
        //     'type' => 'api',
        //     'api_method' => 'suggested',
        //     'filters' => [
        //         'type' => [
        //             'type' => 'select',
        //             'items' => [
        //                 'prodocts',
        //                 'baskets',
        //                 'shops',
        //             ]
        //         ],
        //     ],
        //     'see_more' => true,
        //     'see_more_slug' => 'products',
        //     'details_slug'  => 'product_details',
        //     'manual_model' => 'product'
        // ]);



        $shopSection = Section::create([
            'name' => ['en' => 'Shops', 'ar' => 'المتاجر'],
            'type' => 'api',
            'api_method' => 'shops',
            'filters' => [
                'shop_type' => [
                    'type' => 'select',
                    'items' => [
                        'restaurant',
                        'service_provider',
                        'store',
                    ]
                ],
                'type' => [
                    'type' => 'select',
                    'items' => [
                        'nearby',
                        'offers',
                        'active',
                        'top_rated',
                    ]
                ],
            ],
            'see_more' => true,
            'see_more_slug' => 'shops',
            'details_slug'  => 'shop_details',
            'manual_model' => 'shop'
        ]);

        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Nearby Shops', 'ar' => 'المتاجر القريبة'],
            'page_id' => $homePage->id,
            'section_id' => $shopSection->id,
            'display_type_id' => $shopDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Square->value,
            'order' => 9,
            'filters' => [
                'type' => 'nearby',
            ]
        ]));

        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Restaurants', 'ar' => 'المطاعم'],
            'page_id' => $homePage->id,
            'section_id' => $shopSection->id,
            'display_type_id' => $shopDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Horizontal->value,
            'order' => 10,
            'filters' => [
                'shop_type' => 'restaurant',
            ]
        ]));

        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Service Providers', 'ar' => 'مزودي الخدمات'],
            'page_id' => $homePage->id,
            'section_id' => $shopSection->id,
            'display_type_id' => $shopDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Vertical->value,
            'order' => 11,
            'filters' => [
                'shop_type' => 'service_provider',
            ]
        ]));


        $suggestedShopSection = Section::create([
            'name' => ['en' => 'Suggested Shops', 'ar' => 'المتاجر المقترحة لك'],
            'type' => 'api',
            'api_method' => 'suggested_shops',
            'filters' => [],
            'see_more' => true,
            'see_more_slug' => 'shops',
            'details_slug'  => 'shop_details',
            'manual_model' => 'shop'
        ]);

        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Suggested Shops', 'ar' => 'المتاجر المقترحة لك'],
            'page_id' => $homePage->id,
            'section_id' => $suggestedShopSection->id,
            'display_type_id' => $shopDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Horizontal->value,
            'order' => 9,
            'filters' => []
        ]));


        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Shops with free delivery', 'ar' => 'المتاجر ذات التوصيل المجاني'],
            'page_id' => $homePage->id,
            'section_id' => $shopSection->id,
            'display_type_id' => $shopDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Vertical->value,
            'order' => 12,
            'filters' => [
                'type' => 'free_delivery',
            ]
        ]));

        $suggestedProductSection = Section::create([
            'name' => ['en' => 'Suggested Products', 'ar' => 'المنتجات المقترحة لك'],
            'type' => 'api',
            'api_method' => 'suggested_products',
            'filters' => [],
            'see_more' => true,
            'see_more_slug' => 'products',
            'details_slug'  => 'product_details',
            'manual_model' => 'product'
        ]);

        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Suggested Products', 'ar' => 'المنتجات المقترحة لك'],
            'page_id' => $homePage->id,
            'section_id' => $suggestedProductSection->id,
            'display_type_id' => $productDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Square->value,
            'order' => 10,
            'filters' => []
        ]));

        $suggestedBasketSection = Section::create([
            'name' => ['en' => 'Suggested Baskets', 'ar' => 'السلات المقترحة لك'],
            'type' => 'api',
            'api_method' => 'suggested_baskets',
            'filters' => [],
            'see_more' => true,
            'see_more_slug' => 'baskets',
            'details_slug'  => 'basket_details',
            'manual_model' => 'basket'
        ]);

        PageSection::create($fillPageSectionColors([
            'name' => ['en' => 'Suggested Baskets', 'ar' => 'السلات المقترحة لك'],
            'page_id' => $homePage->id,
            'section_id' => $suggestedBasketSection->id,
            'display_type_id' => $basketsDisplayTypeId,
            'position' => 'after',
            'variant' => VariantSection::Horizontal->value,
            'order' => 11,
            'filters' => []
        ]));
    }
}
