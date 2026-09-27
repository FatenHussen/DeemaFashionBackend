<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\Shop\UpdateRequest;
use App\Http\Resources\Shop\AllResource;
use App\Models\Shop;
use App\Models\Vendor;
use App\Services\Admin\ShopService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdminShopDeliveryLimitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_update_changes_only_the_three_delivery_fields(): void
    {
        $shop = $this->createShop([
            'email' => 'store@example.com',
            'min_order_amount' => 5,
            'delivery_min_hours' => 2,
            'delivery_max_hours' => 6,
        ]);

        $validated = $this->validateUpdate($shop, [
            'min_order_amount' => 20,
            'delivery_min_hours' => 1,
            'delivery_max_hours' => 3,
        ]);

        $this->assertSame(
            ['min_order_amount', 'delivery_min_hours', 'delivery_max_hours'],
            array_keys($validated)
        );

        app(ShopService::class)->update($shop->id, $validated);

        $shop->refresh();
        $this->assertSame('Store', $shop->getTranslation('name', 'en'));
        $this->assertSame('store@example.com', $shop->email);
        $this->assertEquals(20.0, $shop->min_order_amount);
        $this->assertSame(1, $shop->delivery_min_hours);
        $this->assertSame(3, $shop->delivery_max_hours);
    }

    public function test_empty_value_clears_one_field_and_keeps_the_others(): void
    {
        $shop = $this->createShop([
            'email' => 'store-kept@example.com',
            'min_order_amount' => 20,
            'delivery_min_hours' => 1,
            'delivery_max_hours' => 3,
        ]);

        $validated = $this->validateUpdate($shop, [
            'min_order_amount' => '',
        ]);

        app(ShopService::class)->update($shop->id, $validated);

        $shop->refresh();
        $this->assertSame('store-kept@example.com', $shop->email);
        $this->assertNull($shop->min_order_amount);
        $this->assertSame(1, $shop->delivery_min_hours);
        $this->assertSame(3, $shop->delivery_max_hours);
        $this->assertSame('Store', $shop->getTranslation('name', 'en'));
    }

    public function test_platform_shop_rejects_delivery_limit_fields(): void
    {
        $shop = $this->createShop(['is_default' => true]);

        try {
            $this->validateUpdate($shop, [
                'min_order_amount' => 20,
                'delivery_min_hours' => 1,
                'delivery_max_hours' => 3,
            ]);
            $this->fail('Platform shop accepted delivery limit fields.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('min_order_amount', $exception->errors());
            $this->assertArrayHasKey('delivery_min_hours', $exception->errors());
            $this->assertArrayHasKey('delivery_max_hours', $exception->errors());
        }

        $this->assertNull($shop->fresh()->min_order_amount);
    }

    public function test_max_hours_must_stay_at_least_the_saved_minimum(): void
    {
        $shop = $this->createShop([
            'delivery_min_hours' => 5,
            'delivery_max_hours' => 8,
        ]);

        try {
            $this->validateUpdate($shop, [
                'delivery_max_hours' => 2,
            ]);
            $this->fail('A shorter maximum was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('delivery_max_hours', $exception->errors());
        }

        $shop->refresh();
        $this->assertSame(8, $shop->delivery_max_hours);
    }

    public function test_shop_list_includes_platform_and_restaurant_flags(): void
    {
        $shop = $this->createShop([
            'is_default' => true,
            'is_restaurant' => true,
        ]);
        $shop->setRelation('badges', collect());
        $shop->setRelation('categories', collect());

        $data = (new AllResource($shop))->resolve(Request::create('/api/admin/shops', 'GET'));

        $this->assertTrue($data['is_default']);
        $this->assertTrue($data['is_restaurant']);
        $this->assertArrayHasKey('min_order_amount', $data);
        $this->assertArrayHasKey('delivery_min_hours', $data);
        $this->assertArrayHasKey('delivery_max_hours', $data);
    }

    private function validateUpdate(Shop $shop, array $payload): array
    {
        $request = UpdateRequest::create('/api/admin/shops/'.$shop->id, 'PUT', $payload);
        $request->headers->set('Accept', 'application/json');
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make(Redirector::class));

        $route = new Route('PUT', '/api/admin/shops/{shop}', []);
        $route->bind($request);
        $route->setParameter('shop', (string) $shop->id);
        $request->setRouteResolver(fn () => $route);

        $request->validateResolved();

        return $request->validated();
    }

    private function createShop(array $overrides = []): Shop
    {
        $vendor = Vendor::create([
            'name' => ['en' => 'Tikmool', 'ar' => 'تيكموول'],
            'owner_name' => 'Owner',
            'owner_phone' => '0500000000',
            'contract_date' => now()->toDateString(),
            'contract_number' => 'CNT-'.str()->uuid(),
            'contract_duration_months' => 12,
            'commission_rate' => 5,
            'is_active' => true,
        ]);

        return Shop::create(array_merge([
            'name' => ['en' => 'Store', 'ar' => 'متجر'],
            'email' => 'store-'.str()->uuid().'@example.com',
            'vendor_id' => $vendor->id,
            'is_active' => true,
            'is_default' => false,
            'is_restaurant' => false,
        ], $overrides));
    }
}
