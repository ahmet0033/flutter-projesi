<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('rating')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 4 ? 'success' : ($state === 3 ? 'warning' : 'danger')),
                TextColumn::make('title')
                    ->limit(30),
                TextColumn::make('body')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_approved')
                    ->boolean(),
                IconColumn::make('is_verified_purchase')
                    ->label('Verified')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_approved')
                    ->label('Approved')
                    ->options(['1' => 'Approved', '0' => 'Pending']),
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('rating')
                    ->options([
                        5 => '5 stars',
                        4 => '4 stars',
                        3 => '3 stars',
                        2 => '2 stars',
                        1 => '1 star',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Review $record): bool => ! $record->is_approved)
                    ->action(function (Review $record): void {
                        $record->update(['is_approved' => true, 'approved_at' => now()]);
                        Notification::make()->title('Review approved')->success()->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Review $record): bool => $record->is_approved)
                    ->requiresConfirmation()
                    ->action(function (Review $record): void {
                        $record->update(['is_approved' => false, 'approved_at' => null]);
                        Notification::make()->title('Review hidden')->success()->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
