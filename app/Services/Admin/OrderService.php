<?php

namespace App\Services\Admin;

use App\Enums\OrderStatus;
use App\Events\OrderItemStatusChanged;
use App\Exceptions\CustomExceptionWithMessage;
use App\Http\Resources\Order\OneResource;
use App\Http\Resources\Order\AllResource;
use App\Events\OrderStatusChanged;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Shared\DriverCoverageService;
use Illuminate\Database\Eloquent\Builder;
use App\Services\Base\NotificationService;
use App\Services\BaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class OrderService extends BaseService
{
    public function __construct(
        Order $model,
        private readonly DriverCoverageService $driverCoverageService
    ) {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->relations = ['driver', 'user', 'items'];
        $this->searchableFields = ['id'];
    }

    /* =======================
       🔄 ORDER STATUS TRANSITION
    ======================= */
    private function validateOrderStatusTransition(string $from, string $to): void
    {
        $allowed = [
            OrderStatus::PENDING->value => [
                OrderStatus::PREPARING->value,
                OrderStatus::CANCELLED->value,
                OrderStatus::CANCELLED_BY_ADMIN->value,
            ],
            OrderStatus::WAITING_APPROVAL->value => [
                OrderStatus::PREPARING->value,
                OrderStatus::CANCELLED->value,
                OrderStatus::CANCELLED_BY_ADMIN->value,
            ],
            OrderStatus::PREPARING->value => [
                OrderStatus::OUT_DELIVERY->value,
                OrderStatus::CANCELLED->value,
                OrderStatus::CANCELLED_BY_ADMIN->value,
            ],
            OrderStatus::OUT_DELIVERY->value => [
                OrderStatus::DELIVERED->value,
                OrderStatus::CANCELLED->value,
                OrderStatus::CANCELLED_BY_ADMIN->value,
            ],
        ];

        if (! isset($allowed[$from]) || ! in_array($to, $allowed[$from])) {
            throw new CustomExceptionWithMessage(
                'custom.orders.cannot_change_status',
                400,
                ['from' => $from, 'to' => $to]
            );
        }
    }

    /* =======================
       🔄 ITEM STATUS TRANSITION
    ======================= */
    private function validateItemStatusTransition(string $from, string $to): void
    {
        $allowed = [
            OrderStatus::PENDING->value => [
                OrderStatus::PREPARING->value,
            ],
            OrderStatus::PREPARING->value => [
                OrderStatus::OUT_DELIVERY->value,
            ],
            OrderStatus::OUT_DELIVERY->value => [
                OrderStatus::DELIVERED->value,
            ],
        ];

        if (! isset($allowed[$from]) || ! in_array($to, $allowed[$from])) {
            throw new CustomExceptionWithMessage(
                'custom.orders.cannot_change_item_status',
                400,
                ['from' => $from, 'to' => $to]
            );
        }
    }

    /* =======================
       🔄 CHANGE ORDER STATUS
    ======================= */
    public function changeOrderStatus(int $orderId, string $newStatus, ?string $rejectionReason = null)
    {
        return DB::transaction(function () use ($orderId, $newStatus, $rejectionReason) {

            $order = Order::with('items')
                ->lockForUpdate()
                ->findOrFail($orderId);

            if ($order->status === OrderStatus::DELIVERED->value) {
                throw new CustomExceptionWithMessage(
                    'custom.orders.delivered_cannot_change'
                );
            }

            $this->validateOrderStatusTransition(
                $order->status,
                $newStatus
            );

            if ($newStatus === OrderStatus::CANCELLED_BY_ADMIN->value && blank($rejectionReason)) {
                throw new CustomExceptionWithMessage(
                    'custom.orders.rejection_reason_required'
                );
            }

            $oldStatus = $order->status;

            $order->update([
                'status' => $newStatus,
                'rejection_reason' => $newStatus === OrderStatus::CANCELLED_BY_ADMIN->value
                    ? $rejectionReason
                    : null,
            ]);

            $order->items()->update([
                'item_status' => $newStatus,
            ]);
            $order = $order->fresh([
                'items.shopProductVariant.shop',
                'user',
                'driver',
                'address',
                'paymentMethod',
                'coupon',
            ]);

            OrderStatusChanged::dispatch(
                $order,
                $oldStatus,
                $newStatus,
                'admin'
            );

            return $order;
        });
    }

    /* =======================
       🧩 CHANGE ITEM STATUS
    ======================= */
    public function changeItemStatus(int $itemId, string $newStatus)
    {
        return DB::transaction(function () use ($itemId, $newStatus) {

            $item = OrderItem::with('order')
                ->lockForUpdate()
                ->findOrFail($itemId);

            $order = $item->order;

            if ($order->status === OrderStatus::DELIVERED->value) {
                throw new CustomExceptionWithMessage(
                    'custom.orders.already_delivered'
                );
            }

            $this->validateItemStatusTransition(
                $item->item_status,
                $newStatus
            );

            $oldStatus = $item->item_status;

            $item->update([
                'item_status' => $newStatus,
            ]);

            // OrderItemStatusChanged::dispatch(
            //     $item->fresh(),
            //     $oldStatus,
            //     $newStatus,
            //     'admin'
            // );

            // لو كل العناصر صاروا بنفس الحالة → حدّث الطلب
            if (
                $order->items()
                ->where('item_status', '!=', $newStatus)
                ->doesntExist()
            ) {
                $oldOrderStatus = $order->status;

                $order->update([
                    'status' => $newStatus
                ]);

                OrderStatusChanged::dispatch(
                    $order->fresh(),
                    $oldOrderStatus,
                    $newStatus,
                    'admin'
                );
            }

            return $item->fresh();
        });
    }

    /* =======================
       📅 SET DELIVERY DAY AND TIME
    ======================= */
    public function setScheduledDelivery(int $orderId, ?string $scheduledDeliveryAt)
    {
        $order = Order::findOrFail($orderId);

        $closed = [
            OrderStatus::DELIVERED->value,
            OrderStatus::CANCELLED->value,
            OrderStatus::CANCELLED_BY_ADMIN->value,
            OrderStatus::RETURNED_BY_USER->value,
        ];

        if (in_array($order->status, $closed, true)) {
            throw new CustomExceptionWithMessage(
                'custom.orders.cannot_set_scheduled_delivery'
            );
        }

        $order->update([
            'scheduled_delivery_at' => $scheduledDeliveryAt
                ? Carbon::parse($scheduledDeliveryAt)->seconds(0)
                : null,
        ]);

        return $order->fresh([
            'items.shopProductVariant.shop',
            'user',
            'driver',
            'address',
            'paymentMethod',
            'coupon',
        ]);
    }

    /* =======================
       🚚 ASSIGN DRIVER
    ======================= */
    public function assignDriver(int $orderId, int $driverId, ?string $scheduledDeliveryAt = null)
    {
        return DB::transaction(function () use ($orderId, $driverId, $scheduledDeliveryAt) {

            $order = Order::lockForUpdate()->findOrFail($orderId);
            $assignableStatuses = [
                OrderStatus::PENDING->value,
                OrderStatus::PREPARING->value,
            ];

            if (! in_array($order->status, $assignableStatuses, true)) {
                throw new CustomExceptionWithMessage(
                    'custom.orders.invalid_order_state'
                );
            }

            if ($order->driver_id !== null) {
                throw new CustomExceptionWithMessage(
                    'custom.orders.already_assigned'
                );
            }

            $payload = [
                'driver_id'   => $driverId,
                'assigned_by' => 'admin',
            ];

            if ($scheduledDeliveryAt) {
                $payload['scheduled_delivery_at'] = Carbon::parse($scheduledDeliveryAt)->seconds(0);
            }

            $order->update($payload);

            OrderStatusChanged::dispatch(
                $order->fresh(),
                $order->status,
                $order->status,
                'admin'
            );
            return $order;
        });
    }

    /**
     * Orders waiting for assignment (pending/preparing).
     * Optionally filter by a driver's coverage when requested by admin.
     */
    public function ordersToAssignByDriver(
        ?int $driverId = null,
        ?string $status = null,
        bool $isInstantDelivery = true,
        bool $filterByDriverCoverage = false
    )
    {
        $query = Order::query()
            ->whereNull('driver_id')
            ->where('is_instant_delivery', $isInstantDelivery)
            ->where(function (Builder $q) use ($status): void {
                if ($status) {
                    $q->where('status', $status);
                    return;
                }

                $q->whereIn('status', [
                    OrderStatus::PENDING->value,
                    OrderStatus::PREPARING->value,
                ]);
            });

        if ($filterByDriverCoverage) {
            $driver = $this->driverCoverageService->loadDriverWithCoverage($driverId);
            $this->driverCoverageService->applyInstantOrderCoverage($query, $driver);
        }

        return $query
            ->with([
                'user',
            ])
            ->latest()
            ->get();
    }
}
