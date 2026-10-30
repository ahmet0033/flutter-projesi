<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\Actions\OrderActions;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\StoreSettings;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('placed_at', 'desc')
            ->columns([
                TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('total_cents')
                    ->label('Total')
                    ->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_PENDING => 'gray',
                        Order::STATUS_PAID, Order::STATUS_DELIVERED => 'success',
                        Order::STATUS_PROCESSING => 'warning',
                        Order::STATUS_SHIPPED => 'info',
                        Order::STATUS_CANCELLED, Order::STATUS_REFUNDED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::PAYMENT_PAID => 'success',
                        Order::PAYMENT_FAILED => 'danger',
                        Order::PAYMENT_REFUNDED => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('payment_method')
                    ->label('Payment'),
                TextColumn::make('placed_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Order::STATUS_PENDING => 'Pending',
                        Order::STATUS_PAID => 'Paid',
                        Order::STATUS_PROCESSING => 'Processing',
                        Order::STATUS_SHIPPED => 'Shipped',
                        Order::STATUS_DELIVERED => 'Delivered',
                        Order::STATUS_CANCELLED => 'Cancelled',
                        Order::STATUS_REFUNDED => 'Refunded',
                    ]),
                SelectFilter::make('payment_status')
                    ->label('Payment status')
                    ->options([
                        Order::PAYMENT_PENDING => 'Pending',
                        Order::PAYMENT_PAID => 'Paid',
                        Order::PAYMENT_FAILED => 'Failed',
                        Order::PAYMENT_REFUNDED => 'Refunded',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('markPaid')
                    ->label('Mark paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->payment_status !== Order::PAYMENT_PAID)
                    ->requiresConfirmation()
                    ->action(function (Order $record): void {
                        app(OrderService::class)->markPaid($record);
                        Notification::make()->title('Order marked as paid')->success()->send();
                    }),
                ...OrderActions::fulfillment(),
                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $record): bool => $record->canCancel())
                    ->requiresConfirmation()
                    ->action(function (Order $record): void {
                        app(OrderService::class)->cancel($record);
                        Notification::make()->title('Order cancelled')->success()->send();
                    }),
                Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (Order $record): bool => $record->canRefund())
                    ->requiresConfirmation()
                    ->action(function (Order $record): void {
                        app(OrderService::class)->refund($record);
                        Notification::make()->title('Order refunded')->success()->send();
                    }),
            ]);
    }
}
