<?php

namespace App\Http\Controllers\User\Currency;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Resources\Currency\OneResource;
use App\Http\Requests\User\Currency\UpdateUserCurrencyRequest;
use App\Services\User\CurrencyService;
use Illuminate\Http\JsonResponse;

class CurrencyController extends BaseCRUDController
{
    public function __construct(
        protected CurrencyService $currencyService
    ) {
        $this->service = $currencyService;
    }


    public function getUserCurrency(): JsonResponse
    {
        $user = auth('user')->user();
        $currency = $this->currencyService->getUserCurrency($user);

        return $this->sendResponse(
            (new OneResource($currency))->withPreferenceSource(
                $this->currencyService->getPreferenceSource($user, $currency)
            ),
            __('custom.user_currency_retrieved_successfully')
        );
    }

    public function updateUserCurrency(UpdateUserCurrencyRequest $request): JsonResponse
    {
        $user = auth('user')->user();
        $currency = $this->currencyService->updateUserCurrency($user, $request->currency_id);

        return $this->sendResponse(
            (new OneResource($currency))->withPreferenceSource('user'),
            __('custom.user_currency_updated_successfully')
        );
    }
}
