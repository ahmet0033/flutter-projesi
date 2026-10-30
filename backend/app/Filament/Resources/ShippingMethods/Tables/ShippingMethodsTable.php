<?php

namespace App\Filament\Resources\ShippingMethods\Tables;

use App\Support\StoreSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ShippingMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price_cents')
                    ->label('Price')
                    ->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100)
                    ->sortable(),
                TextColumn::make('free_over_cents')
                    ->label('Free over')
                    ->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100)
                    ->placeholder('-'),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Active')
                    ->options(['1' => 'Active', '0' => 'Inactive']),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
