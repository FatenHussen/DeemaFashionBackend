<?php

namespace App\Filament\Resources\VendorInventories\Tables;

use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class VendorInventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('inventory_total_qty', 'asc')
            ->columns([
                Tables\Columns\ImageColumn::make('inventory_image')
                    ->label(__('custom.products.image'))
                    ->getStateUsing(fn (Product $record): ?string => self::resolveProductImageUrl($record))
                    ->circular()
                    ->defaultImageUrl(asset('images/placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('custom.products.name'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label(__('custom.products.sku'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('variants_count')
                    ->label(__('custom.inventory.variants_count'))
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('inventory_total_qty')
                    ->label(__('custom.inventory.total_quantity'))
                    ->badge()
                    ->color(fn ($state) => ((int) $state) <= 10 ? 'danger' : 'success')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('inventory_total_qty', $direction)),
            ])
            ->actions([
                Action::make('updateInventory')
                    ->label(__('custom.inventory.update_quantities'))
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading(__('custom.inventory.update_modal_title'))
                    ->modalSubmitActionLabel(__('custom.save'))
                    ->fillForm(fn (Product $record): array => self::buildFormData($record))
                    ->form(fn (Product $record): array => self::buildFormSchema($record))
                    ->action(function (Product $record, array $data): void {
                        DB::transaction(function () use ($record, $data): void {
                            $total = 0;

                            foreach ($data as $key => $value) {
                                if (!str_starts_with((string) $key, 'qty_')) {
                                    continue;
                                }

                                $variantId = (int) str_replace('qty_', '', (string) $key);
                                $quantity = max(0, (int) $value);

                                $variant = ProductVariant::query()
                                    ->where('id', $variantId)
                                    ->where('product_id', $record->id)
                                    ->first();

                                if (!$variant) {
                                    continue;
                                }

                                $variant->update(['quantity' => $quantity]);
                                $total += $quantity;
                            }

                            $record->update(['quantity' => $total]);
                        });

                        Notification::make()
                            ->title(__('custom.inventory.updated_successfully'))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    private static function buildFormSchema(Product $record): array
    {
        $record->loadMissing(['variants.media']);

        $components = [];

        foreach ($record->variants as $variant) {
            $variantName = self::resolvedVariantName($variant->name);

            $components[] = Placeholder::make('variant_label_' . $variant->id)
                ->label(__('custom.inventory.variant_section_title', [
                    'variant' => $variantName,
                ]))
                ->content(function () use ($variant, $record): HtmlString {
                    $imageUrl = self::resolveVariantImageUrl($variant, $record);

                    if (!$imageUrl) {
                        return new HtmlString('');
                    }

                    return new HtmlString('<img src="' . e($imageUrl) . '" style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;" />');
                });

            $components[] = TextInput::make('qty_' . $variant->id)
                ->label(__('custom.inventory.variant_quantity_label', [
                    'variant' => $variantName,
                ]))
                ->numeric()
                ->integer()
                ->minValue(0)
                ->required();
        }

        if (empty($components)) {
            $components[] = Placeholder::make('no_variant_stock')
                ->label(__('custom.inventory.no_variant_stock_title'))
                ->content(__('custom.inventory.no_variant_stock_body'));
        }

        return $components;
    }

    private static function buildFormData(Product $record): array
    {
        $record->loadMissing(['variants']);

        $data = [];

        foreach ($record->variants as $variant) {
            $data['qty_' . $variant->id] = (int) $variant->quantity;
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
