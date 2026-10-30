<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use App\Support\StoreSettings;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withSum('variants', 'stock_quantity'))
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->getStateUsing(fn (Product $record) => $record->primaryImageUrl())
                    ->height(44),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->sortable(),
                TextColumn::make('price_cents')
                    ->label('Price')
                    ->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100)
                    ->sortable(),
                TextColumn::make('variants_sum_stock_quantity')
                    ->label('Stock')
                    ->numeric()
                    ->badge()
                    ->color(fn (?int $state): string => $state === null || $state <= 0 ? 'danger' : ($state <= 5 ? 'warning' : 'success'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->label('Brand')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Active'),
                TernaryFilter::make('is_featured')
                    ->label('Featured'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()->title('Products activated')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()->title('Products deactivated')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('adjustPrice')
                        ->label('Adjust price')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('info')
                        ->schema([
                            Select::make('direction')
                                ->options(['increase' => 'Increase', 'decrease' => 'Decrease'])
                                ->required()
                                ->default('increase'),
                            TextInput::make('percent')
                                ->label('Percentage')
                                ->numeric()
                                ->suffix('%')
                                ->minValue(0)
                                ->maxValue(100)
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $factor = 1 + (($data['direction'] === 'increase' ? 1 : -1) * ((float) $data['percent'] / 100));

                            $records->each(function (Product $product) use ($factor): void {
                                $product->update([
                                    'price_cents' => max(0, (int) round($product->price_cents * $factor)),
                                ]);
                            });

                            Notification::make()->title('Product prices updated')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('feature')
                        ->label('Feature')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => true]);
                            Notification::make()->title('Products featured')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unfeature')
                        ->label('Unfeature')
                        ->icon('heroicon-o-star')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => false]);
                            Notification::make()->title('Products unfeatured')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
