<?php

namespace Database\Seeders;

use App\Models\Icon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class IconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sourceDir = database_path('seeders/assets/icons');
        $publicDir = storage_path('app/public/icons');
        File::ensureDirectoryExists($publicDir);

        $icons = [
            [
                'name'        => ['ar' => 'جديد', 'en' => 'New'],
                'file'        => 'new.svg',
                'description' => ['ar' => 'منتج جديد', 'en' => 'New product'],
            ],
            [
                'name'        => ['ar' => 'عرض خاص', 'en' => 'Special Offer'],
                'file'        => 'special-offer.svg',
                'description' => ['ar' => 'عرض لفترة محدودة', 'en' => 'Limited time offer'],
            ],
            [
                'name'        => ['ar' => 'الأكثر مبيعاً', 'en' => 'Best Seller'],
                'file'        => 'best-seller.svg',
                'description' => ['ar' => 'من أكثر المنتجات مبيعاً', 'en' => 'One of the best selling products'],
            ],
            [
                'name'        => ['ar' => 'توصيل مجاني', 'en' => 'Free Delivery'],
                'file'        => 'free-delivery.svg',
                'description' => ['ar' => 'توصيل مجاني للمنتج', 'en' => 'Free delivery for this product'],
            ],
            [
                'name'        => ['ar' => 'توصيل سريع', 'en' => 'Fast Delivery'],
                'file'        => 'fast-delivery.svg',
                'description' => ['ar' => 'يصل طلبك بسرعة مع متابعة واضحة للشحنة.', 'en' => 'Your order arrives quickly with clear shipment tracking.'],
            ],
            [
                'name'        => ['ar' => 'خصم', 'en' => 'Discount'],
                'file'        => 'discount.svg',
                'description' => ['ar' => 'منتج عليه خصم', 'en' => 'Product on discount'],
            ],
            [
                'name'        => ['ar' => 'محدود', 'en' => 'Limited'],
                'file'        => 'limited.svg',
                'description' => ['ar' => 'كمية محدودة', 'en' => 'Limited quantity'],
            ],
            [
                'name'        => ['ar' => 'عضوي', 'en' => 'Organic'],
                'file'        => 'organic.svg',
                'description' => ['ar' => 'منتج عضوي طبيعي', 'en' => 'Natural organic product'],
            ],
            [
                'name'        => ['ar' => 'مستورد', 'en' => 'Imported'],
                'file'        => 'imported.svg',
                'description' => ['ar' => 'منتج مستورد', 'en' => 'Imported product'],
            ],
            [
                'name'        => ['ar' => 'دفع عند الاستلام', 'en' => 'Cash on Delivery'],
                'file'        => 'cod.svg',
                'description' => [
                    'ar' => 'تتيح لك طريقة الدفع عند الاستلام (COD) الدفع نقداً عند باب منزلك أو عملك مقابل المنتجات التي يتم تسليمها بواسطة تيك موول أو بواسطة البائع.',
                    'en' => 'Cash on Delivery (COD) allows you to pay in cash at your doorstep or workplace for products delivered by Tikmool or the seller.',
                ],
            ],
            [
                'name'        => ['ar' => 'منتج مكفول', 'en' => 'Warranted Product'],
                'file'        => 'warranty.svg',
                'description' => [
                    'ar' => 'تضمن تيك موول جودة منتجاتها المعروضة، بالإضافة إلى أن بعض المنتجات تشمل ضمان وكفالة لمدد مختلفة من قبل الجهة الموردة أو المصنعة، ويتم ذكر ذلك في بطاقة المنتج عند توفره.',
                    'en' => 'Tikmool guarantees the quality of its listed products. Some products also include a warranty from the supplier or manufacturer, mentioned on the product card when available.',
                ],
            ],
            [
                'name'        => ['ar' => 'توصيل آمن', 'en' => 'Secure Delivery'],
                'file'        => 'secure-delivery.svg',
                'description' => [
                    'ar' => 'عملية التوصيل مضمونة ونراعي إجراءات السلامة لنتأكد من ذلك.',
                    'en' => 'The delivery process is guaranteed and we follow safety procedures to ensure that.',
                ],
            ],
            [
                'name'        => ['ar' => 'معاملتك آمنة', 'en' => 'Secure Transaction'],
                'file'        => 'secure-transaction.svg',
                'description' => [
                    'ar' => 'نعمل دائماً لحماية أمنك وخصوصيتك. يقوم نظام أمان الدفع الخاص بنا بتشفير معلوماتك أثناء النقل.',
                    'en' => 'We always work to protect your security and privacy. Our payment security system encrypts your information during transmission.',
                ],
            ],
        ];

        foreach ($icons as $iconData) {
            $source = $sourceDir . DIRECTORY_SEPARATOR . $iconData['file'];
            $relative = 'icons/' . $iconData['file'];
            $destination = $publicDir . DIRECTORY_SEPARATOR . $iconData['file'];

            if (File::exists($source)) {
                File::copy($source, $destination);
            }

            $payload = [
                'name' => $iconData['name'],
                'image' => $relative,
                'description' => $iconData['description'],
                'is_active' => true,
            ];

            $existing = Icon::query()->where('name->en', $iconData['name']['en'])->first();

            if ($existing) {
                if (is_string($existing->image) && $existing->image !== '' && $existing->image !== $relative) {
                    unset($payload['image']);
                }
                $existing->update($payload);
            } else {
                Icon::create($payload);
            }
        }

        $this->command?->info('Professional product icons seeded.');
    }
}
