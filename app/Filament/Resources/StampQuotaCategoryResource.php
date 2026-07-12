<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StampQuotaCategoryResource\Pages;
use App\Models\StampQuotaCategory;
use App\Models\Category;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StampQuotaCategoryResource extends Resource
{
    protected static ?string $model = StampQuotaCategory::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-star';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Loyalty';
    }

    public static function getNavigationLabel(): string
    {
        return 'Stamp Quota Tiers';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Tier Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('e.g. Bronze'),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('e.g. bronze'),
                        Forms\Components\TextInput::make('rank')
                            ->required()
                            ->numeric()
                            ->helperText('Order of progression (1 = lowest tier)'),
                        Forms\Components\ColorPicker::make('color')
                            ->default('#CD7F32'),
                        Forms\Components\TextInput::make('icon')
                            ->placeholder('e.g. trophy-outline'),
                        Forms\Components\TextInput::make('reward_points')
                            ->numeric()
                            ->default(0)
                            ->helperText('Loyalty points awarded when user completes ALL requirements for this tier'),
                        Forms\Components\Textarea::make('description')
                            ->rows(2),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Stamp Requirements')
                    ->description('Define how many purchases per category are needed to complete this tier')
                    ->schema([
                        Forms\Components\Repeater::make('requirements')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->label('Product Category')
                                    ->options(Category::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                Forms\Components\TextInput::make('required_count')
                                    ->label('Required Purchases')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->placeholder('e.g. 5'),
                                Forms\Components\TextInput::make('points_per_stamp')
                                    ->label('Points Per Stamp')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Points awarded for each individual stamp'),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('+ Add Requirement')
                            ->reorderable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->sortable()
                    ->width(60),
                Tables\Columns\ColorColumn::make('color'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('requirements_count')
                    ->counts('requirements')
                    ->label('Requirements'),
                Tables\Columns\TextColumn::make('reward_points')
                    ->label('Tier Points')
                    ->suffix(' pts'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users'),
            ])
            ->defaultSort('rank', 'asc')
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStampQuotaCategories::route('/'),
            'create' => Pages\CreateStampQuotaCategory::route('/create'),
            'edit' => Pages\EditStampQuotaCategory::route('/{record}/edit'),
        ];
    }
}
