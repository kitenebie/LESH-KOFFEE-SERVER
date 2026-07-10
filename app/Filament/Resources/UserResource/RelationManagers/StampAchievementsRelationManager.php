<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StampAchievementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stampAchievements';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('category')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('collected')
                    ->numeric()
                    ->default(0),
                \Filament\Forms\Components\TextInput::make('required')
                    ->numeric()
                    ->default(8),
                \Filament\Forms\Components\TextInput::make('reward')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\BadgeColumn::make('category'),
                \Filament\Tables\Columns\TextColumn::make('label'),
                \Filament\Tables\Columns\TextColumn::make('collected')
                    ->numeric(),
                \Filament\Tables\Columns\TextColumn::make('required')
                    ->numeric(),
                \Filament\Tables\Columns\TextColumn::make('reward')
                    ->limit(40),
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
