<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Support\StoreSettings;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentOrders extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Son siparişler';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Order::query()->latest('placed_at')->limit(6))
            ->columns([
                TextColumn::make('number')
                    ->label('Sipariş')
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label('Müşteri')
                    ->limit(18),
                TextColumn::make('total_cents')
                    ->label('Toplam')
                    ->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_PAID, Order::STATUS_DELIVERED => 'success',
                        Order::STATUS_PROCESSING => 'warning',
                        Order::STATUS_SHIPPED => 'info',
                        Order::STATUS_CANCELLED, Order::STATUS_REFUNDED => 'danger',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Görüntüle')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('Henüz sipariş yok')
            ->emptyStateDescription('Yeni siparişler burada görünecek.')
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->paginated(false);
    }
}
