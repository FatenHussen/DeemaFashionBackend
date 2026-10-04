<?php

namespace Tests\Feature;

use App\Enums\ProductApprovalStatus;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Vendor;
use App\Services\Admin\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProductShowTest extends TestCase
{
    use RefreshDatabase;

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
