<?php

namespace Tests\Unit;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\Stripe\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Stripe\Event;
use Tests\TestCase;

/**
 * Requires the testing MySQL database (see phpunit.xml).
 */
class StripePaymentServiceDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_intent_succeeded_marks_order_paid(): void
    {
        Schema::disableForeignKeyConstraints();

        $user = User::factory()->create();

        $stripe = PaymentMethod::create([
            'name' => 'Card (Stripe)',
            'code' => 'stripe',
            'is_active' => true,
            'sort_order' => 4,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'user_address_id' => 1,
            'status' => 'pending',
            'payment_method_id' => $stripe->id,
            'is_paid' => false,
            'payment_status' => PaymentStatus::PENDING->value,
            'stripe_payment_intent_id' => 'pi_test_123',
            'total' => 10.5,
            'subtotal' => 10.5,
            'total_quantity' => 1,
            'delivery_price' => 0,
        ]);

        Schema::enableForeignKeyConstraints();

        $intent = (object) [
            'id' => 'pi_test_123',
            'object' => 'payment_intent',
            'status' => 'succeeded',
            'metadata' => (object) [
                'order_id' => (string) $order->id,
            ],
        ];

        $event = Event::constructFrom([
            'id' => 'evt_test_1',
            'object' => 'event',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => $intent,
            ],
        ]);

        $result = app(StripePaymentService::class)->dispatchEvent($event);

        $this->assertTrue($result['handled']);
        $this->assertSame($order->id, $result['order_id']);

        $order->refresh();
        $this->assertTrue($order->is_paid);
        $this->assertSame(PaymentStatus::SUCCEEDED->value, $order->payment_status);
        $this->assertNotNull($order->paid_at);
    }
}
