<?php

namespace App\Filament\Resources\Orders\Actions;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class OrderActions
{
    /**
     * @return array<Action>
     */
    public static function fulfillment(): array
    {
        return [
            Action::make('markProcessing')
                ->label('Processing')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('warning')
                ->visible(fn (Order $record): bool => $record->status === Order::STATUS_PAID)
                ->requiresConfirmation()
                ->action(function (Order $record): void {
                    $record->update(['status' => Order::STATUS_PROCESSING]);
                    Notification::make()->title('Order moved to processing')->success()->send();
                }),
            Action::make('markShipped')
                ->label('Shipped')
                ->icon('heroicon-o-truck')
                ->color('info')
                ->visible(fn (Order $record): bool => $record->status === Order::STATUS_PROCESSING)
                ->requiresConfirmation()
                ->action(function (Order $record): void {
                    $record->update(['status' => Order::STATUS_SHIPPED]);
                    Notification::make()->title('Order marked as shipped')->success()->send();
                }),
            Action::make('markDelivered')
                ->label('Delivered')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn (Order $record): bool => $record->status === Order::STATUS_SHIPPED)
                ->requiresConfirmation()
                ->action(function (Order $record): void {
                    $record->update(['status' => Order::STATUS_DELIVERED]);
                    Notification::make()->title('Order marked as delivered')->success()->send();
                }),
        ];
    }
}
