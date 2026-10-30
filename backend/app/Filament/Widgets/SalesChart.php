<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Support\StoreSettings;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChart extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected ?string $heading = 'Son 7 gün ciro';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '340px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $start = now()->subDays(6)->startOfDay();
        $rows = Order::query()
            ->where('payment_status', Order::PAYMENT_PAID)
            ->whereNotNull('placed_at')
            ->where('placed_at', '>=', $start)
            ->selectRaw('DATE(placed_at) as day, SUM(total_cents) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = Carbon::parse($date)->translatedFormat('D');
            $values[] = round(((int) ($rows[$date->toDateString()] ?? 0)) / 100, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ciro ('.app(StoreSettings::class)->currency().')',
                    'data' => $values,
                    'borderColor' => '#ED2839',
                    'backgroundColor' => 'rgba(237, 40, 57, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }
}
