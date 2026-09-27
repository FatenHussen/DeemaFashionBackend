<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist->columns(1)->schema([
            Tabs::make('product_info_tabs')
                ->tabs([
                    // Tab 1: المعلومات الأساسية
                    Tab::make(__('custom.products.sections.basic_info'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make(__('custom.products.sections.basic_info'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('name')
                                        ->label(__('custom.products.name'))
                                        ->weight('bold')
                                        ->size('xl')
                                        ->color('primary')
                                        ->icon('heroicon-o-cube')
                                        ->columnSpan(2),

                                    Infolists\Components\TextEntry::make('approval_status')
                                        ->label(__('custom.products.approval_status'))
                                        ->badge()
                                        ->size('lg')
                                        ->color(fn(\App\Enums\ProductApprovalStatus $state): string => match ($state) {
                                            \App\Enums\ProductApprovalStatus::PENDING => 'warning',
                                            \App\Enums\ProductApprovalStatus::APPROVED => 'success',
                                            \App\Enums\ProductApprovalStatus::REJECTED => 'danger',
                                        })
                                        ->formatStateUsing(fn(\App\Enums\ProductApprovalStatus $state): string => match ($state) {
                                            \App\Enums\ProductApprovalStatus::PENDING => '⏳ ' . __("custom.products.approval_statuses.{$state->value}"),
                                            \App\Enums\ProductApprovalStatus::APPROVED => '✅ ' . __("custom.products.approval_statuses.{$state->value}"),
                                            \App\Enums\ProductApprovalStatus::REJECTED => '❌ ' . __("custom.products.approval_statuses.{$state->value}"),
                                        })
                                        ->columnSpan(2),

                                    Infolists\Components\TextEntry::make('sku')
                                        ->label(__('custom.products.sku'))
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-hashtag')
                                        ->copyable(),

                                    Infolists\Components\TextEntry::make('barcode')
                                        ->label(__('custom.products.barcode'))
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-qr-code')
                                        ->copyable(),

                                    Infolists\Components\TextEntry::make('model')
                                        ->label(__('custom.products.model'))
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-tag'),

                                    Infolists\Components\TextEntry::make('category.name')
                                        ->label(__('custom.products.category'))
                                        ->badge()
                                        ->color('info')
                                        ->icon('heroicon-o-folder'),

                                    Infolists\Components\TextEntry::make('brand.name')
                                        ->label(__('custom.products.brand'))
                                        ->badge()
                                        ->color('purple')
                                        ->icon('heroicon-o-building-storefront'),

                                    Infolists\Components\TextEntry::make('vendor.name')
                                        ->label(__('custom.products.vendor'))
                                        ->badge()
                                        ->color('warning')
                                        ->icon('heroicon-o-user-group'),

                                    Infolists\Components\TextEntry::make('originCountry.name')
                                        ->label(__('custom.products.country'))
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-globe-alt')
                                        ->placeholder('—'),

                                    Infolists\Components\TextEntry::make('rejection_reason')
                                        ->label(__('custom.products.rejection_reason'))
                                        ->columnSpanFull()
                                        ->color('danger')
                                        ->icon('heroicon-o-exclamation-triangle')
                                        ->visible(fn($record) => $record->approval_status === \App\Enums\ProductApprovalStatus::REJECTED),
                                ])
                                ->columns(4)
                                ->collapsible(),

                            Section::make(__('custom.products.sections.description'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('description')
                                        ->label(__('custom.products.description'))
                                        ->columnSpanFull()
                                        ->prose()
                                        ->markdown(),

                                    Infolists\Components\TextEntry::make('full_description')
                                        ->label(__('custom.products.full_description'))
                                        ->columnSpanFull()
                                        ->prose()
                                        ->html(),
                                ])
                                ->collapsible(),
                        ]),

                    // Tab 2: الأسعار والكميات
                    Tab::make(__('custom.products.sections.pricing'))
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Section::make(__('custom.products.sections.pricing'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('price')
                                        ->label(__('custom.products.price'))
                                        ->money('USD')
                                        ->weight('bold')
                                        ->size('xl')
                                        ->color('success')
                                        ->icon('heroicon-o-banknotes'),

                                    Infolists\Components\TextEntry::make('cost_price')
                                        ->label(__('custom.products.cost_price'))
                                        ->money('USD')
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-calculator')
                                        ->default('-'),

                                    Infolists\Components\TextEntry::make('discount_type')
                                        ->label(__('custom.products.discount_type'))
                                        ->badge()
                                        ->color('info')
                                        ->formatStateUsing(fn($state) => match($state) {
                                            'none' => __('custom.products.form.no_discount'),
                                            'percentage' => __('custom.products.form.percentage'),
                                            'fixed' => __('custom.products.form.fixed_amount'),
                                            default => '-'
                                        })
                                        ->default('-'),

                                    Infolists\Components\TextEntry::make('discount')
                                        ->label(__('custom.products.discount'))
                                        ->suffix(fn($record) => $record->discount_type === 'percentage' ? '%' : '')
                                        ->badge()
                                        ->size('lg')
                                        ->color('danger')
                                        ->icon('heroicon-o-receipt-percent')
                                        ->default('-'),

                                    Infolists\Components\TextEntry::make('price_after_discount')
                                        ->label(__('custom.products.price_after_discount'))
                                        ->money('USD')
                                        ->color('primary')
                                        ->weight('bold')
                                        ->size('xl')
                                        ->icon('heroicon-o-tag'),

                                    Infolists\Components\TextEntry::make('quantity')
                                        ->label(__('custom.products.quantity_available'))
                                        ->badge()
                                        ->size('lg')
                                        ->color(fn($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                                        ->icon(fn($state) => $state > 10 ? 'heroicon-o-check-circle' : ($state > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-x-circle'))
                                        ->default('-'),

                                    Infolists\Components\TextEntry::make('unit')
                                        ->label(__('custom.products.unit'))
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-scale')
                                        ->default('-'),

                                    Infolists\Components\TextEntry::make('warranty_period')
                                        ->label(__('custom.products.warranty_period'))
                                        ->badge()
                                        ->color('info')
                                        ->icon('heroicon-o-shield-check')
                                        ->suffix(' ' . __('custom.products.form.warranty_months'))
                                        ->default('-'),

                                    Infolists\Components\IconEntry::make('is_visible')
                                        ->label(__('custom.products.is_visible'))
                                        ->boolean()
                                        ->trueIcon('heroicon-o-eye')
                                        ->falseIcon('heroicon-o-eye-slash')
                                        ->trueColor('success')
                                        ->falseColor('danger'),
                                ])
                                ->columns(4)
                                ->collapsible(),

                            Section::make(__('custom.products.statistics'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('average_rating')
                                        ->label(__('custom.products.rating'))
                                        ->badge()
                                        ->color('success')
                                        ->size('xl')
                                        ->icon('heroicon-o-star')
                                        ->suffix(' ⭐')
                                        ->default('0'),

                                    Infolists\Components\TextEntry::make('sold_quantity')
                                        ->label(__('custom.products.sold_quantity'))
                                        ->badge()
                                        ->color('primary')
                                        ->size('xl')
                                        ->icon('heroicon-o-shopping-cart')
                                        ->default('0'),

                                    Infolists\Components\TextEntry::make('created_at')
                                        ->label(__('custom.products.date_added'))
                                        ->dateTime('Y-m-d H:i')
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-calendar')
                                        ->since(),
                                ])
                                ->columns(3)
                                ->collapsible(),
                        ]),

                    // Tab 3: الصور
                    Tab::make(__('custom.products.sections.images'))
                        ->icon('heroicon-o-photo')
                        ->badge(fn($record) => $record->media->count() > 0 ? $record->media->count() : null)
                        ->schema([
                            Section::make(__('custom.products.sections.thumbnail'))
                                ->schema([
                                    Infolists\Components\ImageEntry::make('thumbnail')
                                        ->label(__('custom.products.thumbnail'))
                                        ->getStateUsing(fn ($record) => $record->thumbnail_url)
                                        ->size(200)
                                        ->extraAttributes(['class' => 'rounded-xl'])
                                        ->visible(fn($record) => $record->thumbnail)
                                        ->columnSpanFull(),

                                    Infolists\Components\TextEntry::make('no_thumbnail')
                                        ->label(__('custom.products.thumbnail'))
                                        ->default('-')
                                        ->color('gray')
                                        ->visible(fn($record) => !$record->thumbnail)
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.images'))
                                ->schema([
                                    Infolists\Components\ImageEntry::make('media')
                                        ->label('')
                                        ->getStateUsing(function ($record) {
                                            return $record->media->map(function ($media) {
                                                return asset('storage/' . $media->path);
                                            })->toArray();
                                        })
                                        ->columnSpanFull()
                                        ->size(200)
                                        ->extraAttributes(['class' => 'rounded-xl'])
                                        ->visible(fn($record) => $record->media->count() > 0),

                                    Infolists\Components\TextEntry::make('no_images')
                                        ->label('')
                                        ->color('gray')
                                        ->size('lg')
                                        ->columnSpanFull()
                                        ->visible(fn($record) => $record->media->count() === 0),
                                ])
                                ->collapsible(false),
                        ]),

                    // Tab 4: المتغيرات
                    Tab::make(__('custom.products.sections.variants'))
                        ->icon('heroicon-o-squares-2x2')
                        ->badge(fn($record) => $record->variants->count() > 0 ? $record->variants->count() : null)
                        ->schema([
                            Section::make(__('custom.products.sections.variants'))
                                ->schema([
                                    Infolists\Components\RepeatableEntry::make('variants')
                                        ->label('')
                                        ->schema([
                                            Infolists\Components\ViewEntry::make('attributes_display')
                                                ->label(__('custom.products.sections.attributes'))
                                                ->view('filament.infolists.variant-attributes')
                                                ->columnSpanFull(),

                                            Infolists\Components\TextEntry::make('price')
                                                ->label(__('custom.products.price'))
                                                ->money('USD')
                                                ->color('success')
                                                ->icon('heroicon-o-banknotes'),

                                            Infolists\Components\TextEntry::make('quantity')
                                                ->label(__('custom.products.quantity'))
                                                ->badge()
                                                ->color(fn($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                                                ->icon(fn($state) => $state > 10 ? 'heroicon-o-check-circle' : ($state > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-x-circle')),

                                            Infolists\Components\IconEntry::make('is_trend')
                                                ->label(__('custom.products.trending'))
                                                ->boolean()
                                                ->trueIcon('heroicon-o-fire')
                                                ->falseIcon('heroicon-o-minus')
                                                ->trueColor('danger')
                                                ->falseColor('gray'),

                                            Infolists\Components\TextEntry::make('average_rating')
                                                ->label(__('custom.products.rating'))
                                                ->badge()
                                                ->color('success')
                                                ->icon('heroicon-o-star')
                                                ->suffix(' ⭐'),

                                            Infolists\Components\ImageEntry::make('media')
                                                ->label(__('custom.products.sections.images'))
                                                ->getStateUsing(function ($record) {
                                                    return $record->media->map(function ($media) {
                                                        return asset('storage/' . $media->path);
                                                    })->toArray();
                                                })
                                                ->columnSpanFull()
                                                ->size(120)
                                                ->extraAttributes(['class' => 'rounded-xl']),

                                            Section::make(__('custom.products.sections.shop_availability'))
                                                ->schema([
                                                    Infolists\Components\RepeatableEntry::make('shopVariants')
                                                        ->label('')
                                                        ->schema([
                                                            Infolists\Components\TextEntry::make('shop.name')
                                                                ->label(__('custom.products.shop'))
                                                                ->weight('bold')
                                                                ->icon('heroicon-o-building-storefront')
                                                                ->color('info'),

                                                            Infolists\Components\TextEntry::make('created_at')
                                                                ->label(__('custom.products.date_added'))
                                                                ->badge()
                                                                ->color('gray')
                                                                ->icon('heroicon-o-calendar')
                                                                ->since(),
                                                        ])
                                                        ->columns(3),
                                                ])
                                                ->columnSpanFull()
                                                ->collapsible(),
                                        ])
                                        ->columns([
                                            'sm' => 1,
                                            'xl' => 3,
                                        ])
                                        ->contained(false),
                                ])
                                ->columnSpanFull()
                                ->collapsible(false)
                                ->visible(fn($record) => $record->variants->count() > 0),

                            Section::make('لا توجد متغيرات')
                                ->schema([
                                    Infolists\Components\TextEntry::make('no_variants')
                                        ->label('')
                                        ->color('gray')
                                        ->size('lg')
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn($record) => $record->variants->count() === 0),
                        ]),

                    // Tab 5: التفاصيل الإضافية
                    Tab::make(__('custom.products.category_details'))
                        ->icon('heroicon-o-list-bullet')
                        ->badge(fn($record) => ($record->categoryDetails->count() + $record->extraDetails->count()) > 0 ? ($record->categoryDetails->count() + $record->extraDetails->count()) : null)
                        ->schema([
                            Section::make(__('custom.products.category_details'))
                                ->schema([
                                    Infolists\Components\RepeatableEntry::make('categoryDetails')
                                        ->label('')
                                        ->schema([
                                            Infolists\Components\TextEntry::make('categoryDetail.detail_key')
                                                ->label(__('custom.products.detail_key'))
                                                ->weight('bold')
                                                ->icon('heroicon-o-key')
                                                ->color('info'),

                                            Infolists\Components\TextEntry::make('detail_value')
                                                ->label(__('custom.products.detail_value'))
                                                ->icon('heroicon-o-document-text'),
                                        ])
                                        ->columns(2)
                                        ->contained(false),
                                ])
                                ->visible(fn($record) => $record->categoryDetails->count() > 0)
                                ->collapsible(),

                            Section::make(__('custom.products.extra_details'))
                                ->schema([
                                    Infolists\Components\RepeatableEntry::make('extraDetails')
                                        ->label('')
                                        ->schema([
                                            Infolists\Components\TextEntry::make('detail_key')
                                                ->label(__('custom.products.detail_key'))
                                                ->weight('bold')
                                                ->icon('heroicon-o-key')
                                                ->color('info'),

                                            Infolists\Components\TextEntry::make('detail_value')
                                                ->label(__('custom.products.detail_value'))
                                                ->icon('heroicon-o-document-text'),

                                            Infolists\Components\TextEntry::make('price')
                                                ->label(__('custom.products.form.extra_price'))
                                                ->money('USD')
                                                ->icon('heroicon-o-currency-dollar')
                                                ->color('success'),
                                        ])
                                        ->columns(3)
                                        ->contained(false),
                                ])
                                ->visible(fn($record) => $record->extraDetails->count() > 0)
                                ->collapsible(),

                            Section::make('لا توجد تفاصيل إضافية')
                                ->schema([
                                    Infolists\Components\TextEntry::make('no_details')
                                        ->label('')
                                        ->color('gray')
                                        ->size('lg')
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn($record) => $record->categoryDetails->count() === 0 && $record->extraDetails->count() === 0),
                        ]),

                    // Tab 6: إعدادات التوصيل
                    Tab::make(__('custom.products.sections.delivery_settings'))
                        ->icon('heroicon-o-truck')
                        ->schema([
                            Section::make(__('custom.products.sections.delivery_settings'))
                                ->schema([
                                    Infolists\Components\IconEntry::make('is_instant_delivery')
                                        ->label(__('custom.products.is_instant_delivery'))
                                        ->boolean()
                                        ->trueIcon('heroicon-o-bolt')
                                        ->falseIcon('heroicon-o-clock')
                                        ->trueColor('success')
                                        ->falseColor('gray'),

                                    Infolists\Components\TextEntry::make('time_prepare')
                                        ->label(__('custom.products.time_prepare'))
                                        ->formatStateUsing(fn($state) => $state ? $state->format('H:i') : '-')
                                        ->badge()
                                        ->color('info')
                                        ->icon('heroicon-o-clock')
                                        ->size('lg'),
                                ])
                                ->columns(2)
                                ->collapsible(),
                        ]),

                    // Tab 7: المنتجات المشتراة معاً
                    Tab::make(__('custom.products.sections.bought_with'))
                        ->icon('heroicon-o-shopping-bag')
                        ->badge(fn($record) => $record->bought_with && \count($record->bought_with) > 0 ? \count($record->bought_with) : null)
                        ->schema([
                            Section::make(__('custom.products.sections.bought_with'))
                                ->schema([
                                    Infolists\Components\RepeatableEntry::make('bought_with_products_list')
                                        ->label('')
                                        ->schema([
                                            Infolists\Components\TextEntry::make('name')
                                                ->label(__('custom.products.name'))
                                                ->weight('bold')
                                                ->icon('heroicon-o-cube')
                                                ->color('primary'),

                                            Infolists\Components\TextEntry::make('price')
                                                ->label(__('custom.products.price'))
                                                ->money('USD')
                                                ->color('success')
                                                ->icon('heroicon-o-banknotes'),
                                        ])
                                        ->columns(2)
                                        ->contained(false),
                                ])
                                ->visible(fn($record) => $record->bought_with && \count($record->bought_with) > 0)
                                ->collapsible(false),

                            Section::make('لا توجد منتجات مشتراة معاً')
                                ->schema([
                                    Infolists\Components\TextEntry::make('no_bought_with')
                                        ->label('')
                                        ->color('gray')
                                        ->size('lg')
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn($record) => !$record->bought_with || \count($record->bought_with) === 0),
                        ]),

                    // Tab 8: تحسين محركات البحث (SEO)
                    Tab::make(__('custom.products.seo.title'))
                        ->icon('heroicon-o-magnifying-glass')
                        ->badge(fn($record) => ($record->seo_title || $record->seo_description || $record->seo_keywords || $record->seo_image) ? '✓' : null)
                        ->schema([
                            Section::make(__('custom.products.seo.title'))
                                ->schema([
                                    Infolists\Components\TextEntry::make('seo_title')
                                        ->label(__('custom.products.seo.seo_title'))
                                        ->weight('bold')
                                        ->icon('heroicon-o-document-text')
                                        ->color('primary')
                                        ->default('-')
                                        ->columnSpanFull(),

                                    Infolists\Components\TextEntry::make('seo_description')
                                        ->label(__('custom.products.seo.seo_description'))
                                        ->icon('heroicon-o-document-text')
                                        ->default('-')
                                        ->columnSpanFull(),

                                    Infolists\Components\TextEntry::make('seo_keywords')
                                        ->label(__('custom.products.seo.seo_keywords'))
                                        ->badge()
                                        ->separator(',')
                                        ->color('info')
                                        ->icon('heroicon-o-tag')
                                        ->default('-')
                                        ->columnSpanFull(),

                                    Infolists\Components\ImageEntry::make('seo_image')
                                        ->label(__('custom.products.seo.seo_image'))
                                        ->disk('public')
                                        ->size(300)
                                        ->extraAttributes(['class' => 'rounded-xl'])
                                        ->visible(fn($record) => $record->seo_image)
                                        ->columnSpanFull(),

                                    Infolists\Components\TextEntry::make('no_seo_image')
                                        ->label(__('custom.products.seo.seo_image'))
                                        ->default(__('custom.products.seo.no_seo_image'))
                                        ->color('gray')
                                        ->visible(fn($record) => !$record->seo_image)
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
