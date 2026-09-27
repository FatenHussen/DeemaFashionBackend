<?php

namespace Tests\Feature;

use App\Enums\ProductApprovalStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\Admin\ProductService as AdminProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HiddenCategoryProductVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_hiding_a_category_hides_its_products_and_subcategory_products_from_the_catalog(): void
    {
        $vendor = $this->createVendor();
        $fashion = $this->createCategory('Fashion');
        $clothing = $this->createCategory('Clothing', $fashion);
        $food = $this->createCategory('Food');

        $fashionProduct = $this->createProduct($fashion, $vendor, 'Fashion dress');
        $clothingProduct = $this->createProduct($clothing, $vendor, 'Clothing shirt');
        $foodProduct = $this->createProduct($food, $vendor, 'Food apple');

        $fashion->update(['is_active' => false]);
        $clothing->update(['is_active' => false]);

        $ids = collect($this->getJson('/api/user/products')->json('data.items'))->pluck('id')->all();

        $this->assertNotContains($fashionProduct->id, $ids);
        $this->assertNotContains($clothingProduct->id, $ids);
        $this->assertContains($foodProduct->id, $ids);

        $this->getJson("/api/user/products/{$clothingProduct->id}")->assertNotFound();
        $this->getJson("/api/user/categories/{$fashion->id}/page")->assertNotFound();
        $this->getJson("/api/user/categories/{$clothing->id}/page")->assertNotFound();

        $this->getJson('/api/user/search?search=shirt&type=product')
            ->assertOk()
            ->assertJsonMissing(['id' => $clothingProduct->id]);

        $adminIds = app(AdminProductService::class)
            ->queryBuilder(Product::query(), [])
            ->pluck('id')
            ->all();

        $this->assertContains($fashionProduct->id, $adminIds);
        $this->assertContains($clothingProduct->id, $adminIds);
    }

    public function test_hiding_only_the_parent_hides_products_of_an_active_subcategory(): void
    {
        $vendor = $this->createVendor();
        $fashion = $this->createCategory('Fashion');
        $clothing = $this->createCategory('Clothing', $fashion);
        $product = $this->createProduct($clothing, $vendor, 'Still active child product');

        $fashion->update(['is_active' => false]);

        $ids = collect($this->getJson('/api/user/products')->json('data.items'))->pluck('id')->all();

        $this->assertNotContains($product->id, $ids);
        $this->getJson("/api/user/categories/{$clothing->id}/page")->assertNotFound();
    }

    public function test_hiding_only_a_subcategory_keeps_parent_products_visible(): void
    {
        $vendor = $this->createVendor();
        $fashion = $this->createCategory('Fashion');
        $clothing = $this->createCategory('Clothing', $fashion);

        $parentProduct = $this->createProduct($fashion, $vendor, 'Parent fashion product');
        $childProduct = $this->createProduct($clothing, $vendor, 'Child clothing product');

        $clothing->update(['is_active' => false]);

        $ids = collect($this->getJson('/api/user/products')->json('data.items'))->pluck('id')->all();

        $this->assertContains($parentProduct->id, $ids);
        $this->assertNotContains($childProduct->id, $ids);
    }

    private function createCategory(string $name, ?Category $parent = null): Category
    {
        return Category::create([
            'name' => ['en' => $name, 'ar' => $name],
            'parent_id' => $parent?->id,
            'is_active' => true,
            'is_restaurant' => false,
        ]);
    }

    private function createVendor(): Vendor
    {
        return Vendor::create([
            'name' => ['en' => 'Test Vendor', 'ar' => 'Test Vendor'],
            'owner_name' => 'Test Owner',
            'owner_phone' => '0500000000',
            'contract_date' => now()->toDateString(),
            'contract_number' => 'CNT-' . str()->uuid(),
            'contract_duration_months' => 12,
            'commission_rate' => 5,
            'is_active' => true,
        ]);
    }

    private function createProduct(Category $category, Vendor $vendor, string $name): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'name' => ['en' => $name, 'ar' => $name],
            'description' => ['en' => $name, 'ar' => $name],
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
        ]);
    }
}
