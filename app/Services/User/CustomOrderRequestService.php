<?php

namespace App\Services\User;

use App\Enums\CustomOrderRequestStatus;
use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Exceptions\CustomExceptionWithMessage;
use App\Http\Resources\CustomOrderRequest\AllResource;
use App\Http\Resources\CustomOrderRequest\OneResource;
use App\Models\Admin;
use App\Models\CustomOrderRequest;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\UserAddress;
use App\Services\Base\NotificationService;
use App\Services\BaseService;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class CustomOrderRequestService extends BaseService
{
    public function __construct(
        CustomOrderRequest $model,
        private readonly NotificationService $notificationService,
    ) {
        $this->model = $model;
        $this->resource = OneResource::class;
        $this->collection = AllResource::class;
        $this->relations = ['address', 'paymentMethod', 'order.items', 'user'];
        $this->pagination = true;
        $this->searchableFields = ['id', 'description'];
        $this->sortableFields = ['id', 'created_at', 'expected_at'];
    }

    public function getAll($filters = [], $config = [])
    {
        $filters['user_id'] = auth('user')->id();

        return parent::getAll($filters, $config);
    }

    public function getOne($id)
    {
        $request = $this->model::query()
            ->with($this->relations)
            ->where('user_id', auth('user')->id())
            ->find($id);

        if (! $request) {
            throw new CustomExceptionWithMessage('custom.custom_order_requests.not_found', 404);
        }

        return new OneResource($request);
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query = parent::queryBuilder($query, $filters, $config);

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    public function create($data)
    {
        $userId = auth('user')->id();

        $address = UserAddress::query()
            ->where('id', $data['address_id'])
            ->where('user_id', $userId)
            ->first();

        if (! $address) {
            throw new CustomExceptionWithMessage('custom.custom_order_requests.address_not_found', 422);
        }

        $paymentMethodId = $data['payment_method_id'] ?? null;
        if ($paymentMethodId) {
            $paymentMethod = PaymentMethod::query()->active()->find($paymentMethodId);
            if (! $paymentMethod) {
                throw new CustomExceptionWithMessage('custom.custom_order_requests.payment_method_unavailable', 422);
            }
        }

        $paths = [];
        if (! empty($data['images'])) {
            foreach ($data['images'] as $image) {
                $paths[] = $image->store('custom_order_requests', 'public');
            }
        }

        $request = CustomOrderRequest::create([
            'user_id' => $userId,
            'user_address_id' => $address->id,
            'payment_method_id' => $paymentMethodId,
            'description' => $data['description'],
            'images' => $paths ?: null,
            'expected_at' => $data['expected_at'] ?? null,
            'status' => CustomOrderRequestStatus::PENDING_PRICING->value,
        ]);

        $this->notifyAdminsNewRequest($request);

        return new OneResource($request->load($this->relations));
    }

    public function approve(int $id)
    {
        return DB::transaction(function () use ($id) {
            $request = CustomOrderRequest::query()
                ->with(['order.items', 'paymentMethod'])
                ->where('user_id', auth('user')->id())
                ->lockForUpdate()
                ->find($id);

            if (! $request) {
                throw new CustomExceptionWithMessage('custom.custom_order_requests.not_found', 404);
            }

            if ($request->status !== CustomOrderRequestStatus::WAITING_APPROVAL) {
                throw new CustomExceptionWithMessage('custom.custom_order_requests.cannot_approve', 422);
            }

            $order = $request->order;
            if (! $order || $order->status !== OrderStatus::WAITING_APPROVAL->value) {
                throw new CustomExceptionWithMessage('custom.custom_order_requests.order_not_ready', 422);
            }

            $oldStatus = $order->status;
            $paymentMethod = $request->paymentMethod ?? $order->paymentMethod;

            $paidOnPlacement = $paymentMethod?->isPaidOnPlacement() ?? false;

            $order->update([
                'status' => OrderStatus::PREPARING->value,
                'is_paid' => $paidOnPlacement,
                'payment_status' => $paymentMethod?->requiresOnlineConfirmation()
                    ? ($order->payment_status ?? \App\Enums\PaymentStatus::PENDING->value)
                    : ($paidOnPlacement
                        ? \App\Enums\PaymentStatus::SUCCEEDED->value
                        : $order->payment_status),
                'paid_at' => $paidOnPlacement ? ($order->paid_at ?? now()) : $order->paid_at,
            ]);

            $order->items()->update([
                'item_status' => OrderStatus::PREPARING->value,
            ]);

            $request->update([
                'status' => CustomOrderRequestStatus::APPROVED->value,
            ]);

            OrderStatusChanged::dispatch(
                $order->fresh('items'),
                $oldStatus,
                OrderStatus::PREPARING->value,
                'user'
            );

            return new OneResource($request->fresh($this->relations));
        });
    }

    public function cancel(int $id)
    {
        return DB::transaction(function () use ($id) {
            $request = CustomOrderRequest::query()
                ->with(['order.items'])
                ->where('user_id', auth('user')->id())
                ->lockForUpdate()
                ->find($id);

            if (! $request) {
                throw new CustomExceptionWithMessage('custom.custom_order_requests.not_found', 404);
            }

            $status = $request->status;
            if (! in_array($status, [
                CustomOrderRequestStatus::PENDING_PRICING,
                CustomOrderRequestStatus::WAITING_APPROVAL,
            ], true)) {
                throw new CustomExceptionWithMessage('custom.custom_order_requests.cannot_cancel', 422);
            }

            if ($request->order && $request->status === CustomOrderRequestStatus::WAITING_APPROVAL) {
                $this->cancelLinkedOrder($request->order);
            }

            $request->update([
                'status' => CustomOrderRequestStatus::CANCELLED->value,
            ]);

            return new OneResource($request->fresh($this->relations));
        });
    }

    private function cancelLinkedOrder(Order $order): void
    {
        if ($order->status !== OrderStatus::WAITING_APPROVAL->value) {
            throw new CustomExceptionWithMessage('custom.custom_order_requests.cannot_cancel', 422);
        }

        $oldStatus = $order->status;
        $inventory = app(InventoryService::class);

        foreach ($order->items as $item) {
            if (! $item->is_external && $item->shop_product_variant_id) {
                $inventory->increaseStock((int) $item->shop_product_variant_id, (int) $item->quantity);
            }

            $item->update([
                'item_status' => OrderStatus::CANCELLED->value,
            ]);
        }

        $order->update([
            'status' => OrderStatus::CANCELLED->value,
        ]);

        OrderStatusChanged::dispatch(
            $order->fresh('items'),
            $oldStatus,
            OrderStatus::CANCELLED->value,
            'user'
        );
    }

    private function notifyAdminsNewRequest(CustomOrderRequest $request): void
    {
        $snippet = mb_substr($request->description, 0, 80);

        Admin::chunk(100, function ($admins) use ($request, $snippet) {
            foreach ($admins as $admin) {
                $this->notificationService->send(
                    $admin,
                    'طلب سريع جديد بانتظار التسعير',
                    "طلب مخصص #{$request->id}: {$snippet}",
                    [
                        'type' => 'custom_order_request',
                        'custom_order_request_id' => (string) $request->id,
                        'status' => CustomOrderRequestStatus::PENDING_PRICING->value,
                    ]
                );
            }
        });
    }
}
