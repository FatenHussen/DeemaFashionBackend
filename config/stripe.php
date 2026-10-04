<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe API Keys
    |--------------------------------------------------------------------------
    |
    | Secret key is used server-side. Publishable key is returned to clients
    | so they can confirm PaymentIntents with Stripe.js / Flutter Stripe.
    |
    */

    'secret_key' => env('STRIPE_SECRET_KEY'),

    'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Signing Secret
    |--------------------------------------------------------------------------
    |
    | From Stripe Dashboard → Developers → Webhooks → Signing secret.
    | Locally you can use: stripe listen --forward-to ...
    |
    */

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Charge Currency
    |--------------------------------------------------------------------------
    |
    | Order totals in this app are stored in the base currency (USD).
    | Stripe amounts are sent in the smallest unit (cents for USD).
    |
    */

    'currency' => env('STRIPE_CURRENCY', 'usd'),

    /*
    |--------------------------------------------------------------------------
    | PaymentIntent options
    |--------------------------------------------------------------------------
    */

    'automatic_payment_methods' => env('STRIPE_AUTOMATIC_PAYMENT_METHODS', true),

];
