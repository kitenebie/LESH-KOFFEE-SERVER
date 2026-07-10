<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\Textarea::make('address')
                    ->required()
                    ->maxLength(500),
                \Filament\Forms\Components\TextInput::make('latitude')
                    ->numeric(),
                \Filament\Forms\Components\TextInput::make('longitude')
                    ->numeric(),
                \Filament\Forms\Components\Toggle::make('is_default'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('label')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('address')
                    ->limit(50),
                \Filament\Tables\Columns\IconColumn::make('is_default')
                    ->boolean(),
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
