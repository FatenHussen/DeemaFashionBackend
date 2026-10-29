<?php

namespace Tests\Feature;

use App\Enums\ProductApprovalStatus;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\Color;
use App\Models\Country;
use App\Models\Language;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Vendor;
use App\Services\Admin\ProductService;
use App\Support\AttributeColorHex;
use App\Support\CatalogShadeColors;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProductShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_details_return_color_hex_for_linked_and_legacy_values_in_both_languages(): void
    {
        foreach (['en' => 'English', 'ar' => 'العربية'] as $code => $name) {
            Language::create([
                'code' => $code,
                'native_name' => $name,
                'direction' => $code === 'ar' ? 'rtl' : 'ltr',
                'is_active' => true,
                'is_default' => $code === 'ar',
            ]);
        }

        $product = $this->createProduct();
        $attribute = CategoryAttribute::create([
            'category_id' => $product->category_id,
            'name' => ['en' => 'Color', 'ar' => 'اللون'],
            'type' => 'color',
            'is_active' => true,
        ]);
        $linkedColor = Color::create([
            'name' => ['en' => 'Black with Silver Glitter', 'ar' => 'أسود بلمعة فضية'],
            'hex' => '#123456',
        ]);
        Color::create([
            'name' => ['en' => 'Ocean Shimmer', 'ar' => 'أزرق لامع'],
            'hex' => '#2255ee',
        ]);

        $cases = [
            [['en' => 'Black with Silver Glitter (1)', 'ar' => 'درجة سوداء'], $linkedColor->id, '#123456'],
            [['en' => '  oCeAn sHiMmEr  ', 'ar' => 'درجة المحيط الخاصة'], null, '#2255EE'],
            [['en' => '#abc', 'ar' => 'درجة فاتحة'], null, '#AABBCC'],
            [['en' => 'Unmapped custom shade', 'ar' => 'درجة غير مربوطة'], null, null],
        ];

        foreach ($cases as [$name, $colorId]) {
            $value = $attribute->values()->create(['name' => $name, 'color_id' => $colorId]);
            $product->variants()->create([
                'price' => 20,
                'quantity' => 5,
                'is_active' => true,
                'attributes_values_ids' => [$value->id],
            ]);
        }

        foreach (['en', 'ar'] as $locale) {
            $response = $this->withHeaders(['Accept-Language' => $locale])
                ->getJson("/api/user/products/{$product->id}");

            $response->assertOk()
                ->assertJsonPath('data.attributes_map.0.type', 'color')
                ->assertJsonPath('data.attributes_map.0.attribute', $locale === 'ar' ? 'اللون' : 'Color')
                ->assertJsonCount(count($cases), 'data.attributes_map.0.options')
                ->assertJsonCount(count($cases), 'data.shop_variants');

            foreach ($cases as $index => [, , $expectedHex]) {
                $response->assertJsonPath("data.attributes_map.0.options.{$index}.hex", $expectedHex)
                    ->assertJsonPath("data.shop_variants.{$index}.attributes.0.hex", $expectedHex);
                $this->assertSame(
                    $response->json("data.attributes_map.0.options.{$index}.id"),
                    $response->json("data.shop_variants.{$index}.attributes.0.id")
                );
            }
        }
    }

    public function test_square_attribute_named_color_returns_catalog_hex_without_changing_type(): void
    {
        foreach (['en' => 'English', 'ar' => 'العربية'] as $code => $name) {
            Language::create([
                'code' => $code,
                'native_name' => $name,
                'direction' => $code === 'ar' ? 'rtl' : 'ltr',
                'is_active' => true,
                'is_default' => $code === 'ar',
            ]);
        }

        $product = $this->createProduct();
        $colorAttribute = CategoryAttribute::create([
            'category_id' => $product->category_id,
            'name' => ['en' => 'Color', 'ar' => 'اللون'],
            'type' => 'square',
            'is_active' => true,
        ]);
        $sizeAttribute = CategoryAttribute::create([
            'category_id' => $product->category_id,
            'name' => ['en' => 'Size', 'ar' => 'القياس'],
            'type' => 'square',
            'is_active' => true,
        ]);

        $mauve = Color::create([
            'name' => ['en' => 'Mauve Brown (26)', 'ar' => 'بني مووف'],
            'hex' => '#b5838d',
        ]);
        Color::create([
            'name' => ['en' => 'Milky White (9)', 'ar' => 'أبيض حليبي'],
            'hex' => '#f6f1e7',
        ]);
        Color::create([
            'name' => ['en' => 'Black', 'ar' => 'أسود'],
            'hex' => '#000000',
        ]);

        $mauveValue = $colorAttribute->values()->create([
            'name' => ['en' => 'Custom mauve label', 'ar' => 'درجة مووف'],
            'color_id' => $mauve->id,
        ]);
        $milkyValue = $colorAttribute->values()->create([
            'name' => ['en' => 'Milky White (9)', 'ar' => 'درجة حليبية'],
        ]);
        $sizeValue = $sizeAttribute->values()->create([
            'name' => ['en' => 'Black', 'ar' => 'أسود'],
        ]);

        foreach ([$mauveValue, $milkyValue, $sizeValue] as $value) {
            $product->variants()->create([
                'price' => 20,
                'quantity' => 1,
                'is_active' => true,
                'attributes_values_ids' => [$value->id],
            ]);
        }

        foreach (['en', 'ar'] as $locale) {
            $response = $this->withHeaders(['Accept-Language' => $locale])
                ->getJson("/api/user/products/{$product->id}");

            $response->assertOk()
                ->assertJsonPath('data.attributes_map.0.type', 'square')
                ->assertJsonPath('data.attributes_map.0.attribute', $locale === 'ar' ? 'اللون' : 'Color')
                ->assertJsonPath('data.attributes_map.0.options.0.hex', '#B5838D')
                ->assertJsonPath('data.attributes_map.0.options.1.hex', '#F6F1E7')
                ->assertJsonPath('data.attributes_map.1.type', 'square')
                ->assertJsonPath('data.attributes_map.1.options.0.hex', null)
                ->assertJsonPath('data.shop_variants.0.attributes.0.hex', '#B5838D')
                ->assertJsonPath('data.shop_variants.0.attributes.0.type', 'square')
                ->assertJsonPath('data.shop_variants.1.attributes.0.hex', '#F6F1E7')
                ->assertJsonPath('data.shop_variants.2.attributes.0.hex', null);
        }
    }

    public function test_catalog_shades_link_color_records_and_product_hex_matches_on_variants(): void
    {
        foreach (['en' => 'English', 'ar' => 'العربية'] as $code => $name) {
            Language::create([
                'code' => $code,
                'native_name' => $name,
                'direction' => $code === 'ar' ? 'rtl' : 'ltr',
                'is_active' => true,
                'is_default' => $code === 'ar',
            ]);
        }

        $product = $this->createProduct();
        $colorAttribute = CategoryAttribute::create([
            'category_id' => $product->category_id,
            'name' => ['en' => 'Color', 'ar' => 'اللون'],
            'type' => 'color',
            'is_active' => true,
        ]);
        $sizeAttribute = CategoryAttribute::create([
            'category_id' => $product->category_id,
            'name' => ['en' => 'Size', 'ar' => 'القياس'],
            'type' => 'square',
            'is_active' => true,
        ]);

        $glitter = $colorAttribute->values()->create([
            'name' => ['en' => 'Black with Silver Glitter (1)', 'ar' => 'اسود مع لمعة فضية (1)'],
        ]);
        $mauve = $colorAttribute->values()->create([
            'name' => ['en' => 'Mauve Brown (26)', 'ar' => 'بني موف (26)'],
        ]);
        $size = $sizeAttribute->values()->create([
            'name' => ['en' => 'Black (5)', 'ar' => 'اسود (5)'],
        ]);
        $kept = Color::create([
            'name' => ['en' => 'Milky White (9)', 'ar' => 'ابيض حليبي (9)'],
            'hex' => '#111111',
            'is_active' => true,
        ]);
        $milky = $colorAttribute->values()->create([
            'name' => ['en' => 'Milky White (9)', 'ar' => 'ابيض حليبي (9)'],
            'color_id' => $kept->id,
        ]);

        foreach ([$glitter, $mauve, $milky, $size] as $value) {
            $product->variants()->create([
                'price' => 20,
                'quantity' => 1,
                'is_active' => true,
                'attributes_values_ids' => [$value->id],
            ]);
        }

        $this->assertGreaterThan(0, CatalogShadeColors::link());

        $this->assertSame($kept->id, (int) $milky->fresh()->color_id);
        $this->assertNotNull($glitter->fresh()->color_id);
        $this->assertNull($size->fresh()->color_id);

        foreach (['en', 'ar'] as $locale) {
            $response = $this->withHeaders(['Accept-Language' => $locale])
                ->getJson("/api/user/products/{$product->id}");

            $response->assertOk()
                ->assertJsonPath('data.attributes_map.0.type', 'color')
                ->assertJsonPath('data.attributes_map.0.options.0.id', $glitter->id)
                ->assertJsonPath('data.attributes_map.0.options.0.hex', '#3E434A')
                ->assertJsonPath('data.attributes_map.0.options.1.hex', '#9E5E5F')
                ->assertJsonPath('data.attributes_map.0.options.2.hex', '#111111')
                ->assertJsonPath('data.attributes_map.1.options.0.hex', null)
                ->assertJsonPath('data.shop_variants.0.attributes.0.id', $glitter->id)
                ->assertJsonPath('data.shop_variants.0.attributes.0.hex', '#3E434A')
                ->assertJsonPath('data.shop_variants.1.attributes.0.hex', '#9E5E5F')
                ->assertJsonPath('data.shop_variants.2.attributes.0.hex', '#111111')
                ->assertJsonPath('data.shop_variants.3.attributes.0.hex', null);
        }
    }

    public function test_invalid_linked_hex_can_fall_back_to_a_valid_legacy_translation(): void
    {
        $linkedColor = Color::create([
            'name' => ['en' => 'Old shade record', 'ar' => 'سجل قديم'],
            'hex' => 'invalid',
        ]);
        Color::create([
            'name' => ['en' => 'Ruby Veil', 'ar' => 'أحمر مخملي'],
            'hex' => '#bb0044',
        ]);
        $value = new AttributeValue([
            'name' => ['en' => 'Ruby Veil', 'ar' => 'درجة خاصة'],
        ]);
        $value->setRelation('color', $linkedColor);

        $this->assertSame('#BB0044', AttributeColorHex::forValue($value));
    }

    public function test_legacy_color_lookup_reflects_palette_changes_after_a_previous_miss(): void
    {
        $product = $this->createProduct();
        $attribute = CategoryAttribute::create([
            'category_id' => $product->category_id,
            'name' => ['en' => 'Color', 'ar' => 'اللون'],
            'type' => 'color',
            'is_active' => true,
        ]);
        $value = $attribute->values()->create([
            'name' => ['en' => 'New custom shade', 'ar' => 'لون جديد'],
        ]);
        $product->variants()->create([
            'price' => 20,
            'quantity' => 5,
            'is_active' => true,
            'attributes_values_ids' => [$value->id],
        ]);

        $assertHex = function (?string $hex) use ($product): void {
            $this->getJson("/api/user/products/{$product->id}")
                ->assertOk()
                ->assertJsonPath('data.attributes_map.0.options.0.hex', $hex)
                ->assertJsonPath('data.shop_variants.0.attributes.0.hex', $hex);
        };

        $assertHex(null);

        $color = Color::create([
            'name' => $value->getTranslations('name'),
            'hex' => '#123abc',
        ]);
        $assertHex('#123ABC');

        $color->update(['hex' => '#654321']);
        $assertHex('#654321');
    }

    public function test_product_without_variants_still_returns_shop_variants_and_country_name(): void
    {
        $country = Country::create([
            'name' => ['en' => 'Turkey', 'ar' => 'تركيا'],
            'code' => '+90',
        ]);

        $product = $this->createProduct([
            'sku' => 'LIG-8188-BASE',
            'price' => 20,
            'quantity' => 100,
            'country_id' => $country->id,
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'en'])
            ->getJson("/api/user/products/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.id', $product->id);

        $country = $response->json('data.country');
        $this->assertIsString($country);
        $this->assertNotEmpty($country);

        $shopVariants = $response->json('data.shop_variants');
        $this->assertIsArray($shopVariants);
        $this->assertNotEmpty($shopVariants);
        $this->assertSame(20, $shopVariants[0]['price']);
        $this->assertSame('LIG-8188-BASE', $shopVariants[0]['sku']);
        $this->assertSame([], $shopVariants[0]['attributes']);
    }

    public function test_variant_without_shop_link_is_still_returned(): void
    {
        $product = $this->createProduct([
            'sku' => 'PARENT-SKU',
            'price' => 10,
            'quantity' => 0,
        ]);

        $variant = $product->variants()->create([
            'sku' => 'VAR-1',
            'price' => 15,
            'quantity' => 8,
            'is_active' => true,
            'attributes_values_ids' => [],
        ]);

        $response = $this->getJson("/api/user/products/{$product->id}");

        $response->assertOk();
        $shopVariants = $response->json('data.shop_variants');

        $this->assertCount(1, $shopVariants);
        $this->assertSame($variant->id, $shopVariants[0]['variant_id']);
        $this->assertSame(15, $shopVariants[0]['price']);
        $this->assertSame(8, $shopVariants[0]['quantity']);
        $this->assertNull($shopVariants[0]['shop_id']);
    }

    public function test_admin_create_platform_sale_channel_links_platform_default_shop(): void
    {
        $platformVendor = $this->createVendor();
        if ($platformVendor->id !== ProductService::PLATFORM_VENDOR_ID) {
            $this->markTestSkipped('Platform sale_channel requires vendors.id = 1.');
        }

        $category = $this->createCategory();
        $defaultShop = Shop::create([
            'name' => ['en' => 'Platform default', 'ar' => 'فرع المنصة'],
            'email' => 'platform-default@example.com',
            'vendor_id' => ProductService::PLATFORM_VENDOR_ID,
            'is_active' => true,
            'is_default' => true,
        ]);

        $resource = app(ProductService::class)->create([
            'category_id' => $category->id,
            'sale_channel' => 'platform',
            'name' => ['en' => 'Site product', 'ar' => 'منتج الموقع'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => 'SITE-001',
            'price' => 30,
            'quantity' => 5,
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
        ]);

        $product = Product::with(['variants.shopVariants'])->findOrFail($resource->id);

        $this->assertSame('platform', $product->sale_channel);
        $this->assertSame(ProductService::PLATFORM_VENDOR_ID, (int) $product->vendor_id);
        $this->assertCount(1, $product->variants);
        $this->assertCount(1, $product->variants->first()->shopVariants);
        $this->assertSame($defaultShop->id, $product->variants->first()->shopVariants->first()->shop_id);

        $response = $this->getJson("/api/user/products/{$product->id}");
        $response->assertOk();
        $this->assertSame($defaultShop->id, $response->json('data.shop_variants.0.shop_id'));
        $this->assertNotNull($response->json('data.shop_variants.0.id'));
    }

    public function test_admin_create_shop_sale_channel_requires_explicit_shop_link(): void
    {
        $vendor = $this->createVendor();
        $category = $this->createCategory();
        $shop = Shop::create([
            'name' => ['en' => 'Branch', 'ar' => 'فرع'],
            'email' => 'branch@example.com',
            'vendor_id' => $vendor->id,
            'is_active' => true,
            'is_default' => true,
        ]);

        $resource = app(ProductService::class)->create([
            'category_id' => $category->id,
            'sale_channel' => 'shop',
            'name' => ['en' => 'Vendor product', 'ar' => 'منتج متجر'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'price' => 40,
            'quantity' => 3,
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
            'variants' => [
                [
                    'sku' => 'V-1',
                    'price' => 40,
                    'quantity' => 3,
                    'is_active' => true,
                    'attributes_values_ids' => [],
                ],
            ],
            'shop_variants' => [
                [
                    'shop_id' => $shop->id,
                    'variant_index' => 0,
                    'cost_price' => 20,
                ],
            ],
        ]);

        $product = Product::with(['variants.shopVariants'])->findOrFail($resource->id);

        $this->assertSame('shop', $product->sale_channel);
        $this->assertSame($vendor->id, (int) $product->vendor_id);
        $this->assertSame($shop->id, $product->variants->first()->shopVariants->first()->shop_id);
    }

    public function test_admin_create_persists_variants_and_shop_links(): void
    {
        $vendor = $this->createVendor();
        $category = $this->createCategory();
        $shop = Shop::create([
            'name' => ['en' => 'Main shop', 'ar' => 'المحل'],
            'email' => 'shop@example.com',
            'vendor_id' => $vendor->id,
            'is_active' => true,
        ]);

        $resource = app(ProductService::class)->create([
            'category_id' => $category->id,
            'sale_channel' => 'shop',
            'name' => ['en' => 'Jeans', 'ar' => 'جينز'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'price' => 20,
            'quantity' => 0,
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
            'variants' => [
                [
                    'sku' => 'JEANS-RED',
                    'price' => 25,
                    'quantity' => 12,
                    'is_active' => true,
                    'attributes_values_ids' => [],
                ],
            ],
            'shop_variants' => [
                [
                    'shop_id' => $shop->id,
                    'variant_index' => 0,
                    'cost_price' => 10,
                ],
            ],
        ]);

        $productId = $resource->id;
        $product = Product::with(['variants.shopVariants'])->findOrFail($productId);

        $this->assertCount(1, $product->variants);
        $this->assertSame('JEANS-RED', $product->variants->first()->sku);
        $this->assertEquals(25, $product->variants->first()->price);
        $this->assertSame(12, $product->variants->first()->quantity);
        $this->assertCount(1, $product->variants->first()->shopVariants);
        $this->assertSame($shop->id, $product->variants->first()->shopVariants->first()->shop_id);

        $response = $this->getJson("/api/user/products/{$productId}");
        $response->assertOk();
        $this->assertSame($shop->id, $response->json('data.shop_variants.0.shop_id'));
        $this->assertSame(25, $response->json('data.shop_variants.0.price'));
    }

    private function createProduct(array $overrides = []): Product
    {
        $vendor = $this->createVendor();
        $category = $this->createCategory();

        return Product::create(array_merge([
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'Street jeans', 'ar' => 'بنطال جينز'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'price' => 20,
            'quantity' => 100,
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
            'is_visible' => true,
        ], $overrides));
    }

    private function createCategory(): Category
    {
        return Category::create([
            'name' => ['en' => 'Jeans', 'ar' => 'جينز'],
            'is_active' => true,
            'is_restaurant' => false,
        ]);
    }

    private function createVendor(): Vendor
    {
        return Vendor::create([
            'name' => ['en' => 'Deema Fashion', 'ar' => 'ديما فاشن'],
            'owner_name' => 'Owner',
            'owner_phone' => '0500000000',
            'contract_date' => now()->toDateString(),
            'contract_number' => 'CNT-' . str()->uuid(),
            'contract_duration_months' => 12,
            'commission_rate' => 5,
            'is_active' => true,
        ]);
    }
}
