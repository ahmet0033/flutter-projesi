<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Support\StoreSettings;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopProducts extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'En çok satanlar';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Product::query()
                ->withSum('orderItems', 'quantity')
                ->whereHas('orderItems')
                ->orderByDesc('order_items_sum_quantity')
                ->limit(6))
            ->columns([
                TextColumn::make('name')
                    ->label('Ürün')
                    ->limit(24),
                TextColumn::make('order_items_sum_quantity')
                    ->label('Adet')
                    ->numeric(),
                TextColumn::make('price_cents')
                    ->label('Fiyat')
                    ->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Düzenle')
                    ->url(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('Henüz satış yok')
            ->emptyStateDescription('Siparişler geldikçe en çok satanlar burada listelenir.')
            ->emptyStateIcon('heroicon-o-cube')
            ->paginated(false);
    }
}
