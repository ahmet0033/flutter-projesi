<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Reports\BaseReportPage;
use App\Models\ProductVariant;
use App\Support\StoreSettings;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryReport extends BaseReportPage
{
    protected static ?string $navigationLabel = 'Inventory';

    protected static ?string $title = 'Inventory report';

    protected static ?int $navigationSort = 4;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ProductVariant::query()->with(['product.category']))
            ->defaultSort('stock_quantity')
            ->columns([
                TextColumn::make('product.name')->label('Product')->searchable()->wrap(),
                TextColumn::make('product.category.name')->label('Category')->toggleable(),
                TextColumn::make('sku')->searchable(),
                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => $state <= 0 ? 'danger' : ($state <= 5 ? 'warning' : 'success')),
                TextColumn::make('price_cents')
                    ->label('Price')
                    ->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100)
                    ->placeholder('Product price'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('product.is_active')->label('Product active')->badge()->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('stock')
                    ->options([
                        'low' => 'Low stock (≤ 5)',
                        'out' => 'Out of stock',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(($data['value'] ?? null) === 'low', fn (Builder $query): Builder => $query->where('stock_quantity', '<=', 5))
                        ->when(($data['value'] ?? null) === 'out', fn (Builder $query): Builder => $query->where('stock_quantity', '<=', 0))),
            ])
            ->headerActions([
                $this->exportAction(),
            ])
            ->emptyStateHeading('Envanter kaydı bulunamadı')
            ->emptyStateDescription('Ürün varyantları burada listelenir.')
            ->emptyStateIcon('heroicon-o-archive-box')
            ->paginated([25, 50, 100]);
    }

    protected function exportCsv(): StreamedResponse
    {
        $records = $this->getFilteredTableQuery()?->get() ?? collect();
        $currency = app(StoreSettings::class)->currency();

        return $this->streamCsv(
            'inventory-report-'.now()->format('Y-m-d').'.csv',
            ['Product', 'SKU', 'Stock', 'Price', 'Currency', 'Active'],
            $records->map(fn (ProductVariant $variant): array => [
                $variant->product?->name,
                $variant->sku,
                $variant->stock_quantity,
                number_format(($variant->price_cents ?? $variant->product?->price_cents ?? 0) / 100, 2, '.', ''),
                $currency,
                $variant->is_active ? 'yes' : 'no',
            ]),
        );
    }
}
