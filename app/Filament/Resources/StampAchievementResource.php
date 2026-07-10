<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StampAchievementResource\Pages;
use App\Filament\Resources\StampAchievementResource\RelationManagers;
use App\Models\StampAchievement;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StampAchievementResource extends Resource
{
    protected static ?string $model = StampAchievement::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-star';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Rewards';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Achievement Info')
                    ->schema([
                        \Filament\Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\TextInput::make('category')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\Textarea::make('description')
                            ->rows(2),
                        \Filament\Forms\Components\TextInput::make('icon')
                            ->maxLength(255),
                        \Filament\Forms\Components\ColorPicker::make('color'),
                        \Filament\Forms\Components\ColorPicker::make('accent_color'),
                        \Filament\Forms\Components\TextInput::make('reward')
                            ->maxLength(255),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Progress')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('collected')
                            ->numeric()
                            ->required()
                            ->default(0),
                        \Filament\Forms\Components\TextInput::make('required')
                            ->numeric()
                            ->required()
                            ->default(8),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Stamp History')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('histories')
                            ->relationship('histories')
                            ->schema([
                                \Filament\Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                \Filament\Forms\Components\TextInput::make('product_name')
                                    ->required(),
                                \Filament\Forms\Components\DatePicker::make('stamped_date')
                                    ->required(),
                                Forms\Components\TimePicker::make('stamped_time')
                                    ->seconds(false)
                                    ->required(),
                            ])
                            ->columns(4)
                            ->collapsible(),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('category')
                    ->badge(),
                \Filament\Tables\Columns\TextColumn::make('label')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('collected')
                    ->numeric()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('required')
                    ->numeric(),
                \Filament\Tables\Columns\TextColumn::make('reward')
                    ->limit(40),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Drinks'     => 'Drinks',
                        'Foods'      => 'Foods',
                        'Desserts'   => 'Desserts',
                        'Pasalubong' => 'Pasalubong',
                    ]),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStampAchievements::route('/'),
            'create' => Pages\CreateStampAchievement::route('/create'),
            'edit'   => Pages\EditStampAchievement::route('/{record}/edit'),
        ];
    }
}
