<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('order_number')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'Queued' => 'Queued',
                        'Preparing' => 'Preparing',
                        'Out For Delivery' => 'Out For Delivery',
                        'Completed' => 'Completed',
                    ]),
                \Filament\Forms\Components\TextInput::make('total')
                    ->numeric()
                    ->prefix('₱'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('order_number')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Queued' => 'gray',
                        'Preparing' => 'warning',
                        'Out For Delivery' => 'info',
                        'Completed' => 'success',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('total')
                    ->money('PHP')
                    ->sortable(),
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
