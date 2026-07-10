<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoyaltyTransactionResource\Pages;
use App\Models\LoyaltyTransaction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LoyaltyTransactionResource extends Resource
{
    protected static ?string $model = LoyaltyTransaction::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-trophy';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Loyalty Transaction')
                    ->schema([
                        \Filament\Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
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
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'earned'   => 'Earned',
                        'redeemed' => 'Redeemed',
                        'bonus'    => 'Bonus',
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
            'index'  => Pages\ListLoyaltyTransactions::route('/')
        ];
    }
}
