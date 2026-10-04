<?php

namespace App\Services\Stripe;

use App\Exceptions\CustomExceptionWithMessage;
use Stripe\StripeClient;

class StripeClientFactory
{
    private static ?StripeClient $client = null;

    public function make(): StripeClient
    {
        if (self::$client instanceof StripeClient) {
            return self::$client;
        }

        $secret = config('stripe.secret_key');

        if (! is_string($secret) || $secret === '') {
            throw new CustomExceptionWithMessage('custom.stripe.not_configured', 503);
        }

        self::$client = new StripeClient($secret);

        return self::$client;
    }

    /**
     * @internal Used by tests to reset the singleton.
     */
    public static function reset(): void
    {
        self::$client = null;
    }
}
