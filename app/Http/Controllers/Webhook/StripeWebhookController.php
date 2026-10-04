<?php

namespace App\Http\Controllers\Webhook;

use App\Exceptions\CustomExceptionWithMessage;
use App\Http\Controllers\Controller;
use App\Services\Stripe\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(
        private StripePaymentService $stripePaymentService
    ) {}

    public function __invoke(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $result = $this->stripePaymentService->handleWebhook($payload, $signature);
        } catch (CustomExceptionWithMessage $e) {
            Log::warning('Stripe webhook rejected', [
                'key' => $e->getTranslationKey(),
            ]);

            return response()->json([
                'status' => false,
                'message' => __($e->getTranslationKey(), $e->getReplacements()),
            ], $e->getStatus());
        } catch (\Throwable $e) {
            Log::error('Stripe webhook error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Webhook error',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'ok',
            'data' => $result,
        ]);
    }
}
