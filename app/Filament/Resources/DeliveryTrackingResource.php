<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryTrackingResource\Pages;
use App\Models\DeliveryTracking;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DeliveryTrackingResource extends Resource
{
    protected static ?string $model = DeliveryTracking::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-map-pin';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Orders';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Delivery Assignment')
                    ->schema([
                        \Filament\Forms\Components\Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'order_number')
                            ->required()
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\TextInput::make('estimated_minutes')
                            ->numeric()
                            ->default(0)
                            ->label('ETA (minutes)'),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('Rider Info')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('rider_name')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('rider_phone')
                            ->tel()
                            ->maxLength(255),
                        \Filament\Forms\Components\FileUpload::make('rider_avatar')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->openable()
                            ->directory('rider-avatars'),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('Coordinates')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('rider_latitude')
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('rider_longitude')
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('user_latitude')
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('user_longitude')
                            ->numeric(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('rider_name')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('rider_phone'),
                \Filament\Tables\Columns\TextColumn::make('estimated_minutes')
                    ->label('ETA (mins)')
                    ->numeric()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDeliveryTrackings::route('/')
        ];
    }
}
