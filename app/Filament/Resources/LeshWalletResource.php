<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeshWalletResource\Pages;
use App\Filament\Resources\LeshWalletResource\RelationManagers;
use App\Models\LeshWallet;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LeshWalletResource extends Resource
{
    protected static ?string $model = LeshWallet::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-wallet';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('balance')
                    ->money('PHP')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('currency'),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                \Filament\Tables\Columns\TextColumn::make('transactions_count')
                    ->counts('transactions')
                    ->label('Transactions'),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLeshWallets::route('/')
        ];
    }
}
