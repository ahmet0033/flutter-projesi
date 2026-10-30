<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Reports\BaseReportPage;
use App\Models\Order;
use App\Support\StoreSettings;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReport extends BaseReportPage
{
    protected static ?string $navigationLabel = 'Sales';

    protected static ?string $title = 'Sales report';

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Order::query()->where('payment_status', Order::PAYMENT_PAID))
            ->defaultSort('placed_at', 'desc')
            ->columns([
                TextColumn::make('number')->label('Order')->searchable()->sortable(),
                TextColumn::make('placed_at')->label('Date')->dateTime()->sortable(),
                TextColumn::make('customer_name')->label('Customer')->searchable()->limit(28),
                TextColumn::make('customer_email')->label('Email')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_cents')
                    ->label('Total')
                    ->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100)
                    ->sortable()
                    ->summarize(Sum::make()->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100)->label('Revenue')),
                TextColumn::make('status')->badge(),
                TextColumn::make('payment_method')->label('Payment')->badge(),
            ])
            ->filters([
                $this->dateFilter('placed_at', defaultLast30Days: true, label: 'Order date'),
                SelectFilter::make('payment_method')
                    ->options([
                        'cod' => 'Cash on delivery',
                        'bank_transfer' => 'Bank transfer',
                    ]),
            ])
            ->headerActions([
                $this->exportAction(),
            ])
            ->emptyStateHeading('Bu tarih aralığında satış yok')
            ->emptyStateDescription('Farklı bir tarih aralığı seçin veya sipariş bekleyin.')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->paginated([25, 50, 100]);
    }

    protected function exportCsv(): StreamedResponse
    {
        $records = $this->getFilteredTableQuery()?->get() ?? collect();
        $currency = app(StoreSettings::class)->currency();

        return $this->streamCsv(
            'sales-report-'.now()->format('Y-m-d').'.csv',
            ['Order', 'Date', 'Customer', 'Email', 'Status', 'Payment', 'Total', 'Currency'],
            $records->map(fn (Order $order): array => [
                $order->number,
                $order->placed_at?->toDateTimeString(),
                $order->customer_name,
                $order->customer_email,
                $order->status,
                $order->payment_method,
                number_format($order->total_cents / 100, 2, '.', ''),
                $currency,
            ]),
        );
    }
}
