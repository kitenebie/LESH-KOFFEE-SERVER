<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreResource\Pages;
use App\Models\Store;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-building-storefront';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Store Information')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('tagline')
                            ->maxLength(255),
                        \Filament\Forms\Components\Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),
                        \Filament\Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('hours')
                            ->maxLength(255)
                            ->placeholder('Mon - Sun: 7:00 AM - 10:00 PM'),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Spotlight Customer')
                    ->relationship('spotlightCustomer')
                    ->schema([
                        \Filament\Forms\Components\Select::make('user_id')
                            ->label('Linked User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        \Filament\Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('cups_this_month')
                            ->numeric()
                            ->default(0),
                        \Filament\Forms\Components\TextInput::make('avatar')
                            ->url()
                            ->maxLength(255),
                        \Filament\Forms\Components\Textarea::make('reward')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('tagline'),
                \Filament\Tables\Columns\TextColumn::make('address')
                    ->limit(40),
                \Filament\Tables\Columns\TextColumn::make('phone'),
                \Filament\Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('hours'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStores::route('/')
        ];
    }
}
