<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-credit-card';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Marketing';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Subscription Plan')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\Textarea::make('description')
                            ->rows(2),
                        \Filament\Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->prefix('₱'),
                        \Filament\Forms\Components\TextInput::make('drinks')
                            ->numeric()
                            ->required()
                            ->label('Drinks Per Week'),
                        \Filament\Forms\Components\TextInput::make('loyalty_points')
                            ->label('Loyalty Points Earned')
                            ->helperText('Points awarded to customer when they buy this subscription')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        \Filament\Forms\Components\TextInput::make('icon')
                            ->maxLength(255)
                            ->helperText('Heroicon name'),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Eligible Items')
                    ->description('Control which products can be redeemed with this subscription')
                    ->schema([
                        \Filament\Forms\Components\Select::make('redemption_type')
                            ->label('Redemption Type')
                            ->options([
                                'all' => 'All Products',
                                'category' => 'By Category',
                                'products' => 'Specific Products',
                            ])
                            ->default('all')
                            ->required()
                            ->live(),
                        \Filament\Forms\Components\Select::make('eligibleCategories')
                            ->label('Eligible Categories')
                            ->relationship('eligibleCategories', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('redemption_type') === 'category'),
                        \Filament\Forms\Components\Select::make('eligibleProducts')
                            ->label('Eligible Products')
                            ->relationship('eligibleProducts', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('redemption_type') === 'products'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->limit(50),
                \Filament\Tables\Columns\TextColumn::make('price')
                    ->money('PHP')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('drinks')
                    ->label('Drinks/Week')
                    ->numeric()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('loyalty_points')
                    ->label('LP')
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                \Filament\Tables\Columns\TextColumn::make('subscribers_count')
                    ->counts('subscribers')
                    ->label('Subscribers'),
            ])
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
            'index'  => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit'   => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
