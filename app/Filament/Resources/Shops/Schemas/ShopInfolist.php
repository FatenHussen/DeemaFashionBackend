<?php

namespace App\Filament\Resources\Shops\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ShopInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist->columns(1)->schema([
            Tabs::make('shop_info_tabs')
                ->tabs([
                    Tab::make(__('custom.shops.sections.basic_info'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make(__('custom.shops.sections.basic_info'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('name')
                                        ->label(__('custom.shops.name'))
                                        ->weight('bold')
                                        ->color('primary')
                                        ->icon('heroicon-o-building-storefront')
                                        ->columnSpan(2),

                                    Infolists\Components\ImageEntry::make('logo')
                                        ->disk('public')
                                        ->label(__('custom.shops.logo'))
                                        ->columnSpan(2),

                                    Infolists\Components\TextEntry::make('description')
                                        ->label(__('custom.shops.description'))
                                        ->columnSpanFull()
                                        ->prose()
                                        ->visible(fn($record) => !empty($record->description)),

                                    Infolists\Components\TextEntry::make('vendor.name')
                                        ->label(__('custom.shops.vendor'))
                                        ->badge()
                                        ->color('warning')
                                        ->icon('heroicon-o-user-group'),

                                    Infolists\Components\TextEntry::make('area.name')
                                        ->label(__('custom.shops.area'))
                                        ->badge()
                                        ->color('info')
                                        ->icon('heroicon-o-map-pin'),
                                ])
                                ->columns(4)
                                ->collapsible(),

                            Section::make(__('custom.images'))
                                ->schema([
                                    Infolists\Components\ImageEntry::make('cover_images_display')
                                        ->label(__('custom.shops.cover_images'))
                                        ->disk('public')
                                        ->getStateUsing(function ($record) {
                                            // Try to get from media relationship first
                                            $mediaImages = $record->coverImages()->get();
                                            if ($mediaImages->count() > 0) {
                                                return $mediaImages->pluck('path')->toArray();
                                            }

                                            // Fallback to cover_images field
                                            if (!empty($record->cover_images) && is_array($record->cover_images)) {
                                                return $record->cover_images;
                                            }

                                            return [];
                                        })
                                        ->columnSpanFull()
                                        ->visible(function ($record) {
                                            $hasMedia = $record->coverImages()->count() > 0;
                                            $hasImages = !empty($record->cover_images) && is_array($record->cover_images) && count($record->cover_images) > 0;
                                            return $hasMedia || $hasImages;
                                        }),

                                    Infolists\Components\TextEntry::make('no_cover_images')
                                        ->label('')
                                        ->default(__('custom.no_images'))
                                        ->color('gray')
                                        ->columnSpanFull()
                                        ->visible(function ($record) {
                                            $hasMedia = $record->coverImages()->count() > 0;
                                            $hasImages = !empty($record->cover_images) && is_array($record->cover_images) && count($record->cover_images) > 0;
                                            return !$hasMedia && !$hasImages;
                                        }),
                                ])
                                ->collapsible(),
                        ]),

                    // Tab 2: معلومات الاتصال والموقع
                    Tab::make(__('custom.location'))
                        ->icon('heroicon-o-phone')
                        ->schema([
                            Section::make(__('custom.shops.sections.contact_info'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('phone')
                                        ->label(__('custom.shops.phone'))
                                        ->icon('heroicon-o-phone')
                                        ->copyable(),

                                    Infolists\Components\TextEntry::make('mobile')
                                        ->label(__('custom.shops.mobile'))
                                        ->icon('heroicon-o-device-phone-mobile')
                                        ->copyable(),

                                    Infolists\Components\TextEntry::make('email')
                                        ->label(__('custom.shops.email'))
                                        ->icon('heroicon-o-envelope')
                                        ->copyable(),
                                ])
                                ->columns(3)
                                ->collapsible(),

                            Section::make(__('custom.shops.sections.location_info'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('address')
                                        ->label(__('custom.shops.address'))
                                        ->columnSpanFull()
                                        ->icon('heroicon-o-map'),

                                    Infolists\Components\TextEntry::make('lat')
                                        ->label(__('custom.shops.lat'))
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-globe-alt')
                                        ->copyable(),

                                    Infolists\Components\TextEntry::make('lng')
                                        ->label(__('custom.shops.lng'))
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-globe-alt')
                                        ->copyable(),
                                ])
                                ->columns(2)
                                ->collapsible(),
                        ]),

                    // Tab 3: التقييمات والحالة
                    Tab::make(__('custom.shops.sections.ratings_info'))
                        ->icon('heroicon-o-star')
                        ->schema([
                            Section::make(__('custom.shops.sections.ratings_info'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('average_rating')
                                        ->label(__('custom.shops.rating'))
                                        ->badge()
                                        ->color('success')
                                        ->icon('heroicon-o-star')
                                        ->suffix(' ⭐')
                                        ->default('0'),

                                    Infolists\Components\TextEntry::make('ratings_count')
                                        ->label(__('custom.shops.ratings_count'))
                                        ->badge()
                                        ->color('primary')
                                        ->icon('heroicon-o-chat-bubble-left-right')
                                        ->default('0'),

                                    Infolists\Components\IconEntry::make('is_active')
                                        ->label(__('custom.shops.is_active'))
                                        ->boolean()
                                        ->trueIcon('heroicon-o-check-circle')
                                        ->falseIcon('heroicon-o-x-circle')
                                        ->trueColor('success')
                                        ->falseColor('danger'),

                                    Infolists\Components\IconEntry::make('is_free_delivery')
                                        ->label(__('custom.shops.is_free_delivery'))
                                        ->boolean()
                                        ->trueIcon('heroicon-o-truck')
                                        ->falseIcon('heroicon-o-x-circle')
                                        ->trueColor('success')
                                        ->falseColor('gray'),

                                    Infolists\Components\TextEntry::make('min_order_amount')
                                        ->label(__('custom.shops.min_order_amount'))
                                        ->placeholder('—'),

                                    Infolists\Components\TextEntry::make('delivery_min_hours')
                                        ->label(__('custom.shops.delivery_min_hours'))
                                        ->suffix(' ' . __('custom.shops.hours'))
                                        ->placeholder('—'),

                                    Infolists\Components\TextEntry::make('delivery_max_hours')
                                        ->label(__('custom.shops.delivery_max_hours'))
                                        ->suffix(' ' . __('custom.shops.hours'))
                                        ->placeholder('—'),
                                ])
                                ->columns(4)
                                ->collapsible(),
                        ]),

                    // Tab 4: أوقات العمل
                    Tab::make(__('custom.working_hours'))
                        ->icon('heroicon-o-clock')
                        ->badge(fn($record) => !empty($record->working_hours) ? count($record->working_hours) : null)
                        ->schema([
                            Section::make(__('custom.working_hours'))
                                ->schema([
                                    Infolists\Components\ViewEntry::make('working_hours')
                                        ->label('')
                                        ->view('filament.infolists.working-hours')
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(false),
                        ]),

                    // Tab 5: الخدمات
                    Tab::make(__('custom.services'))
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->badge(fn($record) => $record->services?->count() > 0 ? $record->services->count() : null)
                        ->schema([
                            Section::make(__('custom.services'))
                                ->schema([
                                    Infolists\Components\RepeatableEntry::make('services')
                                        ->label('')
                                        ->schema([
                                            Infolists\Components\TextEntry::make('name')
                                                ->label(__('custom.service'))
                                                ->weight('bold')
                                                ->icon('heroicon-o-wrench-screwdriver')
                                                ->color('primary'),

                                            Infolists\Components\TextEntry::make('description')
                                                ->label(__('custom.description'))
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(1)
                                        ->contained(false),
                                ])
                                ->visible(fn($record) => $record->services?->count() > 0)
                                ->collapsible(false),

                            ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
