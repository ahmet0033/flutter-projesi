<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\StoreSettings;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CommerceStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $paidOrders = Order::query()->where('payment_status', Order::PAYMENT_PAID);
        $revenue = (int) $paidOrders->clone()->sum('total_cents');
        $paidCount = $paidOrders->clone()->count();
        $average = $paidCount > 0 ? (int) round($revenue / $paidCount) : 0;

        return [
            Stat::make('Revenue', app(StoreSettings::class)->currency().' '.number_format($revenue / 100, 2))
                ->description('Paid orders')
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Orders', Order::query()->count())
                ->description('All orders')
                ->icon('heroicon-o-shopping-bag')
                ->color('primary'),
            Stat::make('Average order', app(StoreSettings::class)->currency().' '.number_format($average / 100, 2))
                ->description('Paid orders')
                ->icon('heroicon-o-chart-bar')
                ->color('info'),
            Stat::make('Customers', User::query()->whereHas('roles', fn ($query) => $query->where('name', 'customer'))->count())
                ->description('Registered customers')
                ->icon('heroicon-o-users')
                ->color('gray'),
            Stat::make('Low stock', ProductVariant::query()->where('stock_quantity', '<=', 5)->count())
                ->description('Variants at or below 5')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),
        ];
    }
}
