<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletTransactionResource\Pages;
use App\Models\WalletTransaction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class WalletTransactionResource extends Resource
{
    protected static ?string $model = WalletTransaction::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-arrow-path-rounded-square';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Transaction Details')
                    ->schema([
                        \Filament\Forms\Components\Select::make('wallet_id')
                            ->label('Wallet')
                            ->relationship('wallet', 'id')
                            ->required()
                            ->searchable(),
                        \Filament\Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\Select::make('type')
                            ->options([
                                'credit' => 'Credit (Top-up)',
                                'debit'  => 'Debit (Payment)',
                            ])
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->prefix('₱'),
                        \Filament\Forms\Components\TextInput::make('description')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\DatePicker::make('transaction_date')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'credit',
                        'danger'  => 'debit',
                    ]),
                \Filament\Tables\Columns\TextColumn::make('amount')
                    ->money('PHP')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->limit(40),
                \Filament\Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'credit' => 'Credit',
                        'debit'  => 'Debit',
                    ]),
            ])
            ->defaultSort('transaction_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWalletTransactions::route('/')
        ];
    }
}
