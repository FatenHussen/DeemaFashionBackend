<?php

namespace Tests\Unit;

use App\Models\PaymentMethod;
use PHPUnit\Framework\TestCase;

class PaymentMethodStripeTest extends TestCase
{
    public function test_cash_is_not_paid_on_placement(): void
    {
        $method = new PaymentMethod(['code' => 'cash']);

        $this->assertTrue($method->isCash());
        $this->assertFalse($method->isStripe());
        $this->assertFalse($method->requiresOnlineConfirmation());
        $this->assertFalse($method->isPaidOnPlacement());
    }

    public function test_local_wallets_are_paid_on_placement(): void
    {
        foreach (['syriatel', 'mtn_cash'] as $code) {
            $method = new PaymentMethod(['code' => $code]);

            $this->assertFalse($method->requiresOnlineConfirmation());
            $this->assertTrue($method->isPaidOnPlacement());
        }
    }

    public function test_stripe_requires_confirmation_and_is_unpaid_on_placement(): void
    {
        $method = new PaymentMethod(['code' => 'stripe']);

        $this->assertTrue($method->isStripe());
        $this->assertTrue($method->requiresOnlineConfirmation());
        $this->assertFalse($method->isPaidOnPlacement());
    }
}
