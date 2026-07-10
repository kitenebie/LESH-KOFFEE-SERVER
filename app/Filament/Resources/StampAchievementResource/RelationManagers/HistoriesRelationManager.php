<?php

namespace App\Filament\Resources\StampAchievementResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class HistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                \Filament\Forms\Components\TextInput::make('product_name')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\DatePicker::make('stamped_date')
                    ->required(),
                \Filament\Forms\Components\TextInput::make('stamped_time')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('product_name')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('stamped_date')
                    ->date()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('stamped_time'),
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
            ])
            ->defaultSort('stamped_date', 'desc');
    }
}
