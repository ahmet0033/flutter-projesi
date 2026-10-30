<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->limit(40),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('value')
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percentage'
                        ? $state.'%'
                        : '$'.number_format($state / 100, 2))
                    ->label('Value'),
                TextColumn::make('used_count')
                    ->label('Used')
                    ->formatStateUsing(fn ($state, $record) => $record->usage_limit
                        ? "{$state}/{$record->usage_limit}"
                        : (string) $state),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed',
                    ]),
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
