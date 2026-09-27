<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\VendorUser;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class LowStockProducts extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): ?string
    {
        return __('custom.stats.low_stock_products');
    }

    public function table(Table $table): Table
    {
        /** @var VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return $table->query(
                fn() => Product::query()->whereRaw('1 = 0')
            );
        }

        $vendorId = $user->vendor_id;

        return $table
            ->query(
                Product::where('vendor_id', $vendorId)
                    ->where('quantity', '<=', 10)
                    ->where('quantity', '>', 0)
                    ->orderBy('quantity', 'asc')
            )
            ->columns([
                Tables\Columns\ImageColumn::make('media')
                    ->label(__('custom.products.image'))
                    ->getStateUsing(fn($record) => $record->image_url)
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('custom.products.name'))
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('sku')
                    ->label(__('custom.products.sku'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('custom.products.quantity'))
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state <= 5 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('custom.products.price'))
                    ->money('USD'),
            ])
            ->paginated([5, 10]);
    }
}
