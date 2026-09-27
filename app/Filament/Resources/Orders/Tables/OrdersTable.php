<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Auth;
use Filament\Tables;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('custom.orders.order_number'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('custom.orders.customer'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('custom.orders.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        OrderStatus::PENDING->value => 'warning',
                        OrderStatus::PREPARING->value => 'info',
                        OrderStatus::OUT_DELIVERY->value => 'primary',
                        OrderStatus::DELIVERED->value => 'success',
                        OrderStatus::CANCELLED->value => 'danger',
                        OrderStatus::CANCELLED_BY_ADMIN->value => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        OrderStatus::PENDING->value => __('custom.orders.statuses.pending'),
                        OrderStatus::PREPARING->value => __('custom.orders.statuses.preparing'),
                        OrderStatus::OUT_DELIVERY->value => __('custom.orders.statuses.out_delivery'),
                        OrderStatus::DELIVERED->value => __('custom.orders.statuses.delivered'),
                        OrderStatus::CANCELLED->value => __('custom.orders.statuses.cancelled'),
                        OrderStatus::CANCELLED_BY_ADMIN->value => __('custom.orders.statuses.cancelled_by_admin'),
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label(__('custom.orders.total'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->label(__('custom.orders.total_quantity'))
                    ->sortable(),


                Tables\Columns\IconColumn::make('is_instant_delivery')
                    ->label(__('custom.orders.is_instant_delivery'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('scheduled_delivery_at')
                    ->label(__('custom.orders.scheduled_delivery_at'))
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('custom.orders.order_date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('custom.orders.status'))
                    ->options([
                        OrderStatus::PENDING->value => __('custom.orders.statuses.pending'),
                        OrderStatus::PREPARING->value => __('custom.orders.statuses.preparing'),
                        OrderStatus::OUT_DELIVERY->value => __('custom.orders.statuses.out_delivery'),
                        OrderStatus::DELIVERED->value => __('custom.orders.statuses.delivered'),
                        OrderStatus::CANCELLED->value => __('custom.orders.statuses.cancelled'),
                        OrderStatus::CANCELLED_BY_ADMIN->value => __('custom.orders.statuses.cancelled_by_admin'),
                    ]),

                Tables\Filters\TernaryFilter::make('is_instant_delivery')
                    ->label(__('custom.orders.is_instant_delivery'))
                    ->placeholder(__('custom.all'))
                    ->trueLabel(__('custom.orders.delivery_types.instant'))
                    ->falseLabel(__('custom.orders.delivery_types.normal')),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('start_preparing')
                    ->label(__('custom.orders.actions.start_preparing'))
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === OrderStatus::PENDING->value && static::isFullVendorOrder($record))
                    ->action(function ($record) {
                        $record->update([
                            'status' => OrderStatus::PREPARING->value,
                            'preparing_at' => now(),
                        ]);
                        $record->items()->update(['item_status' => OrderStatus::PREPARING->value]);


                        \Filament\Notifications\Notification::make()
                            ->title(__('custom.orders.actions.status_updated'))
                            ->success()
                            ->send();
                    }),

                Action::make('ready_for_delivery')
                    ->label(__('custom.orders.actions.ready_for_delivery'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === OrderStatus::PREPARING->value && static::isFullVendorOrder($record))
                    ->action(function ($record) {
                        $record->update([
                            'status' => OrderStatus::OUT_DELIVERY->value,
                            'out_delivery_at' => now(),
                        ]);
                        $record->items()->update(['item_status' => OrderStatus::OUT_DELIVERY->value]);

                        \Filament\Notifications\Notification::make()
                            ->title(__('custom.orders.actions.ready_notification'))
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function getVendorShopIds(): array
    {
        $user = Auth::guard('vendor-user')->user();
        if (!$user) {
            return [];
        }

        return $user->shops()->pluck('shops.id')->toArray();
    }

    protected static function isFullVendorOrder($record): bool
    {
        $shopIds = static::getVendorShopIds();
        if (empty($shopIds)) {
            return false;
        }

        $items = $record->items ?? $record->items()->get();
        if ($items->isEmpty()) {
            return false;
        }

        $vendorItemCount = $items->filter(function ($item) use ($shopIds) {
            $shopId = $item->shopProductVariant?->shop_id;
            return $shopId && in_array($shopId, $shopIds);
        })->count();

        return $vendorItemCount === $items->count();
    }
}
