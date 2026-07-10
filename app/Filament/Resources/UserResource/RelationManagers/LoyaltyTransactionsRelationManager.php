<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LoyaltyTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'loyaltyTransactions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('type')
                    ->options([
                        'earned'   => 'Earned',
                        'redeemed' => 'Redeemed',
                        'bonus'    => 'Bonus',
                    ])
                    ->required(),
                \Filament\Forms\Components\TextInput::make('points')
                    ->numeric()
                    ->required(),
                \Filament\Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\DatePicker::make('transaction_date')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'earned',
                        'danger'  => 'redeemed',
                        'warning' => 'bonus',
                    ]),
                \Filament\Tables\Columns\TextColumn::make('points')
                    ->numeric()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->limit(40),
                \Filament\Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
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
            ])
            ->defaultSort('transaction_date', 'desc');
    }
}
