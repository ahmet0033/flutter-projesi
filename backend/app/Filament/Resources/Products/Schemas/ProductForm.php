<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Support\StoreSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('tagline'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('price_cents')
                    ->label('Price')
                    ->prefix('$')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->formatStateUsing(fn ($state) => $state === null
                        ? null
                        : number_format(((int) $state) / 100, 2, '.', ''))
                    ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
                TextInput::make('compare_at_price_cents')
                    ->label('Compare at price')
                    ->prefix('$')
                    ->numeric()
                    ->minValue(0)
                    ->formatStateUsing(fn ($state) => $state === null
                        ? null
                        : number_format(((int) $state) / 100, 2, '.', ''))
                    ->dehydrateStateUsing(fn ($state) => filled($state)
                        ? (int) round(((float) $state) * 100)
                        : null),
                TextInput::make('currency')
                    ->required()
                    ->default(fn (): string => app(StoreSettings::class)->currency())
                    ->maxLength(3),
                FileUpload::make('image_path')
                    ->label('Primary image')
                    ->disk(config('catalog.media_disk'))
                    ->directory('products')
                    ->visibility('public')
                    ->image(),
                TextInput::make('image_url')
                    ->label('External image URL')
                    ->url()
                    ->helperText('Used when no uploaded image exists (demo seed data).'),
                KeyValue::make('specs')
                    ->keyLabel('Spec')
                    ->valueLabel('Value')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->default(true),
                Toggle::make('is_featured')
                    ->default(false),
                Repeater::make('variants')
                    ->relationship()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->default('Default'),
                        TextInput::make('sku')
                            ->required(),
                        TextInput::make('price_cents')
                            ->label('Price')
                            ->prefix('$')
                            ->numeric()
                            ->minValue(0)
                            ->formatStateUsing(fn ($state) => $state === null
                                ? null
                                : number_format(((int) $state) / 100, 2, '.', ''))
                            ->dehydrateStateUsing(fn ($state) => filled($state)
                                ? (int) round(((float) $state) * 100)
                                : null),
                        TextInput::make('stock_quantity')
                            ->required()
                            ->numeric()
                            ->default(0),
                        KeyValue::make('options')
                            ->keyLabel('Option')
                            ->valueLabel('Value'),
                        Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                    ->columnSpanFull(),
                Repeater::make('images')
                    ->relationship()
                    ->schema([
                        FileUpload::make('path')
                            ->label('Image')
                            ->disk(config('catalog.media_disk'))
                            ->directory('products')
                            ->visibility('public')
                            ->image(),
                        TextInput::make('url')
                            ->label('External URL')
                            ->url(),
                        TextInput::make('alt'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
