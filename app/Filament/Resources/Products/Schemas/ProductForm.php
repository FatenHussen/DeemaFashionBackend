<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Forms\Components\TinyEditor;
use App\Models\AttributeValue;
use App\Models\Color;
use App\Models\Category;
use App\Models\Product;
use App\Models\VendorUser;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Auth;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        /** @var VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();
        $vendorId = $user?->vendor_id;

        return $schema->columns(1)->schema([
            Tabs::make('product_tabs')
                ->tabs([
                    // Tab 1: Basic Information
                    Tab::make(__('custom.products.tabs.basic_info'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make(__('custom.products.sections.basic_info'))
                                ->schema([
                                    Forms\Components\TextInput::make('name.ar')
                                        ->label(__('custom.products.form.name_ar'))
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('name.en')
                                        ->label(__('custom.products.form.name_en'))
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('model')
                                        ->label(__('custom.products.form.model_number'))
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255)
                                        ->visible(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->dehydrated(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('sku')
                                        ->label(__('custom.products.form.product_code'))
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255)
                                        ->visible(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->dehydrated(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->columnSpan(1),

                                    ...static::categoryHierarchySelects(),

                                    Forms\Components\Hidden::make('category_id')
                                        ->required(),

                                    Forms\Components\TextInput::make('barcode')
                                        ->label(__('custom.products.form.barcode_label'))
                                        ->maxLength(255)
                                        ->visible(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->dehydrated(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('brand_id')
                                        ->label(__('custom.products.form.brand_label'))
                                        ->relationship('brand', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->dehydrated(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('country_id')
                                        ->label(__('custom.products.form.country'))
                                        ->relationship('originCountry', 'name')
                                        ->getOptionLabelFromRecordUsing(
                                            fn ($record) => $record->getTranslation('name', app()->getLocale())
                                                ?: $record->getTranslation('name', 'en')
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->nullable()
                                        ->visible(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->dehydrated(fn(callable $get): bool => !static::isRestaurantCategory($get('category_id')))
                                        ->columnSpan(1),

                                    Forms\Components\Hidden::make('vendor_id')
                                        ->default($vendorId),

                                    Forms\Components\Hidden::make('approval_status')
                                        ->default(fn() => Auth::guard('admin')->check()
                                            ? \App\Enums\ProductApprovalStatus::APPROVED
                                            : \App\Enums\ProductApprovalStatus::PENDING),
                                ])
                                ->columns(2)
                                ->collapsible(),

                            Section::make(__('custom.products.sections.description'))
                                ->schema([
                                    Forms\Components\Textarea::make('description.ar')
                                        ->label(__('custom.products.form.description_ar'))
                                        ->rows(3)
                                        ->maxLength(500)
                                        ->columnSpanFull(),

                                    Forms\Components\Textarea::make('description.en')
                                        ->label(__('custom.products.form.description_en'))
                                        ->rows(3)
                                        ->maxLength(500)
                                        ->columnSpanFull(),

                                    TinyEditor::make('full_description.ar')
                                        ->label(__('custom.products.form.full_description_ar'))
                                        ->rtl(true)
                                        ->minHeight(420)
                                        ->columnSpanFull(),

                                    TinyEditor::make('full_description.en')
                                        ->label(__('custom.products.form.full_description_en'))
                                        ->rtl(false)
                                        ->minHeight(420)
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.category_details'))
                                ->schema([
                                    Forms\Components\Repeater::make('categoryDetails')
                                        ->label('')
                                        ->relationship('categoryDetails')
                                        ->schema([
                                            Forms\Components\Select::make('category_detail_id')
                                                ->label(__('custom.products.form.specification'))
                                                ->options(function (callable $get) {
                                                    $categoryId = $get('../../category_id');
                                                    if (!$categoryId) {
                                                        return [];
                                                    }
                                                    return \App\Models\CategoryDetail::where('category_id', $categoryId)
                                                        ->pluck('name', 'id');
                                                })
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    $set('value_option_key', null);
                                                    $set('detail_value.ar', null);
                                                    $set('detail_value.en', null);
                                                })
                                                ->searchable()
                                                ->native(false),

                                            Forms\Components\Select::make('value_option_key')
                                                ->label(__('custom.products.detail_value'))
                                                ->options(function (callable $get) {
                                                    $categoryDetailId = $get('category_detail_id');
                                                    if (!$categoryDetailId) {
                                                        return [];
                                                    }

                                                    $detail = \App\Models\CategoryDetail::query()->find($categoryDetailId);
                                                    if (!$detail || !is_array($detail->value_options)) {
                                                        return [];
                                                    }

                                                    $locale = app()->getLocale();
                                                    $options = [];

                                                    foreach ($detail->value_options as $item) {
                                                        if (!is_array($item)) {
                                                            continue;
                                                        }

                                                        $arValue = isset($item['ar']) ? trim((string) $item['ar']) : '';
                                                        $enValue = isset($item['en']) ? trim((string) $item['en']) : '';

                                                        if ($arValue === '' && $enValue === '') {
                                                            continue;
                                                        }

                                                        $key = json_encode([
                                                            'ar' => $arValue ?: null,
                                                            'en' => $enValue ?: null,
                                                        ], JSON_UNESCAPED_UNICODE);

                                                        $options[$key] = $locale === 'ar'
                                                            ? ($arValue ?: $enValue)
                                                            : ($enValue ?: $arValue);
                                                    }

                                                    return $options;
                                                })
                                                ->required()
                                                ->searchable()
                                                ->native(false)
                                                ->dehydrated(false)
                                                ->live()
                                                ->afterStateHydrated(function ($state, callable $get, callable $set) {
                                                    if (!blank($state)) {
                                                        return;
                                                    }

                                                    $detailValue = $get('detail_value');
                                                    if (!is_array($detailValue)) {
                                                        return;
                                                    }

                                                    $arValue = isset($detailValue['ar']) ? trim((string) $detailValue['ar']) : '';
                                                    $enValue = isset($detailValue['en']) ? trim((string) $detailValue['en']) : '';

                                                    if ($arValue === '' && $enValue === '') {
                                                        return;
                                                    }

                                                    $set('value_option_key', json_encode([
                                                        'ar' => $arValue ?: null,
                                                        'en' => $enValue ?: null,
                                                    ], JSON_UNESCAPED_UNICODE));
                                                })
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    if (blank($state)) {
                                                        $set('detail_value.ar', null);
                                                        $set('detail_value.en', null);
                                                        return;
                                                    }

                                                    $decoded = json_decode((string) $state, true);

                                                    if (!is_array($decoded)) {
                                                        $set('detail_value.ar', null);
                                                        $set('detail_value.en', null);
                                                        return;
                                                    }

                                                    $set('detail_value.ar', $decoded['ar'] ?? null);
                                                    $set('detail_value.en', $decoded['en'] ?? null);
                                                }),

                                            Forms\Components\Hidden::make('detail_value.ar'),

                                            Forms\Components\Hidden::make('detail_value.en'),
                                        ])
                                        ->columns(3)
                                        ->defaultItems(0)
                                        ->addActionLabel(__('custom.products.form.add_detail'))
                                        ->collapsible(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.extra_details'))
                                ->schema([
                                    Forms\Components\Repeater::make('extraDetails')
                                        ->label('')
                                        ->relationship('extraDetails')
                                        ->schema([
                                            Forms\Components\TextInput::make('detail_key.ar')
                                                ->label(__('custom.products.form.title_ar'))
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('detail_key.en')
                                                ->label(__('custom.products.form.title_en'))
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('detail_value.ar')
                                                ->label(__('custom.products.form.value_ar'))
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('detail_value.en')
                                                ->label(__('custom.products.form.value_en'))
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('price')
                                                ->label(__('custom.products.form.extra_price'))
                                                ->numeric()
                                                ->minValue(0)
                                                ->default(0)
                                                ->suffix(__('custom.currency'))
                                                ->helperText(__('custom.products.form.extra_price_help'))
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(0)
                                        ->addActionLabel(__('custom.products.form.add_extra_detail'))
                                        ->collapsible(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.bought_with'))
                                ->schema([
                                    Forms\Components\Select::make('bought_with')
                                        ->label(__('custom.products.form.select_products'))
                                        ->multiple()
                                        ->searchable()
                                        ->options(function (callable $get, ?Product $record) use ($vendorId) {
                                            $query = Product::query();

                                            if ($vendorId) {
                                                $query->where('vendor_id', $vendorId);
                                            }

                                            if (!empty($get('category_id'))) {
                                                $query->where('category_id', (int) $get('category_id'));
                                            }

                                            if ($record?->id) {
                                                $query->where('id', '!=', $record->id);
                                            }

                                            return $query->pluck('name', 'id');
                                        })
                                        ->helperText(__('custom.products.form.bought_with_help'))
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.pricing'))
                                ->schema([
                                    Forms\Components\TextInput::make('price')
                                        ->label(__('custom.products.form.price_label') . ' ($)')
                                        ->numeric()
                                        ->prefix('$')
                                        ->minValue(0)
                                        ->live(onBlur: true)
                                        ->afterStateHydrated(function ($state, callable $set) {
                                            if ($state === null || $state === '') {
                                                return;
                                            }
                                            $syp = \App\Models\Currency::query()->where('code', 'SYP')->first();
                                            if ($syp) {
                                                $set('price_syp_display', $syp->convertFromBase((float) $state));
                                            }
                                        })
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $syp = \App\Models\Currency::query()->where('code', 'SYP')->first();
                                            if ($syp && $state !== null && $state !== '') {
                                                $set('price_syp_display', $syp->convertFromBase((float) $state));
                                            }
                                        })
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('price_syp_display')
                                        ->label(__('custom.products.form.price_label') . ' (ل.س)')
                                        ->numeric()
                                        ->suffix('ل.س')
                                        ->minValue(0)
                                        ->dehydrated(false)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state === null || $state === '') {
                                                return;
                                            }
                                            $usd = \App\Helpers\CurrencyHelper::convertSypToUsd((float) $state);
                                            $set('price', $usd);
                                        })
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('discount_type')
                                        ->label(__('custom.products.form.discount_type'))
                                        ->options([
                                            'none' => __('custom.products.form.no_discount'),
                                            'percentage' => __('custom.products.form.percentage'),
                                            'fixed' => __('custom.products.form.fixed_amount'),
                                        ])
                                        ->default('none')
                                        ->live()
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('discount')
                                        ->label(__('custom.products.form.discount_label'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->step(0.01)
                                        ->maxValue(fn (callable $get) => $get('discount_type') === 'percentage' ? 100 : null)
                                        ->suffix(fn(callable $get) => $get('discount_type') === 'percentage' ? '%' : '$')
                                        ->visible(fn(callable $get) => $get('discount_type') !== 'none')
                                        ->columnSpan(1),

                                    Forms\Components\Placeholder::make('price_after_discount_display')
                                        ->label(__('custom.products.price_after_discount'))
                                        ->content(function (callable $get) {
                                            $price = (float) ($get('price') ?? 0);
                                            $type = $get('discount_type');
                                            $discount = (float) ($get('discount') ?? 0);
                                            if (!$price || $type === 'none' || $discount <= 0) {
                                                return number_format($price, 4) . ' $';
                                            }
                                            $final = $type === 'percentage'
                                                ? $price - ($price * $discount / 100)
                                                : max(0, $price - $discount);
                                            $syp = \App\Models\Currency::query()->where('code', 'SYP')->first();
                                            $sypAmount = $syp ? $syp->convertFromBase($final) : null;
                                            return number_format($final, 4) . ' $'
                                                . ($sypAmount !== null ? ' / ' . number_format($sypAmount, 2) . ' ل.س' : '');
                                        })
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('cost_price')
                                        ->label(__('custom.products.form.cost_price'))
                                        ->numeric()
                                        ->prefix('$')
                                        ->minValue(0)
                                        ->helperText(__('custom.products.form.cost_price_help'))
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('quantity')
                                        ->label(__('custom.products.form.quantity_label'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('unit_id')
                                        ->label(__('custom.products.form.unit'))
                                        ->options(fn() => \App\Models\Unit::query()
                                            ->where('is_active', true)
                                            ->orderBy('id')
                                            ->get()
                                            ->mapWithKeys(function (\App\Models\Unit $unit) {
                                                $label = $unit->getTranslation('name', app()->getLocale(), false)
                                                    ?? $unit->getTranslation('name', 'en', false)
                                                    ?? $unit->getTranslation('name', 'ar', false)
                                                    ?? (string) $unit->id;

                                                return [$unit->id => $label];
                                            })
                                            ->toArray())
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder(__('custom.products.form.unit_placeholder'))
                                        ->helperText(__('custom.products.form.unit_help'))
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('warranty_period')
                                        ->label(__('custom.products.form.warranty_period'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->suffix(__('custom.products.form.warranty_months'))
                                        ->helperText(__('custom.products.form.warranty_period_help'))
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('stock')
                                        ->label(__('custom.products.form.stock'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->helperText(__('custom.products.form.stock_help'))
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('max_purchase_quantity')
                                        ->label(__('custom.products.form.max_purchase_quantity'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->helperText(__('custom.products.form.max_purchase_quantity_help'))
                                        ->columnSpan(1),
                                ])
                                ->columns(4)
                                ->collapsible(),

                            Section::make(__('custom.products.sections.visibility'))
                                ->schema([
                                    Forms\Components\Toggle::make('is_visible')
                                        ->label(__('custom.products.form.is_visible'))
                                        ->helperText(__('custom.products.form.is_visible_help'))
                                        ->default(true),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.icons_badges'))
                                ->schema([
                                    Forms\Components\Select::make('icon_ids')
                                        ->label(__('custom.products.form.icons'))
                                        ->relationship('icons', 'name')
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->helperText(__('custom.products.form.icons_help'))
                                        ->columnSpanFull(),

                                    Section::make(__('custom.products.form.badge_top'))
                                        ->schema([
                                            Forms\Components\Select::make('top_badge_id')
                                                ->label(__('custom.products.form.badge_top'))
                                                ->options(static::badgeOptions())
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->placeholder(__('custom.products.form.no_discount'))
                                                ->helperText(__('custom.products.form.top_badge_helper'))
                                                ->columnSpanFull(),
                                        ])
                                        ->collapsible()
                                        ->columnSpanFull(),

                                    Section::make(__('custom.products.form.badge_bottom'))
                                        ->schema([
                                            Forms\Components\Select::make('bottom_badge_ids')
                                                ->label(__('custom.products.form.badge_bottom'))
                                                ->options(static::badgeOptions())
                                                ->multiple()
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->helperText(__('custom.products.form.bottom_badges_helper'))
                                                ->columnSpanFull(),
                                        ])
                                        ->collapsible()
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.delivery_settings'))
                                ->schema([
                                    Forms\Components\Toggle::make('is_instant_delivery')
                                        ->label(__('custom.products.form.instant_delivery'))
                                        ->default(false),

                                    Forms\Components\TimePicker::make('time_prepare')
                                        ->label(__('custom.products.form.preparation_time'))
                                        ->seconds(false),

                                    Forms\Components\TextInput::make('delivery_time')
                                        ->label(__('custom.products.form.delivery_time'))
                                        ->placeholder('مثال: 12-48 ساعة')
                                        ->maxLength(100)
                                        ->helperText(__('custom.products.form.delivery_time_help'))
                                        ->hidden(fn (callable $get) => $get('vendor_id') == 1)
                                        ->columnSpan(1),

                                    Forms\Components\Placeholder::make('delivery_time_tikmool')
                                        ->label(__('custom.products.form.delivery_time'))
                                        ->content('12-48 ساعة (تيك مول)')
                                        ->visible(fn (callable $get) => $get('vendor_id') == 1)
                                        ->columnSpan(1),
                                ])
                                ->columns(2)
                                ->collapsible(),
                        ]),

                    // Tab 2: Images
                    Tab::make(__('custom.products.tabs.images'))
                        ->icon('heroicon-o-photo')
                        ->schema([
                            Section::make(__('custom.products.sections.thumbnail'))
                                ->schema([
                                    Forms\Components\FileUpload::make('thumbnail')
                                        ->label(__('custom.products.form.thumbnail'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('product/thumbnails')
                                        ->imageEditor()
                                        ->helperText(__('custom.products.form.thumbnail_help'))
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.main_image'))
                                ->schema([
                                    Forms\Components\FileUpload::make('main_image')
                                        ->label(__('custom.products.form.main_image_label'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('product')
                                        ->imageEditor()
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.images'))
                                ->schema([
                                    Forms\Components\FileUpload::make('media')
                                        ->label(__('custom.products.form.additional_images_label'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('product')
                                        ->multiple()
                                        ->maxFiles(10)
                                        ->reorderable()
                                        ->imageEditor()
                                        ->helperText(__('custom.products.form.images_help'))
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),
                        ]),

                    // Tab 3: Variants
                    Tab::make(__('custom.products.tabs.variants'))
                        ->icon('heroicon-o-squares-2x2')
                        ->schema([
                            Section::make(__('custom.products.variants.title'))
                                ->description(__('custom.products.form.add_variant_description'))
                                ->schema([
                                    Forms\Components\Placeholder::make('variant_info')
                                        ->label('')
                                        ->content(__('custom.products.form.add_variant_placeholder'))
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(),

                            Section::make(__('custom.products.sections.variants'))
                                ->schema([
                                    Forms\Components\Repeater::make('variants')
                                        ->label('')
                                        ->relationship('variants')
                                        ->schema([
                                            Grid::make(2)
                                                ->schema([
                                                    Forms\Components\TextInput::make('name.ar')
                                                        ->label(__('custom.products.form.variant_name_ar'))
                                                        ->maxLength(255)
                                                        ->columnSpan(1),

                                                    Forms\Components\TextInput::make('name.en')
                                                        ->label(__('custom.products.form.variant_name_en'))
                                                        ->maxLength(255)
                                                        ->columnSpan(1),

                                                    Forms\Components\TextInput::make('sku')
                                                        ->label(__('custom.products.form.variant_sku'))
                                                        ->maxLength(255)
                                                        ->unique(table: 'product_variants', column: 'sku', ignoreRecord: true)
                                                        ->columnSpan(1),
                                                ]),

                                            Section::make(__('custom.products.sections.attributes'))
                                                ->schema([
                                                    Forms\Components\Repeater::make('attribute_selections')
                                                        ->label('')
                                                        ->schema([
                                                            Forms\Components\Select::make('attribute_id')
                                                                ->label(__('custom.products.form.attribute_label'))
                                                                ->options(function (callable $get) {
                                                                    $categoryId = $get('../../../../category_id')
                                                                        ?: $get('../../../../category_level_1');

                                                                    return static::getInheritedAttributeOptions(
                                                                        $categoryId ? (int) $categoryId : null
                                                                    );
                                                                })
                                                                ->live(onBlur: true)
                                                                ->required()
                                                                ->searchable()
                                                                ->afterStateUpdated(function (callable $set) {
                                                                    $set('value_id', null);
                                                                }),

                                                            Forms\Components\Select::make('value_id')
                                                                ->label(__('custom.products.form.value_label'))
                                                                ->options(function (callable $get) {
                                                                    $attributeId = $get('attribute_id');
                                                                    if (!$attributeId || $attributeId === '_placeholder') {
                                                                        return [];
                                                                    }

                                                                    if (static::isColorAttribute($attributeId)) {
                                                                        return static::getColorOptions($attributeId);
                                                                    }

                                                                    $values = \App\Models\AttributeValue::where('category_attribute_id', $attributeId)->get();
                                                                    return $values->mapWithKeys(fn($v) => [$v->id => $v->getTranslation('name', app()->getLocale())])->toArray();
                                                                })
                                                                ->required()
                                                                ->searchable()
                                                                ->native(false)
                                                                ->allowHtml()
                                                                ->extraAttributes(function (callable $get) {
                                                                    return static::isColorAttribute($get('attribute_id'))
                                                                        ? ['class' => 'color-swatch-select']
                                                                        : [];
                                                                }),
                                                        ])
                                                        ->columns(2)
                                                        ->defaultItems(1)
                                                        ->addActionLabel(__('custom.products.form.add_attribute'))
                                                        ->collapsible()
                                                        ->itemLabel(
                                                            fn(array $state): ?string =>
                                                            isset($state['attribute_id']) && isset($state['value_id'])
                                                                ? \App\Models\CategoryAttribute::find($state['attribute_id'])?->name . ': ' . \App\Models\AttributeValue::find($state['value_id'])?->name
                                                                : __('custom.products.form.new_attribute')
                                                        ),

                                                    Forms\Components\Hidden::make('attributes_values_ids')
                                                        ->afterStateHydrated(function ($component, $state, callable $get, callable $set) {
                                                            if ($state && is_array($state)) {
                                                                $selections = [];
                                                                foreach ($state as $valueId) {
                                                                    $value = \App\Models\AttributeValue::find($valueId);
                                                                    if ($value) {
                                                                        $selections[] = [
                                                                            'attribute_id' => $value->category_attribute_id,
                                                                            'value_id' => $valueId,
                                                                        ];
                                                                    }
                                                                }
                                                                $set('attribute_selections', $selections);
                                                            }
                                                        })
                                                        ->dehydrateStateUsing(function ($state, callable $get) {
                                                            $selections = $get('attribute_selections');
                                                            if (!$selections) {
                                                                return [];
                                                            }
                                                            return collect($selections)->pluck('value_id')->filter()->values()->toArray();
                                                        })
                                                ])
                                                ->columnSpanFull()
                                                ->collapsible(),

                                            Forms\Components\TextInput::make('price')
                                                ->label(__('custom.products.form.price_label'))
                                                ->numeric()
                                                ->prefix('$')
                                                ->minValue(0)
                                                ->helperText(__('custom.products.form.price_help')),

                                            Forms\Components\TextInput::make('quantity')
                                                ->label(__('custom.products.form.available_quantity'))
                                                ->numeric()
                                                ->integer()
                                                ->minValue(0)
                                                ->helperText(__('custom.products.form.quantity_help')),

                                            Forms\Components\Toggle::make('is_trend')
                                                ->label(__('custom.products.form.trending_product'))
                                                ->helperText(__('custom.products.form.trending_help')),

                                            Forms\Components\FileUpload::make('variant_media')
                                                ->label(__('custom.products.form.variant_images'))
                                                ->image()
                                                ->multiple()
                                                ->disk('public')
                                                ->directory('product-variant')
                                                ->maxFiles(5)
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(1)
                                        ->collapsible()
                                        ->cloneable()
                                        ->itemLabel(
                                            fn(array $state): ?string =>
                                            isset($state['attribute_selections']) && is_array($state['attribute_selections']) && count($state['attribute_selections']) > 0
                                                ? __('custom.products.form.variant_with_attributes', ['count' => count($state['attribute_selections'])])
                                                : __('custom.products.form.new_variant')
                                        )
                                        ->defaultItems(1)
                                        ->addActionLabel(__('custom.products.form.add_new_variant'))
                                        ->reorderableWithButtons(),
                                ])
                                ->collapsible()
                                ->columnSpanFull(),
                        ]),

                    // Tab 4: SEO
                    Tab::make(__('custom.products.seo.title'))
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Section::make(__('custom.products.seo.title'))
                                ->description(__('custom.products.seo.description'))
                                ->schema([
                                    Forms\Components\TextInput::make('seo_title.ar')
                                        ->label(__('custom.products.seo.seo_title_ar'))
                                        ->maxLength(160)
                                        ->helperText(__('custom.products.seo.seo_title_help'))
                                        ->placeholder(__('custom.products.seo.seo_title_placeholder'))
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('seo_title.en')
                                        ->label(__('custom.products.seo.seo_title_en'))
                                        ->maxLength(160)
                                        ->helperText('Optimal length: 50-60 characters')
                                        ->placeholder('Example: Buy [Product Name] at Best Price')
                                        ->columnSpan(1),

                                    Forms\Components\Textarea::make('seo_description.ar')
                                        ->label(__('custom.products.seo.seo_description_ar'))
                                        ->rows(3)
                                        ->maxLength(320)
                                        ->helperText(__('custom.products.seo.seo_description_help'))
                                        ->placeholder(__('custom.products.seo.seo_description_placeholder'))
                                        ->columnSpanFull(),

                                    Forms\Components\Textarea::make('seo_description.en')
                                        ->label(__('custom.products.seo.seo_description_en'))
                                        ->rows(3)
                                        ->maxLength(320)
                                        ->helperText('Optimal length: 150-160 characters')
                                        ->placeholder('Compelling description that encourages users to click')
                                        ->columnSpanFull(),

                                    Forms\Components\TagsInput::make('seo_keywords.ar')
                                        ->label(__('custom.products.seo.seo_keywords_ar'))
                                        ->placeholder(__('custom.products.seo.seo_keywords_placeholder'))
                                        ->helperText(__('custom.products.seo.seo_keywords_help'))
                                        ->separator(',')
                                        ->columnSpan(1),

                                    Forms\Components\TagsInput::make('seo_keywords.en')
                                        ->label(__('custom.products.seo.seo_keywords_en'))
                                        ->placeholder('Add keyword and press Enter')
                                        ->helperText('Add 5-10 relevant keywords')
                                        ->separator(',')
                                        ->columnSpan(1),

                                    Forms\Components\FileUpload::make('seo_image')
                                        ->label(__('custom.products.seo.seo_image'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('product-seo')
                                        ->imageEditor()
                                        ->helperText(__('custom.products.seo.seo_image_help'))
                                        ->columnSpanFull(),
                                ])
                                ->columns(2)
                                ->collapsible(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    private static function isRestaurantCategory($categoryId): bool
    {
        if (!$categoryId) {
            return false;
        }

        return (bool) \App\Models\Category::query()
            ->whereKey($categoryId)
            ->value('is_restaurant');
    }

    private static function categoryHierarchySelects(): array
    {
        $components = [];

        foreach (range(1, 6) as $level) {
            $field = 'category_level_' . $level;

            $select = Forms\Components\Select::make($field)
                ->label(__('custom.products.form.category_level_label', ['level' => $level]))
                ->options(fn (callable $get): array => static::categoryOptionsForLevel($get, $level))
                ->searchable()
                ->preload()
                ->live()
                ->required($level === 1)
                ->helperText($level === 1
                    ? __('custom.products.form.category_level_hint')
                    : __('custom.products.form.category_level_optional_hint'))
                ->dehydrated(false)
                ->visible(fn (callable $get): bool => static::isCategoryLevelVisible($get, $level))
                ->afterStateHydrated(function ($state, callable $get, callable $set) use ($level): void {
                    if ($level !== 1) {
                        return;
                    }

                    static::hydrateCategoryLevelsFromCategoryId($set, $get('category_id'));
                })
                ->afterStateUpdated(function ($state, callable $get, callable $set) use ($level): void {
                    static::resetCategoryLevelsAfter($set, $level);
                    static::syncSelectedCategory($get, $set);
                    static::applyRestaurantFieldReset($get, $set);
                })
                ->columnSpan(1);

            if ($level > 1) {
                $select->placeholder(__('custom.products.form.category_level_optional'));
            }

            $components[] = $select;
        }

        return $components;
    }

    private static function categoryOptionsForLevel(callable $get, int $level): array
    {
        $parentId = $level === 1
            ? null
            : $get('category_level_' . ($level - 1));

        if ($level > 1 && !$parentId) {
            return [];
        }

        return Category::query()
            ->where('parent_id', $parentId)
            ->orderBy('order')
            ->get()
            ->mapWithKeys(fn (Category $category) => [
                $category->id => (string) ($category->getTranslation('name', app()->getLocale(), false)
                    ?: $category->getTranslation('name', config('app.fallback_locale', 'en'), false)
                    ?: $category->id),
            ])
            ->toArray();
    }

    private static function isCategoryLevelVisible(callable $get, int $level): bool
    {
        if ($level === 1) {
            return true;
        }

        $parentId = $get('category_level_' . ($level - 1));

        if (!$parentId) {
            return false;
        }

        return static::hasChildCategories((int) $parentId);
    }

    private static function hasChildCategories(int $parentId): bool
    {
        return Category::query()->where('parent_id', $parentId)->exists();
    }

    private static function resetCategoryLevelsAfter(callable $set, int $level): void
    {
        foreach (range($level + 1, 6) as $nextLevel) {
            $set('category_level_' . $nextLevel, null);
        }
    }

    private static function syncSelectedCategory(callable $get, callable $set): void
    {
        $selectedCategoryId = null;

        foreach (range(1, 6) as $level) {
            $value = $get('category_level_' . $level);
            if ($value) {
                $selectedCategoryId = (int) $value;
            }
        }

        $set('category_id', $selectedCategoryId);
    }

    private static function hydrateCategoryLevelsFromCategoryId(callable $set, mixed $categoryId): void
    {
        foreach (range(1, 6) as $level) {
            $set('category_level_' . $level, null);
        }

        if (!$categoryId) {
            return;
        }

        $chain = static::getCategoryChainIds((int) $categoryId);

        foreach ($chain as $index => $id) {
            $set('category_level_' . ($index + 1), $id);
        }
    }

    private static function getCategoryChainIds(int $categoryId): array
    {
        $chain = [];
        $current = Category::query()->select('id', 'parent_id')->find($categoryId);

        while ($current) {
            array_unshift($chain, $current->id);

            if (!$current->parent_id) {
                break;
            }

            $current = Category::query()->select('id', 'parent_id')->find($current->parent_id);
        }

        return array_slice($chain, 0, 6);
    }

    private static function applyRestaurantFieldReset(callable $get, callable $set): void
    {
        if (!static::isRestaurantCategory($get('category_id'))) {
            return;
        }

        $set('model', null);
        $set('sku', null);
        $set('barcode', null);
        $set('country_id', null);
        $set('sale_country_id', null);
    }

    private static function getInheritedAttributeOptions(?int $categoryId): array
    {
        if (!$categoryId) {
            return ['_placeholder' => __('custom.products.form.select_category_first')];
        }

        $options = \App\Models\CategoryAttribute::query()
            ->forCategoryTree($categoryId)
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        if (empty($options)) {
            return ['_placeholder' => __('custom.products.form.no_attributes')];
        }

        return $options;
    }

    private static function isColorAttribute($attributeId): bool
    {
        if (!$attributeId || $attributeId === '_placeholder') {
            return false;
        }

        $attribute = \App\Models\CategoryAttribute::query()->find($attributeId);

        return \App\Support\AttributeColorHex::isColorAttribute(
            $attribute?->type,
            $attribute?->getTranslations('name') ?? []
        );
    }

    private static function getAttributeValueHexById(int $valueId): ?string
    {
        $value = \App\Models\AttributeValue::find($valueId);
        if (!$value) {
            return null;
        }

        $hex = $value->getTranslation('name', app()->getLocale(), false);
        if (!$hex) {
            $hex = $value->getTranslation('name', 'en', false)
                ?: $value->getTranslation('name', 'ar', false);
        }

        return is_string($hex) && $hex !== '' ? $hex : null;
    }

    private static function getColorOptions(int $attributeId): array
    {
        $values = AttributeValue::with('color')
            ->where('category_attribute_id', $attributeId)
            ->get();

        $options = [];
        foreach ($values as $value) {
            $label = static::getColorOptionLabel($value);

            if (!$label) {
                continue;
            }

            $options[$value->id] = $label;
        }

        return $options;
    }

    private static function getColorOptionLabel(AttributeValue $value): ?string
    {
        $color = $value->color;
        $hex = \App\Support\AttributeColorHex::normalize($color?->hex);

        if (!$hex) {
            return null;
        }

        $name = $color ? static::getTranslatedColorName($color) : null;

        if (!$name) {
            $name = $value->getTranslation('name', app()->getLocale(), false)
                ?: $value->getTranslation('name', 'en', false)
                ?: $value->getTranslation('name', 'ar', false)
                ?: $hex;
        }

        return static::renderColorSwatch($name, $hex);
    }

    private static function getTranslatedColorName(Color $color): ?string
    {
        $name = $color->getTranslation('name', app()->getLocale(), false)
            ?: $color->getTranslation('name', 'en', false)
            ?: $color->getTranslation('name', 'ar', false);

        return is_string($name) && $name !== '' ? $name : null;
    }

    private static function renderColorSwatch(string $name, string $hex): string
    {
        $nameSafe = e($name);
        $hexSafe = e($hex);

        return '<span style="display:inline-flex;align-items:center;gap:8px;">' .
            '<span style="display:inline-block;width:18px;height:18px;border-radius:9999px;background:' . $hexSafe . ';border:1px solid #d1d5db;"></span>' .
            '<span style="display:flex;flex-direction:column;line-height:1.1;">' .
            '<span>' . $nameSafe . '</span>' .
            '<small style="color:#6b7280;">' . $hexSafe . '</small>' .
            '</span>' .
            '</span>';
    }

    private static function badgeOptions(): array
    {
        return \App\Models\Badge::query()
            ->get()
            ->mapWithKeys(function ($badge) {
                $name = $badge->name;

                if (is_array($name)) {
                    $label = (string) ($name[app()->getLocale()] ?? $name['ar'] ?? $name['en'] ?? $badge->id);
                } else {
                    $label = (string) ($name ?: $badge->id);
                }

                return [$badge->id => $label];
            })
            ->toArray();
    }

}
