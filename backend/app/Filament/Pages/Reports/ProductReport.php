<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Reports\BaseReportPage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\StoreSettings;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductReport extends BaseReportPage
{
    protected static ?string $navigationLabel = 'Products';

    protected static ?string $title = 'Product report';

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => OrderItem::query()
                ->selectRaw('product_id as id, product_id, SUM(quantity) as units_sold, SUM(line_total_cents) as revenue_cents')
                ->whereHas('order', fn (Builder $query) => $query->where('payment_status', Order::PAYMENT_PAID))
                ->with('product')
                ->groupBy('product_id'))
            ->defaultSort('revenue_cents', 'desc')
            ->columns([
                TextColumn::make('product.name')->label('Product')->wrap(),
                TextColumn::make('product.category.name')->label('Category')->toggleable(),
                TextColumn::make('units_sold')->label('Units')->numeric()->sortable()->summarize(Sum::make()->label('Units')),
                TextColumn::make('revenue_cents')
                    ->label('Revenue')
                    ->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100)
                    ->sortable()
                    ->summarize(Sum::make()->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100)->label('Revenue')),
            ])
            ->filters([
                $this->productDateFilter(),
            ])
            ->headerActions([
                $this->exportAction(),
            ])
            ->emptyStateHeading('Bu tarih aralığında ürün satışı yok')
            ->emptyStateDescription('Farklı bir tarih aralığı seçin.')
            ->emptyStateIcon('heroicon-o-cube')
            ->paginated([25, 50, 100]);
    }

    protected function productDateFilter(): Filter
    {
        return Filter::make('date_range')
            ->label('Date range')
            ->schema([
                DatePicker::make('from')->label('From')->default(now()->subDays(29)->toDateString()),
                DatePicker::make('until')->label('Until'),
            ])
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if ($data['from'] ?? null) {
                    $indicators[] = Indicator::make('Order date from '.$data['from'])
                        ->removeField('from');
                }

                if ($data['until'] ?? null) {
                    $indicators[] = Indicator::make('Order date until '.$data['until'])
                        ->removeField('until');
                }

                return $indicators;
            })
            ->query(fn (Builder $query, array $data): Builder => $query->whereHas('order', fn (Builder $query): Builder => $query
                ->where('payment_status', Order::PAYMENT_PAID)
                ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('placed_at', '>=', $date))
                ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('placed_at', '<=', $date))));
    }

    protected function exportCsv(): StreamedResponse
    {
        $records = $this->getFilteredTableQuery()?->get() ?? collect();

        return $this->streamCsv(
            'product-report-'.now()->format('Y-m-d').'.csv',
            ['Product', 'Category', 'Units sold', 'Revenue', 'Currency'],
            $records->map(fn (OrderItem $item): array => [
                $item->product?->name,
                $item->product?->category?->name,
                $item->units_sold,
                number_format($item->revenue_cents / 100, 2, '.', ''),
                app(StoreSettings::class)->currency(),
            ]),
        );
    }
}
