<?php

namespace Tests\Feature;

use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    public function test_webhook_rejects_when_secret_missing(): void
    {
        config(['stripe.webhook_secret' => null]);

        $response = $this->postJson('/api/webhooks/stripe', ['hello' => 'world']);

        $response->assertStatus(503);
    }
}
