<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TopupLoyaltyTierResource\Pages;
use App\Models\TopupLoyaltyTier;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TopupLoyaltyTierResource extends Resource
{
    protected static ?string $model = TopupLoyaltyTier::class;

    protected static ?string $navigationLabel = 'Top-Up Loyalty Tiers';

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-gift';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Loyalty';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Top-Up Loyalty Tier')
                    ->description('Configure how many loyalty points a customer earns when they top-up their Lesh Wallet.')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Bronze Tier, Silver Tier')
                            ->helperText('Display name for this tier'),
                        \Filament\Forms\Components\TextInput::make('min_amount')
                            ->label('Minimum Amount (₱)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('₱')
                            ->helperText('Minimum top-up amount for this tier (inclusive)'),
                        \Filament\Forms\Components\TextInput::make('max_amount')
                            ->label('Maximum Amount (₱)')
                            ->numeric()
                            ->nullable()
                            ->prefix('₱')
                            ->helperText('Maximum top-up amount (inclusive). Leave empty for unlimited.'),
                        \Filament\Forms\Components\TextInput::make('loyalty_points')
                            ->label('Loyalty Points Awarded')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->helperText('How many loyalty points the customer earns'),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('min_amount')
                    ->label('Min (₱)')
                    ->money('PHP')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('max_amount')
                    ->label('Max (₱)')
                    ->money('PHP')
                    ->placeholder('Unlimited')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('loyalty_points')
                    ->label('Points')
                    ->numeric()
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->defaultSort('min_amount', 'asc')
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTopupLoyaltyTiers::route('/'),
            'create' => Pages\CreateTopupLoyaltyTier::route('/create'),
            'edit'   => Pages\EditTopupLoyaltyTier::route('/{record}/edit'),
        ];
    }
}
