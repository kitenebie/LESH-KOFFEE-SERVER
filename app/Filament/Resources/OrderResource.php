<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-shopping-cart';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Orders';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Order Info')
                    ->schema([
                        \Filament\Forms\Components\Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\TextInput::make('order_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        \Filament\Forms\Components\DatePicker::make('date')
                            ->required(),
                        Forms\Components\TimePicker::make('time')
                            ->required(),
                        \Filament\Forms\Components\Select::make('fulfillment')
                            ->options([
                                'DineIn'   => 'Dine In',
                                'Delivery' => 'Delivery',
                            ])
                            ->required()
                            ->live(),
                        \Filament\Forms\Components\TextInput::make('ref_no')
                            ->label('Reference No.')
                            ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('fulfillment') === 'DineIn'),
                        \Filament\Forms\Components\TextInput::make('req_id')
                            ->label('Request ID')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('cashier'),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Status')
                    ->schema([
                        \Filament\Forms\Components\Select::make('status')
                            ->options([
                                'Queued'           => 'Queued',
                                'Preparing'        => 'Preparing',
                                'Ready'            => 'Ready',
                                'Out For Delivery' => 'Out For Delivery',
                                'Completed'        => 'Completed',
                                'Cancelled'        => 'Cancelled',
                            ])
                            ->required(),
                        \Filament\Forms\Components\Select::make('current_step')
                            ->options([
                                'queue'    => 'Queue',
                                'preparing'=> 'Preparing',
                                'ready'    => 'Ready',
                                'delivery' => 'Delivery',
                                'received' => 'Received',
                                'rate'     => 'Rate',
                            ])
                            ->required(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Financials')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->required()
                            ->prefix('₱'),
                        \Filament\Forms\Components\TextInput::make('delivery_fee')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱'),
                        \Filament\Forms\Components\TextInput::make('discount')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱'),
                        \Filament\Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->required()
                            ->prefix('₱'),
                        \Filament\Forms\Components\TextInput::make('subscription_discount')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱')
                            ->label('Subscription Disc.'),
                        \Filament\Forms\Components\TextInput::make('voucher_discount')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱')
                            ->label('Voucher Disc.'),
                        \Filament\Forms\Components\TextInput::make('perk_discount')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱')
                            ->label('Perk Disc.'),
                        \Filament\Forms\Components\TextInput::make('voucher_codes')
                            ->label('Voucher Codes'),
                        \Filament\Forms\Components\TextInput::make('subscription_items_used')
                            ->numeric()
                            ->default(0)
                            ->label('Sub Items Used'),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Order Items')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                \Filament\Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload(),
                                \Filament\Forms\Components\TextInput::make('name')
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1),
                                \Filament\Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('₱'),
                            ])
                            ->columns(4)
                            ->collapsible(),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                \Filament\Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning'  => 'Queued',
                        'primary'  => 'Preparing',
                        'info'     => 'Ready',
                        'success'  => 'Completed',
                        'danger'   => 'Cancelled',
                    ]),
                \Filament\Tables\Columns\BadgeColumn::make('fulfillment')
                    ->colors([
                        'gray'    => 'DineIn',
                        'info'    => 'Delivery',
                    ]),
                \Filament\Tables\Columns\TextColumn::make('total')
                    ->money('PHP')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Queued'           => 'Queued',
                        'Preparing'        => 'Preparing',
                        'Ready'            => 'Ready',
                        'Out For Delivery' => 'Out For Delivery',
                        'Completed'        => 'Completed',
                        'Cancelled'        => 'Cancelled',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('fulfillment')
                    ->options([
                        'DineIn'   => 'Dine In',
                        'Delivery' => 'Delivery',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view'   => Pages\ViewOrder::route('/{record}'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
