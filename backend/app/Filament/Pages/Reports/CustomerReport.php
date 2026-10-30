<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Reports\BaseReportPage;
use App\Models\Order;
use App\Models\User;
use App\Support\StoreSettings;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerReport extends BaseReportPage
{
    protected static ?string $navigationLabel = 'Customers';

    protected static ?string $title = 'Customer report';

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => User::query()
                ->whereHas('roles', fn (Builder $query) => $query->where('name', 'customer'))
                ->withCount(['orders as paid_orders_count' => fn (Builder $query) => $query->where('payment_status', Order::PAYMENT_PAID)])
                ->withSum(['orders as paid_spend_cents' => fn (Builder $query) => $query->where('payment_status', Order::PAYMENT_PAID)], 'total_cents'))
            ->defaultSort('paid_spend_cents', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->toggleable(),
                TextColumn::make('paid_orders_count')->label('Paid orders')->numeric()->sortable()->summarize(Sum::make()->label('Orders')),
                TextColumn::make('paid_spend_cents')
                    ->label('Total spend')
                    ->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100)
                    ->sortable()
                    ->summarize(Sum::make()->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100)->label('Spend')),
                TextColumn::make('created_at')->label('Joined')->dateTime()->sortable(),
            ])
            ->filters([
                $this->dateFilter('created_at'),
            ])
            ->headerActions([
                $this->exportAction(),
            ])
            ->emptyStateHeading('Müşteri bulunamadı')
            ->emptyStateDescription('Kayıtlı müşteriler burada listelenir.')
            ->emptyStateIcon('heroicon-o-users')
            ->paginated([25, 50, 100]);
    }

    protected function exportCsv(): StreamedResponse
    {
        $records = $this->getFilteredTableQuery()?->get() ?? collect();
        $currency = app(StoreSettings::class)->currency();

        return $this->streamCsv(
            'customer-report-'.now()->format('Y-m-d').'.csv',
            ['Name', 'Email', 'Paid orders', 'Total spend', 'Currency', 'Joined'],
            $records->map(fn (User $user): array => [
                $user->name,
                $user->email,
                $user->paid_orders_count,
                number_format(($user->paid_spend_cents ?? 0) / 100, 2, '.', ''),
                $currency,
                $user->created_at?->toDateTimeString(),
            ]),
        );
    }
}
