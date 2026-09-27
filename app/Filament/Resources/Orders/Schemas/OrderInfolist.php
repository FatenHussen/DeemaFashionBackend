<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class OrderInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist
            ->columns(1)
            ->schema([
                Tabs::make('order_info_tabs')
                    ->tabs([
                        Tab::make(__('custom.orders.sections.order_info'))
                            ->schema([
                                Section::make(__('custom.orders.sections.order_info'))
                                    ->schema([
                                        Infolists\Components\TextEntry::make('id')
                                            ->label(__('custom.orders.order_number')),

                                        Infolists\Components\TextEntry::make('delivery_code')
                                            ->label(__('custom.orders.delivery_code'))
                                            ->copyable()
                                            ->badge()
                                            ->color('primary'),

                                        Infolists\Components\TextEntry::make('status')
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
                                            }),

                                        Infolists\Components\IconEntry::make('is_instant_delivery')
                                            ->label(__('custom.orders.is_instant_delivery'))
                                            ->boolean(),

                                        Infolists\Components\TextEntry::make('scheduled_delivery_at')
                                            ->label(__('custom.orders.scheduled_delivery_at'))
                                            ->dateTime('Y-m-d H:i')
                                            ->placeholder('—'),

                                        Infolists\Components\TextEntry::make('created_at')
                                            ->label(__('custom.orders.order_date'))
                                            ->dateTime(),
                                    ])
                                    ->columns(3),
                            ]),

                        Tab::make(__('custom.orders.sections.customer_info'))
                            ->schema([
                                Section::make(__('custom.orders.sections.customer_info'))
                                    ->schema([
                                        Infolists\Components\TextEntry::make('user.name')
                                            ->label(__('custom.orders.customer_name')),

                                        Infolists\Components\TextEntry::make('user.phone')
                                            ->label(__('custom.orders.customer_phone')),

                                        Infolists\Components\TextEntry::make('user.email')
                                            ->label(__('custom.orders.customer_email')),
                                    ])
                                    ->columns(3),
                            ]),

                        Tab::make(__('custom.orders.sections.address_info'))
                            ->schema([
                                Section::make(__('custom.orders.sections.address_info'))
                                    ->schema([
                                        Infolists\Components\TextEntry::make('address.address')
                                            ->label(__('custom.orders.address'))
                                            ->columnSpanFull(),

                                        Infolists\Components\TextEntry::make('address.area.name')
                                            ->label(__('custom.orders.area')),

                                        Infolists\Components\TextEntry::make('address.city.name')
                                            ->label(__('custom.orders.city')),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make(__('custom.orders.sections.driver_info'))
                            ->visible(fn($record) => $record->driver_id !== null)
                            ->schema([
                                Section::make(__('custom.orders.sections.driver_info'))
                                    ->schema([
                                        Infolists\Components\TextEntry::make('driver.name')
                                            ->label(__('custom.orders.driver_name')),

                                        Infolists\Components\TextEntry::make('driver.phone')
                                            ->label(__('custom.orders.driver_phone')),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make(__('custom.orders.sections.pricing'))
                            ->schema([
                                Section::make(__('custom.orders.sections.pricing'))
                                    ->schema([
                                        Infolists\Components\TextEntry::make('subtotal')
                                            ->label(__('custom.orders.subtotal'))
                                            ->money('USD'),

                                        Infolists\Components\TextEntry::make('basket_discount')
                                            ->label(__('custom.orders.basket_discount'))
                                            ->money('USD'),

                                        Infolists\Components\TextEntry::make('coupon_discount')
                                            ->label(__('custom.orders.coupon_discount'))
                                            ->money('USD'),

                                        Infolists\Components\TextEntry::make('delivery_price')
                                            ->label(__('custom.orders.delivery_price'))
                                            ->money('USD'),

                                        Infolists\Components\TextEntry::make('total')
                                            ->label(__('custom.orders.total'))
                                            ->money('USD')
                                            ->size('lg')
                                            ->weight('bold'),

                                        Infolists\Components\TextEntry::make('automatic_promotions_snapshot')
                                            ->label(__('custom.orders.automatic_promotions_snapshot'))
                                            ->columnSpanFull()
                                            ->visible(fn ($record) => ! empty($record->automatic_promotions_snapshot))
                                            ->formatStateUsing(
                                                fn ($state) => $state
                                                    ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                                    : ''
                                            ),
                                    ])
                                    ->columns(5),
                            ]),

                        Tab::make(__('custom.orders.sections.items'))
                            ->schema([
                                Section::make(__('custom.orders.sections.items'))
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('items')
                                            ->label('')
                                            ->getStateUsing(function ($record) {
                                                $user = Auth::guard('vendor-user')->user();
                                                if (!$user) {
                                                    return [];
                                                }

                                                $shopIds = $user->shops()->pluck('shops.id')->toArray();
                                                $items = $record->items ?? collect();

                                                return $items->filter(function ($item) use ($shopIds) {
                                                    $shopId = $item->shopProductVariant?->shop_id;
                                                    return $shopId && in_array($shopId, $shopIds);
                                                })->values();
                                            })
                                            ->schema([
                                                Infolists\Components\TextEntry::make('shopProductVariant.productVariant.product.name')
                                                    ->label(__('custom.orders.product')),

                                                Infolists\Components\TextEntry::make('item_status')
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
                                                    }),

                                                Infolists\Components\TextEntry::make('quantity')
                                                    ->label(__('custom.orders.quantity')),

                                                Infolists\Components\TextEntry::make('price')
                                                    ->label(__('custom.orders.price'))
                                                    ->money('USD'),

                                                Infolists\Components\TextEntry::make('total')
                                                    ->label(__('custom.orders.total'))
                                                    ->money('USD')
                                                    ->getStateUsing(fn($record) => $record->price * $record->quantity),
                                            ])
                                            ->columns(5),
                                    ]),
                            ]),

                        Tab::make(__('custom.orders.sections.timeline'))
                            ->schema([
                                Section::make(__('custom.orders.sections.timeline'))
                                    ->schema([
                                        Infolists\Components\TextEntry::make('pending_at')
                                            ->label(__('custom.orders.pending_at'))
                                            ->dateTime(),

                                        Infolists\Components\TextEntry::make('preparing_at')
                                            ->label(__('custom.orders.preparing_at'))
                                            ->dateTime(),

                                        Infolists\Components\TextEntry::make('out_delivery_at')
                                            ->label(__('custom.orders.out_delivery_at'))
                                            ->dateTime(),

                                        Infolists\Components\TextEntry::make('delivered_at')
                                            ->label(__('custom.orders.delivered_at'))
                                            ->dateTime(),
                                    ])
                                    ->columns(4)
                                    ->collapsible(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
