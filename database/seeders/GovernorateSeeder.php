<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Governorate;
use App\Models\City;
use App\Models\Area;

class GovernorateSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // 1. Ontario
            'Ontario' => [
                'ar' => 'أونتاريو',
                'cities' => [
                    'Toronto' => [
                        'ar' => 'تورونتو',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 43.6532, 'lng' => -79.3832],
                            ['ar' => 'نورث يورك', 'en' => 'North York', 'lat' => 43.7615, 'lng' => -79.4111],
                            ['ar' => 'سكاربورو', 'en' => 'Scarborough', 'lat' => 43.7764, 'lng' => -79.2318],
                            ['ar' => 'إيتوبيكوك', 'en' => 'Etobicoke', 'lat' => 43.6205, 'lng' => -79.5132],
                            ['ar' => 'يوركفيل', 'en' => 'Yorkville', 'lat' => 43.6709, 'lng' => -79.3932],
                        ],
                    ],
                    'Ottawa' => [
                        'ar' => 'أوتاوا',
                        'areas' => [
                            ['ar' => 'سنترتاون', 'en' => 'Centretown', 'lat' => 45.4112, 'lng' => -75.6981],
                            ['ar' => 'كاناتا', 'en' => 'Kanata', 'lat' => 45.3088, 'lng' => -75.8980],
                            ['ar' => 'أورلينز', 'en' => 'Orleans', 'lat' => 45.4542, 'lng' => -75.5060],
                        ],
                    ],
                    'Mississauga' => [
                        'ar' => 'ميسيساغا',
                        'areas' => [
                            ['ar' => 'سيتي سنتر', 'en' => 'City Centre', 'lat' => 43.5890, 'lng' => -79.6441],
                            ['ar' => 'بورت كريديت', 'en' => 'Port Credit', 'lat' => 43.5517, 'lng' => -79.5856],
                        ],
                    ],
                ],
            ],

            // 2. Quebec
            'Quebec' => [
                'ar' => 'كيبيك',
                'cities' => [
                    'Montreal' => [
                        'ar' => 'مونتريال',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 45.5017, 'lng' => -73.5673],
                            ['ar' => 'الهضبة', 'en' => 'Plateau', 'lat' => 45.5200, 'lng' => -73.5800],
                            ['ar' => 'ويستماونت', 'en' => 'Westmount', 'lat' => 45.4850, 'lng' => -73.5950],
                        ],
                    ],
                    'Quebec City' => [
                        'ar' => 'مدينة كيبيك',
                        'areas' => [
                            ['ar' => 'البلدة القديمة', 'en' => 'Old Quebec', 'lat' => 46.8139, 'lng' => -71.2080],
                            ['ar' => 'سانت فوي', 'en' => 'Sainte-Foy', 'lat' => 46.7800, 'lng' => -71.3000],
                        ],
                    ],
                ],
            ],

            // 3. British Columbia
            'British Columbia' => [
                'ar' => 'كولومبيا البريطانية',
                'cities' => [
                    'Vancouver' => [
                        'ar' => 'فانكوفر',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 49.2827, 'lng' => -123.1207],
                            ['ar' => 'كيتسيلانو', 'en' => 'Kitsilano', 'lat' => 49.2680, 'lng' => -123.1650],
                            ['ar' => 'برنابي', 'en' => 'Burnaby', 'lat' => 49.2488, 'lng' => -122.9805],
                        ],
                    ],
                    'Victoria' => [
                        'ar' => 'فيكتوريا',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 48.4284, 'lng' => -123.3656],
                            ['ar' => 'أوك باي', 'en' => 'Oak Bay', 'lat' => 48.4260, 'lng' => -123.3160],
                        ],
                    ],
                ],
            ],

            // 4. Alberta
            'Alberta' => [
                'ar' => 'ألبرتا',
                'cities' => [
                    'Calgary' => [
                        'ar' => 'كالجاري',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 51.0447, 'lng' => -114.0719],
                            ['ar' => 'نورت إيست', 'en' => 'Northeast', 'lat' => 51.0800, 'lng' => -113.9800],
                        ],
                    ],
                    'Edmonton' => [
                        'ar' => 'إدمونتون',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 53.5461, 'lng' => -113.4938],
                            ['ar' => 'ساوث سايد', 'en' => 'South Side', 'lat' => 53.4800, 'lng' => -113.5000],
                        ],
                    ],
                ],
            ],

            // 5. Manitoba
            'Manitoba' => [
                'ar' => 'مانيتوبا',
                'cities' => [
                    'Winnipeg' => [
                        'ar' => 'وينيبيغ',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 49.8951, 'lng' => -97.1384],
                            ['ar' => 'أسبن', 'en' => 'Osborne Village', 'lat' => 49.8770, 'lng' => -97.1450],
                        ],
                    ],
                ],
            ],

            // 6. Saskatchewan
            'Saskatchewan' => [
                'ar' => 'ساسكاتشوان',
                'cities' => [
                    'Saskatoon' => [
                        'ar' => 'ساسكاتون',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 52.1332, 'lng' => -106.6700],
                        ],
                    ],
                    'Regina' => [
                        'ar' => 'ريجينا',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 50.4452, 'lng' => -104.6189],
                        ],
                    ],
                ],
            ],

            // 7. Nova Scotia
            'Nova Scotia' => [
                'ar' => 'نوفا سكوشا',
                'cities' => [
                    'Halifax' => [
                        'ar' => 'هاليفاكس',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 44.6488, 'lng' => -63.5752],
                            ['ar' => 'دارتموث', 'en' => 'Dartmouth', 'lat' => 44.6650, 'lng' => -63.5670],
                        ],
                    ],
                ],
            ],

            // 8. New Brunswick
            'New Brunswick' => [
                'ar' => 'نيو برونزويك',
                'cities' => [
                    'Moncton' => [
                        'ar' => 'مونكتون',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 46.0878, 'lng' => -64.7782],
                        ],
                    ],
                    'Saint John' => [
                        'ar' => 'سانت جون',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 45.2733, 'lng' => -66.0633],
                        ],
                    ],
                ],
            ],

            // 9. Newfoundland and Labrador
            'Newfoundland and Labrador' => [
                'ar' => 'نيوفاوندلاند ولابرادور',
                'cities' => [
                    "St. John's" => [
                        'ar' => 'سانت جونز',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 47.5615, 'lng' => -52.7126],
                        ],
                    ],
                ],
            ],

            // 10. Prince Edward Island
            'Prince Edward Island' => [
                'ar' => 'جزيرة الأمير إدوارد',
                'cities' => [
                    'Charlottetown' => [
                        'ar' => 'شارلوت تاون',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 46.2382, 'lng' => -63.1311],
                        ],
                    ],
                ],
            ],

            // 11. Northwest Territories
            'Northwest Territories' => [
                'ar' => 'الأقاليم الشمالية الغربية',
                'cities' => [
                    'Yellowknife' => [
                        'ar' => 'يلونايف',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 62.4540, 'lng' => -114.3718],
                        ],
                    ],
                ],
            ],

            // 12. Yukon
            'Yukon' => [
                'ar' => 'يوكون',
                'cities' => [
                    'Whitehorse' => [
                        'ar' => 'وايت هورس',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 60.7212, 'lng' => -135.0568],
                        ],
                    ],
                ],
            ],

            // 13. Nunavut
            'Nunavut' => [
                'ar' => 'نونافوت',
                'cities' => [
                    'Iqaluit' => [
                        'ar' => 'إيكالويت',
                        'areas' => [
                            ['ar' => 'وسط المدينة', 'en' => 'Downtown', 'lat' => 63.7467, 'lng' => -68.5170],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($data as $govKey => $govData) {
            $governorate = Governorate::create([
                'name' => [
                    'ar' => $govData['ar'],
                    'en' => $govKey,
                ],
            ]);

            foreach ($govData['cities'] as $cityKey => $cityData) {
                $city = City::create([
                    'name' => [
                        'ar' => $cityData['ar'],
                        'en' => $cityKey,
                    ],
                    'governorate_id' => $governorate->id,
                ]);

                foreach ($cityData['areas'] as $area) {
                    Area::create([
                        'name' => [
                            'ar' => $area['ar'],
                            'en' => $area['en'] ?? $area['ar'],
                        ],
                        'lat' => $area['lat'] ?? null,
                        'lng' => $area['lng'] ?? null,
                        'city_id' => $city->id,
                        'base_fee' => 4,
                    ]);
                }
            }
        }
    }
}
