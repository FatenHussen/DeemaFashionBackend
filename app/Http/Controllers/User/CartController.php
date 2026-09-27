<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Cart\StoreItemRequest;
use App\Http\Requests\User\Cart\StoreRequest;
use App\Http\Requests\User\Cart\UpdateItemRequest;
use App\Models\User;
use App\Services\User\CalculateDeliveryPriceService;
use App\Services\User\CartService;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService)
    {
    }

    public function index()
    {
        $userId = auth('user')->id();

        return $this->sendResponse(data: [
            'items' => $this->cartService->list($userId),
            'checkout' => $this->cartService->checkoutSummary($userId),
        ]);
    }

    public function addItem(StoreItemRequest $request)
    {
        $item = $this->cartService->add(auth('user')->id(), $request->validated());

        return $this->sendResponse(data: $item, message: __('custom.Created'));
    }

    public function updateItem(UpdateItemRequest $request, int $item)
    {
        $updated = $this->cartService->update(auth('user')->id(), $item, $request->validated());

        return $this->sendResponse(data: $updated, message: __('custom.Updated'));
    }

    public function removeItem(int $item)
    {
        $this->cartService->remove(auth('user')->id(), $item);

        return $this->sendResponse(data: [], message: __('custom.Deleted'));
    }

    public function calculateDeliveryPrice(
        StoreRequest $request,
    ) {
        /** @var User $user */
        $user = auth('user')->user() ?? User::find(1);

        $price = CalculateDeliveryPriceService::handle(
            user: $user,
            items: $request->items,
            addressId: $request->address_id
        );

        return $this->sendResponse(data: $price);
    }
}
