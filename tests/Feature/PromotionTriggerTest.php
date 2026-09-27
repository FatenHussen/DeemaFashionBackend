<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Promotion;
use App\Models\User;
use App\Services\User\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PromotionTriggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_order_discount_applies_when_user_has_no_orders(): void
    {
        $user = $this->createUser();
        $this->createPromotion([
            'type' => 'first_order_discount',
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ]);

        $result = app(PromotionService::class)->evaluateAutomaticTriggerDiscounts($user, 200, collect());

        $this->assertSame(20.0, $result['discount']);
        $this->assertSame('first_order_discount', $result['promotions'][0]['type']);
    }

    public function test_first_order_discount_does_not_apply_after_a_previous_order(): void
    {
        $user = $this->createUser();
        $this->insertOrder($user->id, 'delivered');
        $this->createPromotion([
            'type' => 'first_order_discount',
            'discount_type' => 'fixed',
            'discount_value' => 15,
        ]);

        $result = app(PromotionService::class)->evaluateAutomaticTriggerDiscounts($user, 200, collect());

        $this->assertSame(0.0, $result['discount']);
        $this->assertSame([], $result['promotions']);
    }

    public function test_cancelled_order_still_counts_as_a_first_order(): void
    {
        $user = $this->createUser();
        $this->insertOrder($user->id, 'cancelled');
        $this->createPromotion([
            'type' => 'first_order_free_shipping',
        ]);

        $applies = app(PromotionService::class)->hasActiveAutomaticFreeShipping(80, collect(), $user);

        $this->assertTrue($applies);
    }

    public function test_signup_discount_requires_registration_during_the_promotion(): void
    {
        $user = $this->createUser(now()->subDays(10));
        $this->createPromotion([
            'type' => 'signup_discount',
            'discount_type' => 'fixed',
            'discount_value' => 5,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $result = app(PromotionService::class)->evaluateAutomaticTriggerDiscounts($user, 100, collect());

        $this->assertSame(0.0, $result['discount']);
    }

    public function test_signup_gift_applies_on_the_first_order_of_a_new_account(): void
    {
        $user = $this->createUser(now());
        $this->createPromotion([
            'type' => 'signup_gift',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'gift_description' => ['en' => 'Mug', 'ar' => 'كوب'],
        ]);

        $result = app(PromotionService::class)->applyAutomaticOrderPromotions(
            null,
            $user->id,
            50,
            true,
            collect()
        );

        $this->assertCount(1, $result['gifts']);
        $this->assertSame('signup_gift', $result['gifts'][0]['type']);
        $this->assertSame('كوب', $result['gifts'][0]['gift_description']['ar']);
    }

    private function createUser($createdAt = null): User
    {
        Currency::query()->create([
            'code' => 'USD',
            'name' => ['en' => 'US Dollar', 'ar' => 'دولار'],
            'symbol' => '$',
            'exchange_rate' => 1,
            'is_default' => true,
            'is_active' => true,
        ]);

        $user = User::query()->create([
            'name' => 'Promo User',
            'email' => 'promo-trigger-'.uniqid().'@example.com',
            'phone' => '099'.random_int(1000000, 9999999),
            'password' => Hash::make('password'),
        ]);

        if ($createdAt) {
            $user->forceFill(['created_at' => $createdAt])->save();
        }

        return $user->fresh();
    }

    private function insertOrder(int $userId, string $status): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('orders')->insert([
            'user_id' => $userId,
            'user_address_id' => 1,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Schema::enableForeignKeyConstraints();
    }

    private function createPromotion(array $attributes = []): Promotion
    {
        return Promotion::query()->create(array_merge([
            'name' => ['en' => 'Promotion', 'ar' => 'عرض'],
            'description' => ['en' => 'Promotion', 'ar' => 'عرض'],
            'type' => 'first_order_discount',
            'is_active' => true,
            'position' => 'top',
            'starts_at' => null,
            'ends_at' => null,
            'min_spend' => null,
            'discount_value' => null,
            'discount_type' => null,
            'gift_description' => null,
            'reward_points' => null,
        ], $attributes));
    }
}
