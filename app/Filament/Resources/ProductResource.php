<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-shopping-bag';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Catalog';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Product Details')
                    ->schema([
                        \Filament\Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        \Filament\Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->prefix('₱'),
                        \Filament\Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('products')
                            ->columnSpanFull(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Stats & Visibility')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('rating')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(0)
                            ->maxValue(5)
                            ->default(0),
                        \Filament\Forms\Components\TextInput::make('reviews')
                            ->numeric()
                            ->default(0),
                        \Filament\Forms\Components\Toggle::make('is_popular')
                            ->label('Popular')
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Loyalty')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('loyalty_points')
                            ->label('Loyalty Points Earned')
                            ->helperText('Points awarded to customer when they purchase this product')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('image')
                    ->circular(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('price')
                    ->money('PHP')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('rating')
                    ->numeric(1)
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('reviews')
                    ->numeric()
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_popular')
                    ->boolean()
                    ->label('Popular')
                ,\Filament\Tables\Columns\TextColumn::make('loyalty_points')
                    ->label('LP')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                \Filament\Tables\Filters\TernaryFilter::make('is_popular')
                    ->label('Popular'),
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
        return [
            RelationManagers\CustomizationRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
