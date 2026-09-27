<?php

namespace App\Http\Controllers\Admin\Order;

use App\Enums\OrderStatus;
use App\Http\Controllers\BaseIndexController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\FilterRequest;
use App\Http\Resources\Order\AllResource;
use App\Http\Resources\Order\OneResource;
use App\Services\Admin\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends BaseIndexController
{
    public function __construct(
        OrderService $service
    ) {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }



    /* =======================
       🔄 CHANGE ORDER STATUS
    ======================= */
    public function changeStatus(Request $request, int $orderId)
    {
        // Dashboard sometimes sends out_for_delivery; canonical value is out_delivery
        if ($request->input('status') === 'out_for_delivery') {
            $request->merge(['status' => OrderStatus::OUT_DELIVERY->value]);
        }

        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_column(OrderStatus::cases(), 'value')),
            'rejection_reason' => 'nullable|string|max:1000|required_if:status,' . OrderStatus::CANCELLED_BY_ADMIN->value,
        ]);

        $order = $this->service->changeOrderStatus(
            $orderId,
            $data['status'],
            $data['rejection_reason'] ?? null
        );

        return $this->sendResponse(
            data: new AllResource($order),
            message: __('custom.orders.status_updated_successfully')
        );
    }

    /* =======================
       📅 SET DELIVERY DAY AND TIME
    ======================= */
    public function setScheduledDelivery(Request $request, int $orderId)
    {
        $raw = $request->input('scheduled_delivery_at');
        if (is_string($raw) && trim($raw) !== '') {
            try {
                $request->merge([
                    'scheduled_delivery_at' => Carbon::parse($raw)->format('Y-m-d H:i'),
                ]);
            } catch (\Throwable) {
                // Leave the raw value so validation rejects it.
            }
        }

        $data = $request->validate([
            'scheduled_delivery_at' => ['nullable', 'date_format:Y-m-d H:i'],
        ]);

        $order = $this->service->setScheduledDelivery(
            $orderId,
            $data['scheduled_delivery_at'] ?? null
        );

        return $this->sendResponse(
            data: new OneResource($order),
            message: __('custom.orders.scheduled_delivery_updated')
        );
    }

    /* =======================
       🚚 ASSIGN DRIVER
    ======================= */
    public function assignDriver(Request $request, int $orderId)
    {
        $data = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
        ]);

        $this->service->assignDriver($orderId, $data['driver_id']);

        return $this->sendResponse(
            message: __('custom.orders.assigned_to_driver_successfully')
        );
    }

    /* =======================
       🧩 GET ORDERS TO ASSIGN
       FILTERED BY DRIVER COVERAGE
    ======================= */
    public function ordersToAssign(Request $request)
    {
        $data = $request->validate([
            'filter_by_driver_coverage' => ['nullable', 'boolean'],
            'driver_id' => ['nullable', 'required_if:filter_by_driver_coverage,1,true', 'exists:drivers,id'],
            'status' => ['nullable', Rule::in(['pending', 'preparing'])],
            'is_instant_delivery' => ['nullable', 'boolean'],
        ]);

        $filterByDriverCoverage = (bool) ($data['filter_by_driver_coverage'] ?? false);

        $orders = $this->service->ordersToAssignByDriver(
            driverId: isset($data['driver_id']) ? (int) $data['driver_id'] : null,
            status: $data['status'] ?? null,
            isInstantDelivery: $data['is_instant_delivery'] ?? true,
            filterByDriverCoverage: $filterByDriverCoverage,
        );

        return $this->sendResponse(
            data: $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'value' => ($order->order_code ?? $order->id) . ' . ' . ($order->user->name ?? '-'),
                ];
            })->values(),
        );
    }

    /* =======================
       🧩 CHANGE ITEM STATUS
    ======================= */
    public function changeItemStatus(Request $request, int $itemId)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,preparing,out_delivery,delivered',
        ]);

        $item = $this->service->changeItemStatus(
            $itemId,
            $data['status']
        );

        return $this->sendResponse(
            data: $item,
            message: __('custom.orders.item_status_updated_successfully')
        );
    }
}
