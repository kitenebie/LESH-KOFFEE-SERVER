<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CartItemResource\Pages;
use App\Models\CartItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CartItemResource extends Resource
{
    protected static ?string $model = CartItem::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-shopping-bag';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Orders';
    }

    public static function getNavigationLabel(): string
    {
        return 'User Carts';
    }

    public static function getModelLabel(): string
    {
        return 'Cart Item';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Cart Item')
                    ->schema([
                        \Filament\Forms\Components\Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        \Filament\Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1),
                        \Filament\Forms\Components\TextInput::make('unit_price')
                            ->numeric()
                            ->prefix('₱')
                            ->disabled()
                            ->label('Unit Price (auto-calculated)'),
                        \Filament\Forms\Components\KeyValue::make('customization.selections')
                            ->label('Customization Selections')
                            ->nullable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('quantity')
                    ->sortable()
                    ->alignCenter(),
                \Filament\Tables\Columns\TextColumn::make('unit_price')
                    ->money('PHP')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('line_total')
                    ->label('Line Total')
                    ->money('PHP')
                    ->state(fn ($record) => $record->unit_price * $record->quantity),
                \Filament\Tables\Columns\TextColumn::make('customization')
                    ->label('Customization')
                    ->formatStateUsing(function ($state) {
                        if (empty($state) || !is_array($state)) return '—';
                        $selections = $state['selections'] ?? $state;
                        if (!is_array($selections)) return '—';
                        $parts = [];
                        foreach ($selections as $key => $values) {
                            if (is_array($values) && count($values) > 0) {
                                $parts[] = ucfirst($key) . ': ' . implode(', ', $values);
                            }
                        }
                        return implode(' • ', $parts) ?: '—';
                    })
                    ->wrap(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime('M d, h:i A')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('user_id')
                    ->label('Customer')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('created_at', 'desc')
            ->groups([
                \Filament\Tables\Grouping\Group::make('user.name')
                    ->label('Customer')
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCartItems::route('/'),
        ];
    }
}
