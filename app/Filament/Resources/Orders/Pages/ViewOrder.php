<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('set_scheduled_delivery')
                ->label(__('custom.orders.actions.set_scheduled_delivery'))
                ->icon('heroicon-o-calendar-days')
                ->color('gray')
                ->visible(fn () => ! in_array($this->record->status, [
                    OrderStatus::DELIVERED->value,
                    OrderStatus::CANCELLED->value,
                    OrderStatus::CANCELLED_BY_ADMIN->value,
                    OrderStatus::RETURNED_BY_USER->value,
                ], true))
                ->fillForm(fn () => [
                    'scheduled_delivery_at' => $this->record->scheduled_delivery_at,
                ])
                ->form([
                    DateTimePicker::make('scheduled_delivery_at')
                        ->label(__('custom.orders.scheduled_delivery_at'))
                        ->seconds(false)
                        ->native(false),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'scheduled_delivery_at' => $data['scheduled_delivery_at'] ?: null,
                    ]);

                    Notification::make()
                        ->title(__('custom.orders.scheduled_delivery_updated'))
                        ->success()
                        ->send();
                }),

            Action::make('start_preparing')
                ->label(__('custom.orders.actions.start_preparing'))
                ->icon('heroicon-o-clock')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn() => $this->record->status === OrderStatus::PENDING->value && $this->isFullVendorOrder())
                ->action(function () {
                    $this->record->update([
                        'status' => OrderStatus::PREPARING->value,
                        'preparing_at' => now(),
                    ]);
                    $this->record->items()->update(['item_status' => OrderStatus::PREPARING->value]);

                    Notification::make()
                        ->title(__('custom.orders.actions.status_updated'))
                        ->success()
                        ->send();
                }),

            Action::make('ready_for_delivery')
                ->label(__('custom.orders.actions.ready_for_delivery'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn() => $this->record->status === OrderStatus::PREPARING->value && $this->isFullVendorOrder())
                ->action(function () {
                    $this->record->update([
                        'status' => OrderStatus::OUT_DELIVERY->value,
                        'out_delivery_at' => now(),
                    ]);
                    $this->record->items()->update(['item_status' => OrderStatus::OUT_DELIVERY->value]);

                    Notification::make()
                        ->title(__('custom.orders.actions.ready_notification'))
                        ->success()
                        ->send();
                }),

            Action::make('update_item_status')
                ->label(__('custom.orders.actions.update_item_status'))
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('primary')
                ->visible(fn() => ! $this->isFullVendorOrder())
                ->form([
                    Select::make('item_id')
                        ->label(__('custom.orders.product'))
                        ->options(fn() => $this->getVendorItemOptions())
                        ->searchable()
                        ->required(),
                    Select::make('status')
                        ->label(__('custom.orders.status'))
                        ->options([
                            OrderStatus::PENDING->value => __('custom.orders.statuses.pending'),
                            OrderStatus::PREPARING->value => __('custom.orders.statuses.preparing'),
                            OrderStatus::OUT_DELIVERY->value => __('custom.orders.statuses.out_delivery'),
                            OrderStatus::DELIVERED->value => __('custom.orders.statuses.delivered'),
                            OrderStatus::CANCELLED->value => __('custom.orders.statuses.cancelled'),
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $shopIds = $this->getVendorShopIds();

                    $item = OrderItem::where('id', $data['item_id'])
                        ->whereHas('shopProductVariant', function ($q) use ($shopIds) {
                            $q->whereIn('shop_id', $shopIds);
                        })
                        ->firstOrFail();

                    $item->update(['item_status' => $data['status']]);

                    Notification::make()
                        ->title(__('custom.orders.actions.status_updated'))
                        ->success()
                        ->send();
                }),

            Action::make('update_vendor_items_status')
                ->label(__('custom.orders.actions.update_items_status'))
                ->icon('heroicon-o-arrows-up-down')
                ->color('warning')
                ->visible(fn() => ! $this->isFullVendorOrder())
                ->requiresConfirmation()
                ->form([
                    Select::make('status')
                        ->label(__('custom.orders.status'))
                        ->options([
                            OrderStatus::PENDING->value => __('custom.orders.statuses.pending'),
                            OrderStatus::PREPARING->value => __('custom.orders.statuses.preparing'),
                            OrderStatus::OUT_DELIVERY->value => __('custom.orders.statuses.out_delivery'),
                            OrderStatus::DELIVERED->value => __('custom.orders.statuses.delivered'),
                            OrderStatus::CANCELLED->value => __('custom.orders.statuses.cancelled'),
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $shopIds = $this->getVendorShopIds();
                    if (empty($shopIds)) {
                        return;
                    }

                    $itemIds = $this->record->items
                        ->filter(function ($item) use ($shopIds) {
                            $shopId = $item->shopProductVariant?->shop_id;
                            return $shopId && in_array($shopId, $shopIds);
                        })
                        ->pluck('id')
                        ->all();

                    if (!empty($itemIds)) {
                        OrderItem::whereIn('id', $itemIds)->update(['item_status' => $data['status']]);
                    }

                    Notification::make()
                        ->title(__('custom.orders.actions.status_updated'))
                        ->success()
                        ->send();
                }),
        ];
    }

    private function getVendorShopIds(): array
    {
        $user = Auth::guard('vendor-user')->user();
        if (!$user) {
            return [];
        }

        return $user->shops()->pluck('shops.id')->toArray();
    }

    private function isFullVendorOrder(): bool
    {
        $shopIds = $this->getVendorShopIds();
        if (empty($shopIds)) {
            return false;
        }

        $items = $this->record->items ?? collect();
        if ($items->isEmpty()) {
            return false;
        }

        $vendorItemCount = $items->filter(function ($item) use ($shopIds) {
            $shopId = $item->shopProductVariant?->shop_id;
            return $shopId && in_array($shopId, $shopIds);
        })->count();

        return $vendorItemCount === $items->count();
    }

    private function getVendorItemOptions(): array
    {
        $shopIds = $this->getVendorShopIds();
        if (empty($shopIds)) {
            return [];
        }

        return $this->record->items
            ->filter(function ($item) use ($shopIds) {
                $shopId = $item->shopProductVariant?->shop_id;
                return $shopId && in_array($shopId, $shopIds);
            })
            ->mapWithKeys(function ($item) {
                $name = $item->product_name ?? $item->shopProductVariant?->productVariant?->product?->name ?? ('#' . $item->id);
                return [$item->id => $name];
            })
            ->toArray();
    }
}
