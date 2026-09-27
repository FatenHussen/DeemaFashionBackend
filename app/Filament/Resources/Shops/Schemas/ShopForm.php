<?php

namespace App\Filament\Resources\Shops\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class ShopForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('Shop Information')
                    ->tabs([
                        // Tab 1: Basic Information (English)
                        Tabs\Tab::make(__('custom.shops.tabs.basic_info_en'))
                            ->schema([
                                Forms\Components\TextInput::make('name.en')
                                    ->label(__('custom.shops.name') . ' (English)')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('description.en')
                                    ->label(__('custom.shops.description') . ' (English)')
                                    ->rows(3),

                                Forms\Components\Textarea::make('address.en')
                                    ->label(__('custom.shops.address') . ' (English)')
                                    ->rows(2),
                            ]),

                        // Tab 2: Basic Information (Arabic)
                        Tabs\Tab::make(__('custom.shops.tabs.basic_info_ar'))
                            ->schema([
                                Forms\Components\TextInput::make('name.ar')
                                    ->label(__('custom.shops.name') . ' (العربية)')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('description.ar')
                                    ->label(__('custom.shops.description') . ' (العربية)')
                                    ->rows(3),

                                Forms\Components\Textarea::make('address.ar')
                                    ->label(__('custom.shops.address') . ' (العربية)')
                                    ->rows(2),
                            ]),

                        // Tab 3: Contact & Location
                        Tabs\Tab::make(__('custom.shops.tabs.contact_location'))
                            ->schema([
                                Section::make(__('custom.shops.sections.contact_info'))
                                    ->schema([
                                        Forms\Components\TextInput::make('phone')
                                            ->label(__('custom.shops.phone'))
                                            ->tel()
                                            ->maxLength(20),

                                        Forms\Components\TextInput::make('mobile')
                                            ->label(__('custom.shops.mobile'))
                                            ->tel()
                                            ->maxLength(20),

                                        Forms\Components\TextInput::make('email')
                                            ->label(__('custom.shops.email'))
                                            ->email()
                                            ->maxLength(255),
                                    ])
                                    ->columns(3),

                                Section::make(__('custom.shops.sections.location_info'))
                                    ->schema([
                                        Forms\Components\TextInput::make('lat')
                                            ->label(__('custom.shops.lat'))
                                            ->numeric()
                                            ->step(0.000001)
                                            ->placeholder('33.513807')
                                            ->minValue(-90)
                                            ->maxValue(90)
                                            ->rules(['nullable', 'numeric', 'min:-90', 'max:90'])
                                            ->helperText(__('custom.shops.lat_help')),

                                        Forms\Components\TextInput::make('lng')
                                            ->label(__('custom.shops.lng'))
                                            ->numeric()
                                            ->step(0.000001)
                                            ->placeholder('36.276528')
                                            ->minValue(-180)
                                            ->maxValue(180)
                                            ->rules(['nullable', 'numeric', 'min:-180', 'max:180'])
                                            ->helperText(__('custom.shops.lng_help')),

                                        Forms\Components\Select::make('area_id')
                                            ->label(__('custom.shops.area'))
                                            ->relationship('area', 'name')
                                            ->searchable()
                                            ->preload(),
                                    ])
                                    ->columns(3),
                            ]),

                        // Tab 4: Media
                        Tabs\Tab::make(__('custom.shops.tabs.media'))
                            ->schema([
                                Forms\Components\FileUpload::make('logo')
                                    ->label(__('custom.shops.logo'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('shop')
                                    ->imageEditor()
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('cover_images')
                                    ->label(__('custom.shops.cover_images'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('shop/covers')
                                    ->multiple()
                                    ->maxFiles(5)
                                    ->imageEditor()
                                    ->columnSpanFull(),
                            ]),

                        // Tab 5: Working Hours
                        Tabs\Tab::make(__('custom.shops.tabs.working_hours'))
                            ->schema([
                                Forms\Components\Repeater::make('working_hours')
                                    ->label(__('custom.shops.working_hours'))
                                    ->schema([
                                        Forms\Components\Select::make('day')
                                            ->label(__('custom.shops.day'))
                                            ->options([
                                                'monday' => __('custom.shops.days.monday'),
                                                'tuesday' => __('custom.shops.days.tuesday'),
                                                'wednesday' => __('custom.shops.days.wednesday'),
                                                'thursday' => __('custom.shops.days.thursday'),
                                                'friday' => __('custom.shops.days.friday'),
                                                'saturday' => __('custom.shops.days.saturday'),
                                                'sunday' => __('custom.shops.days.sunday'),
                                            ])
                                            ->required()
                                            ->distinct()
                                            ->columnSpan(2),

                                        Forms\Components\TimePicker::make('open')
                                            ->label(__('custom.shops.open'))
                                            ->seconds(false)
                                            ->required(fn ($get) => !$get('closed'))
                                            ->disabled(fn ($get) => $get('closed'))
                                            ->columnSpan(2),

                                        Forms\Components\TimePicker::make('close')
                                            ->label(__('custom.shops.close'))
                                            ->seconds(false)
                                            ->required(fn ($get) => !$get('closed'))
                                            ->disabled(fn ($get) => $get('closed'))
                                            ->columnSpan(2),

                                        Forms\Components\Toggle::make('closed')
                                            ->label(__('custom.shops.closed'))
                                            ->default(false)
                                            ->reactive()
                                            ->columnSpan(1),
                                    ])
                                    ->columns(7)
                                    ->defaultItems(0)
                                    ->addActionLabel(__('custom.shops.add_day'))
                                    ->reorderable(false)
                                    ->columnSpanFull(),
                            ]),

                        // Tab 6: Services & Settings
                        Tabs\Tab::make(__('custom.shops.tabs.settings'))
                            ->schema([
                                Section::make(__('custom.shops.sections.services'))
                                    ->schema([
                                        Forms\Components\Select::make('service_ids')
                                            ->label(__('custom.shops.services'))
                                            ->relationship('services', 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->columnSpanFull(),
                                    ]),

                                Section::make(__('custom.shops.sections.settings'))
                                    ->schema([
                                        Forms\Components\Select::make('vendor_id')
                                            ->label(__('custom.shops.vendor'))
                                            ->relationship('vendor', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Forms\Components\Toggle::make('is_active')
                                            ->label(__('custom.shops.is_active'))
                                            ->default(true),

                                        Forms\Components\Toggle::make('is_free_delivery')
                                            ->label(__('custom.shops.is_free_delivery'))
                                            ->default(false),

                                        Forms\Components\TextInput::make('min_order_amount')
                                            ->label(__('custom.shops.min_order_amount'))
                                            ->numeric()
                                            ->minValue(0)
                                            ->visible(fn ($record) => ! $record?->is_default)
                                            ->helperText(__('custom.shops.min_order_amount_help')),

                                        Forms\Components\TextInput::make('delivery_min_hours')
                                            ->label(__('custom.shops.delivery_min_hours'))
                                            ->numeric()
                                            ->integer()
                                            ->minValue(0)
                                            ->visible(fn ($record) => ! $record?->is_default)
                                            ->helperText(__('custom.shops.delivery_window_help')),

                                        Forms\Components\TextInput::make('delivery_max_hours')
                                            ->label(__('custom.shops.delivery_max_hours'))
                                            ->numeric()
                                            ->integer()
                                            ->minValue(0)
                                            ->visible(fn ($record) => ! $record?->is_default),

                                        Forms\Components\Placeholder::make('platform_checkout')
                                            ->label(__('custom.shops.min_order_amount'))
                                            ->content(__('custom.shops.platform_uses_settings'))
                                            ->visible(fn ($record) => (bool) $record?->is_default)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

