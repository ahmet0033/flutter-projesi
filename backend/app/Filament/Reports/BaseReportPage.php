<?php

namespace App\Filament\Reports;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class BaseReportPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected Width|string|null $maxContentWidth = Width::Full;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('ViewReports');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    protected function dateFilter(string $column = 'placed_at', bool $defaultLast30Days = false, string $label = 'Date'): Filter
    {
        $from = DatePicker::make('from')->label('From');

        if ($defaultLast30Days) {
            $from->default(now()->subDays(29)->toDateString());
        }

        return Filter::make('date_range')
            ->label('Date range')
            ->schema([
                $from,
                DatePicker::make('until')->label('Until'),
            ])
            ->indicateUsing(function (array $data) use ($label): array {
                $indicators = [];

                if ($data['from'] ?? null) {
                    $indicators[] = Indicator::make($label.' from '.$data['from'])
                        ->removeField('from');
                }

                if ($data['until'] ?? null) {
                    $indicators[] = Indicator::make($label.' until '.$data['until'])
                        ->removeField('until');
                }

                return $indicators;
            })
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate($column, '>=', $date))
                ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate($column, '<=', $date)));
    }

    protected function exportAction(): Action
    {
        return Action::make('export')
            ->label('Export CSV')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->visible(fn (): bool => (bool) auth()->user()?->can('ExportReports'))
            ->action(fn (): StreamedResponse => $this->exportCsv());
    }

    /**
     * @param  array<string>  $headers
     * @param  iterable<array<mixed>>  $rows
     */
    protected function streamCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    abstract protected function exportCsv(): StreamedResponse;
}
