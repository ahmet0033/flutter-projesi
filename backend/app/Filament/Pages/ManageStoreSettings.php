<?php

namespace App\Filament\Pages;

use App\Support\StoreSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageStoreSettings extends Page
{
    protected static ?string $slug = 'settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Store settings';

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(app(StoreSettings::class)->toArray());
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('ManageSettings');
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Store identity')
                ->schema([
                    TextInput::make('store_name')->required()->maxLength(80),
                    Select::make('currency')
                        ->options([
                            'USD' => 'USD ($)',
                            'EUR' => 'EUR (€)',
                            'GBP' => 'GBP (£)',
                            'TRY' => 'TRY (₺)',
                        ])
                        ->required(),
                    TextInput::make('order_prefix')->required()->maxLength(12),
                    TextInput::make('support_email')->email(),
                    TextInput::make('support_phone')->tel(),
                    Textarea::make('address')->rows(3)->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('Tax')
                ->schema([
                    TextInput::make('tax_rate')
                        ->label('Tax rate')
                        ->numeric()
                        ->suffix('%')
                        ->minValue(0)
                        ->maxValue(100)
                        ->required(),
                    Toggle::make('tax_inclusive')
                        ->label('Prices include tax')
                        ->helperText('When enabled, order tax is included in the displayed price.'),
                ])
                ->columns(2),
            Section::make('Social links')
                ->schema([
                    TextInput::make('instagram_url')->url(),
                    TextInput::make('x_url')->label('X / Twitter')->url(),
                    TextInput::make('facebook_url')->url(),
                ])
                ->columns(3),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->getFormContentComponent(),
        ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->key('form-actions'),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save settings')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function save(): void
    {
        app(StoreSettings::class)->update($this->form->getState());

        Notification::make()
            ->title('Store settings saved')
            ->success()
            ->send();
    }
}
