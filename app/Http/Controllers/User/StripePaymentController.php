<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OneResource;
use App\Models\Order;
use App\Services\Stripe\StripePaymentService;

class StripePaymentController extends Controller
{
    public function __construct(
        private StripePaymentService $stripePaymentService
    ) {}

    /**
     * Public config for Stripe.js / flutter_stripe.
     */
    public function config()
    {
        return $this->sendResponse([
            'publishable_key' => $this->stripePaymentService->publishableKey(),
            'currency' => strtoupper((string) config('stripe.currency', 'usd')),
            'enabled' => $this->stripePaymentService->isConfigured(),
        ]);
    }

    /**
     * Create or reuse a PaymentIntent for an unpaid Stripe order.
     */
    public function pay(int $orderId)
    {
        $order = Order::query()
            ->with(['paymentMethod', 'user'])
            ->where('id', $orderId)
            ->where('user_id', auth('user')->id())
            ->firstOrFail();

        $payment = $this->stripePaymentService->createOrReusePaymentIntent($order);

        $order = $order->fresh(['paymentMethod', 'items', 'user', 'address', 'driver']);

        return $this->sendResponse([
            'order' => new OneResource($order),
            'payment' => $payment,
        ]);
    }

    /**
     * Poll / sync payment status after client confirms (webhook is source of truth).
     */
    public function status(int $orderId)
    {
        $order = Order::query()
            ->with(['paymentMethod'])
            ->where('id', $orderId)
            ->where('user_id', auth('user')->id())
            ->firstOrFail();

        if ($order->paymentMethod?->isStripe() && ! $order->is_paid && $order->stripe_payment_intent_id) {
            $order = $this->stripePaymentService->syncOrderFromIntent($order);
        }

        return $this->sendResponse([
            'order_id' => $order->id,
            'is_paid' => (bool) $order->is_paid,
            'payment_status' => $order->payment_status,
            'paid_at' => $order->paid_at?->toDateTimeString(),
            'stripe_payment_intent_id' => $order->stripe_payment_intent_id,
        ]);
    }
}
