<?php

namespace App\Filament\Resources\Shops\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class ShopsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label(__('custom.shops.logo'))->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('custom.shops.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('custom.shops.phone'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('area.name')
                    ->label(__('custom.shops.area'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('average_rating')
                    ->label(__('custom.shops.rating'))
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('min_order_amount')
                    ->label(__('custom.shops.min_order_amount'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivery_min_hours')
                    ->label(__('custom.shops.delivery_min_hours'))
                    ->suffix(' ' . __('custom.shops.hours')),

                Tables\Columns\TextColumn::make('delivery_max_hours')
                    ->label(__('custom.shops.delivery_max_hours'))
                    ->suffix(' ' . __('custom.shops.hours')),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('custom.shops.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('custom.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('custom.shops.is_active'))
                    ->placeholder(__('custom.all'))
                    ->trueLabel(__('custom.active'))
                    ->falseLabel(__('custom.inactive')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
