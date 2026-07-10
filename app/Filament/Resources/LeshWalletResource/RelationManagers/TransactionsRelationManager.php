<?php

namespace App\Filament\Resources\LeshWalletResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('type')
                    ->options([
                        'credit' => 'Credit (Top-up)',
                        'debit'  => 'Debit (Payment)',
                    ])
                    ->required(),
                \Filament\Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('₱'),
                \Filament\Forms\Components\TextInput::make('description')
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
                        'success' => 'credit',
                        'danger'  => 'debit',
                    ]),
                \Filament\Tables\Columns\TextColumn::make('amount')
                    ->money('PHP')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->limit(50),
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
