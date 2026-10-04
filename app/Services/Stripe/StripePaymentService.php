<?php

namespace App\Services\Stripe;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\CustomExceptionWithMessage;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use UnexpectedValueException;

class StripePaymentService
{
    public function __construct(
        private StripeClientFactory $clientFactory
    ) {}

    public function publishableKey(): ?string
    {
        $key = config('stripe.publishable_key');

        return is_string($key) && $key !== '' ? $key : null;
    }

    public function isConfigured(): bool
    {
        return filled(config('stripe.secret_key'))
            && filled(config('stripe.publishable_key'));
    }

    /**
     * Create (or reuse) a PaymentIntent for an unpaid Stripe order.
     *
     * @return array{client_secret: string|null, payment_intent_id: string|null, publishable_key: string|null, payment_status: string, amount: float, currency: string}
     */
    public function createOrReusePaymentIntent(Order $order): array
    {
        $order->loadMissing(['paymentMethod', 'user']);

        if (! $order->paymentMethod?->isStripe()) {
            throw new CustomExceptionWithMessage('custom.stripe.not_stripe_order', 422);
        }

        if ($order->is_paid || $order->payment_status === PaymentStatus::SUCCEEDED->value) {
            throw new CustomExceptionWithMessage('custom.stripe.already_paid', 422);
        }

        if (in_array($order->status, [
            OrderStatus::CANCELLED->value,
            OrderStatus::CANCELLED_BY_ADMIN->value,
            OrderStatus::REJECTEDBYDELIVERY->value,
        ], true)) {
            throw new CustomExceptionWithMessage('custom.stripe.order_not_payable', 422);
        }

        if (! $this->isConfigured()) {
            throw new CustomExceptionWithMessage('custom.stripe.not_configured', 503);
        }

        $amount = round((float) $order->total, 2);

        if ($amount <= 0) {
            $order->update([
                'is_paid' => true,
                'payment_status' => PaymentStatus::SUCCEEDED->value,
                'paid_at' => now(),
            ]);

            return $this->paymentPayload($order->fresh(), null);
        }

        $currency = strtolower((string) config('stripe.currency', 'usd'));
        $amountInSmallestUnit = $this->toSmallestUnit($amount, $currency);

        try {
            if ($order->stripe_payment_intent_id) {
                $intent = $this->clientFactory->make()->paymentIntents->retrieve(
                    $order->stripe_payment_intent_id
                );

                if (in_array($intent->status, ['succeeded', 'processing'], true)) {
                    $this->markOrderPaid($order, $intent);

                    return $this->paymentPayload($order->fresh(), $intent);
                }

                if (! in_array($intent->status, ['canceled', 'cancelled'], true)) {
                    if ((int) $intent->amount !== $amountInSmallestUnit || strtolower((string) $intent->currency) !== $currency) {
                        $intent = $this->clientFactory->make()->paymentIntents->update(
                            $intent->id,
                            [
                                'amount' => $amountInSmallestUnit,
                                'currency' => $currency,
                                'metadata' => $this->orderMetadata($order),
                            ]
                        );
                    }

                    $order->update([
                        'payment_status' => $this->mapIntentStatus($intent->status),
                    ]);

                    return $this->paymentPayload($order->fresh(), $intent);
                }
            }

            $params = [
                'amount' => $amountInSmallestUnit,
                'currency' => $currency,
                'metadata' => $this->orderMetadata($order),
                'description' => 'Order #'.($order->order_code ?? $order->id),
                'receipt_email' => $order->user?->email,
            ];

            if (config('stripe.automatic_payment_methods')) {
                $params['automatic_payment_methods'] = ['enabled' => true];
            } else {
                $params['payment_method_types'] = ['card'];
            }

            $intent = $this->clientFactory->make()->paymentIntents->create($params);

            $order->update([
                'stripe_payment_intent_id' => $intent->id,
                'payment_status' => $this->mapIntentStatus($intent->status),
            ]);

            return $this->paymentPayload($order->fresh(), $intent);
        } catch (ApiErrorException $e) {
            Log::error('Stripe PaymentIntent error', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            throw new CustomExceptionWithMessage('custom.stripe.intent_failed', 502);
        }
    }

    public function syncOrderFromIntent(Order $order): Order
    {
        if (! $order->stripe_payment_intent_id) {
            return $order;
        }

        try {
            $intent = $this->clientFactory->make()->paymentIntents->retrieve(
                $order->stripe_payment_intent_id
            );
        } catch (ApiErrorException $e) {
            Log::warning('Stripe sync failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return $order;
        }

        if ($intent->status === 'succeeded') {
            $this->markOrderPaid($order, $intent);
        } else {
            $order->update([
                'payment_status' => $this->mapIntentStatus($intent->status),
            ]);
        }

        return $order->fresh(['paymentMethod', 'items', 'user', 'address', 'driver']);
    }

    public function cancelPaymentIntent(Order $order): void
    {
        if (! $order->stripe_payment_intent_id || $order->is_paid) {
            return;
        }

        try {
            $intent = $this->clientFactory->make()->paymentIntents->retrieve(
                $order->stripe_payment_intent_id
            );

            if (! in_array($intent->status, ['succeeded', 'canceled', 'cancelled'], true)) {
                $this->clientFactory->make()->paymentIntents->cancel($intent->id);
            }

            $order->update([
                'payment_status' => PaymentStatus::CANCELLED->value,
            ]);
        } catch (ApiErrorException $e) {
            Log::warning('Stripe cancel PaymentIntent failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function handleWebhook(string $payload, ?string $signatureHeader): array
    {
        $secret = config('stripe.webhook_secret');

        if (! is_string($secret) || $secret === '') {
            throw new CustomExceptionWithMessage('custom.stripe.webhook_not_configured', 503);
        }

        try {
            $event = Webhook::constructEvent($payload, (string) $signatureHeader, $secret);
        } catch (UnexpectedValueException) {
            throw new CustomExceptionWithMessage('custom.stripe.invalid_payload', 400);
        } catch (SignatureVerificationException) {
            throw new CustomExceptionWithMessage('custom.stripe.invalid_signature', 400);
        }

        return $this->dispatchEvent($event);
    }

    /**
     * @return array{handled: bool, type: string, order_id: int|null}
     */
    public function dispatchEvent(Event $event): array
    {
        $type = $event->type;
        $object = $event->data->object ?? null;

        if (! $object instanceof PaymentIntent && ! (is_object($object) && ($object->object ?? null) === 'payment_intent')) {
            return ['handled' => false, 'type' => $type, 'order_id' => null];
        }

        $intentId = $object->id ?? null;
        $orderId = isset($object->metadata->order_id)
            ? (int) $object->metadata->order_id
            : null;

        $order = null;

        if ($intentId) {
            $order = Order::query()->where('stripe_payment_intent_id', $intentId)->first();
        }

        if (! $order && $orderId) {
            $order = Order::query()->find($orderId);
        }

        if (! $order) {
            Log::info('Stripe webhook: order not found', [
                'type' => $type,
                'payment_intent' => $intentId,
                'order_id' => $orderId,
            ]);

            return ['handled' => false, 'type' => $type, 'order_id' => $orderId];
        }

        return DB::transaction(function () use ($type, $object, $order) {
            $order = Order::query()->lockForUpdate()->find($order->id);

            match ($type) {
                'payment_intent.succeeded' => $this->markOrderPaid($order, $object),
                'payment_intent.payment_failed' => $order->update([
                    'payment_status' => PaymentStatus::FAILED->value,
                ]),
                'payment_intent.canceled' => $order->update([
                    'payment_status' => PaymentStatus::CANCELLED->value,
                ]),
                'payment_intent.processing' => $order->update([
                    'payment_status' => PaymentStatus::PROCESSING->value,
                ]),
                default => null,
            };

            return [
                'handled' => true,
                'type' => $type,
                'order_id' => $order->id,
            ];
        });
    }

    public function markOrderPaid(Order $order, object $intent): void
    {
        if ($order->is_paid && $order->payment_status === PaymentStatus::SUCCEEDED->value) {
            return;
        }

        $order->update([
            'is_paid' => true,
            'payment_status' => PaymentStatus::SUCCEEDED->value,
            'paid_at' => now(),
            'stripe_payment_intent_id' => $intent->id ?? $order->stripe_payment_intent_id,
        ]);
    }

    /**
     * @return array{client_secret: string|null, payment_intent_id: string|null, publishable_key: string|null, payment_status: string|null, amount: float, currency: string, is_paid: bool}
     */
    public function paymentPayload(Order $order, ?PaymentIntent $intent = null): array
    {
        return [
            'client_secret' => $intent?->client_secret,
            'payment_intent_id' => $intent?->id ?? $order->stripe_payment_intent_id,
            'publishable_key' => $this->publishableKey(),
            'payment_status' => $order->payment_status,
            'amount' => (float) $order->total,
            'currency' => strtoupper((string) config('stripe.currency', 'usd')),
            'is_paid' => (bool) $order->is_paid,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function orderMetadata(Order $order): array
    {
        return [
            'order_id' => (string) $order->id,
            'order_code' => (string) ($order->order_code ?? $order->id),
            'user_id' => (string) $order->user_id,
        ];
    }

    private function mapIntentStatus(string $status): string
    {
        return match ($status) {
            'succeeded' => PaymentStatus::SUCCEEDED->value,
            'processing' => PaymentStatus::PROCESSING->value,
            'requires_action', 'requires_confirmation', 'requires_source_action' => PaymentStatus::REQUIRES_ACTION->value,
            'canceled', 'cancelled' => PaymentStatus::CANCELLED->value,
            'requires_payment_method' => PaymentStatus::FAILED->value,
            default => PaymentStatus::PENDING->value,
        };
    }

    private function toSmallestUnit(float $amount, string $currency): int
    {
        // Zero-decimal currencies (JPY, etc.) — keep extensible.
        $zeroDecimal = ['bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf'];

        if (in_array(strtolower($currency), $zeroDecimal, true)) {
            return (int) round($amount);
        }

        return (int) round($amount * 100);
    }
}
