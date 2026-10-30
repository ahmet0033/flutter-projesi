<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Reports\SalesReport;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('newProduct')
                ->label('New product')
                ->icon('heroicon-o-plus')
                ->url(fn (): string => ProductResource::getUrl('create'))
                ->visible(fn (): bool => (bool) auth()->user()?->can('Create:Product')),
            Action::make('orders')
                ->label('Orders')
                ->icon('heroicon-o-shopping-bag')
                ->color('gray')
                ->url(fn (): string => OrderResource::getUrl('index'))
                ->visible(fn (): bool => (bool) auth()->user()?->can('ViewAny:Order')),
            Action::make('reports')
                ->label('Reports')
                ->icon('heroicon-o-chart-bar')
                ->color('gray')
                ->url(fn (): string => SalesReport::getUrl())
                ->visible(fn (): bool => (bool) auth()->user()?->can('ViewReports')),
            Action::make('settings')
                ->label('Settings')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->url(fn (): string => ManageStoreSettings::getUrl())
                ->visible(fn (): bool => (bool) auth()->user()?->can('ManageSettings')),
        ];
    }
}
