<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('media')
                    ->label(__('custom.products.image'))
                    ->getStateUsing(fn ($record) => $record->image_url)
                    ->circular()
                    ->defaultImageUrl(asset('images/placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('custom.products.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label(__('custom.products.sku'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('custom.products.category'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label(__('custom.products.brand'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('custom.products.price'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price_after_discount')
                    ->label(__('custom.products.price_after_discount'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('custom.products.quantity'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('average_rating')
                    ->label(__('custom.products.rating'))
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('approval_status')
                    ->label(__('custom.products.approval_status'))
                    ->badge()
                    ->color(fn(\App\Enums\ProductApprovalStatus $state): string => match ($state) {
                        \App\Enums\ProductApprovalStatus::PENDING => 'warning',
                        \App\Enums\ProductApprovalStatus::APPROVED => 'success',
                        \App\Enums\ProductApprovalStatus::REJECTED => 'danger',
                    })
                    ->formatStateUsing(fn(\App\Enums\ProductApprovalStatus $state): string => __('custom.products.approval_statuses.' . $state->value))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('custom.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('custom.products.category'))
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('brand_id')
                    ->label(__('custom.products.brand'))
                    ->relationship('brand', 'name'),

                Tables\Filters\SelectFilter::make('approval_status')
                    ->label(__('custom.products.approval_status'))
                    ->options([
                        'pending' => __('custom.products.approval_statuses.pending'),
                        'approved' => __('custom.products.approval_statuses.approved'),
                        'rejected' => __('custom.products.approval_statuses.rejected'),
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('updatePrices')
                    ->label(__('custom.products.actions.update_prices'))
                    ->icon('heroicon-m-currency-dollar')
                    ->modalHeading(__('custom.products.actions.update_prices_modal_title'))
                    ->modalSubmitActionLabel(__('custom.save'))
                    ->fillForm(fn (Product $record): array => self::buildPriceFormData($record))
                    ->form(fn (Product $record): array => self::buildPriceFormSchema($record))
                    ->action(function (Product $record, array $data): void {
                        DB::transaction(function () use ($record, $data): void {
                            $basePrice = isset($data['product_price']) ? (float) $data['product_price'] : (float) $record->price;
                            $record->update(['price' => round($basePrice, 2)]);

                            foreach ($data as $key => $value) {
                                if (!str_starts_with((string) $key, 'price_variant_')) {
                                    continue;
                                }

                                $variantId = (int) str_replace('price_variant_', '', (string) $key);
                                $price = max(0, (float) $value);

                                $variant = $record->variants()
                                    ->whereKey($variantId)
                                    ->first();

                                if (!$variant) {
                                    continue;
                                }

                                $variant->update(['price' => round($price, 2)]);
                            }
                        });

                        Notification::make()
                            ->title(__('custom.products.actions.prices_updated'))
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ]);
    }

    private static function buildPriceFormSchema(Product $record): array
    {
        $record->loadMissing(['media', 'variants.media']);

        $components = [
            TextInput::make('product_price')
                ->label(__('custom.products.actions.base_price'))
                ->numeric()
                ->minValue(0)
                ->required(),
        ];

        foreach ($record->variants as $variant) {
            $components[] = Placeholder::make('price_variant_label_' . $variant->id)
                ->label(__('custom.products.actions.variant_prices_section', [
                    'variant' => self::resolvedVariantName($variant->name),
                ]))
                ->content(function () use ($variant, $record): HtmlString {
                    $imageUrl = self::resolveVariantImageUrl($variant, $record);

                    if (!$imageUrl) {
                        return new HtmlString('');
                    }

                    return new HtmlString('<img src="' . e($imageUrl) . '" style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;" />');
                });

            $components[] = TextInput::make('price_variant_' . $variant->id)
                ->label(__('custom.products.actions.variant_price_label', [
                    'variant' => self::resolvedVariantName($variant->name),
                ]))
                ->numeric()
                ->minValue(0)
                ->required();
        }

        return $components;
    }

    private static function buildPriceFormData(Product $record): array
    {
        $record->loadMissing(['variants']);

        $data = [
            'product_price' => (float) $record->price,
        ];

        foreach ($record->variants as $variant) {
            $data['price_variant_' . $variant->id] = (float) $variant->price;
        }

        return $data;
    }

    private static function resolvedVariantName(mixed $name): string
    {
        if (is_array($name)) {
            return (string) ($name[app()->getLocale()] ?? $name['ar'] ?? $name['en'] ?? '-');
        }

        if (is_string($name) && $name !== '') {
            return $name;
        }

        return '-';
    }

    private static function resolveProductImageUrl(Product $product): ?string
    {
        return $product->image_url;
    }

    private static function resolveVariantImageUrl(mixed $variant, Product $product): ?string
    {
        $variantMedia = $variant->media->first();

        if ($variantMedia && !empty($variantMedia->path)) {
            return asset('storage/' . $variantMedia->path);
        }

        return self::resolveProductImageUrl($product);
    }
}
