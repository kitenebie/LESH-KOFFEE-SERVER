<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->prefix('₱'),
                \Filament\Forms\Components\Toggle::make('is_popular')
                    ->label('Popular'),
                \Filament\Forms\Components\Toggle::make('is_customizable')
                    ->label('Customizable'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('image')
                    ->circular(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('price')
                    ->money('PHP')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('rating')
                    ->numeric(1),
                \Filament\Tables\Columns\IconColumn::make('is_popular')
                    ->boolean()
                    ->label('Popular'),
            ])
            ->filters([])
            ->headerActions([
                \Filament\Actions\CreateAction::make(),
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
}
