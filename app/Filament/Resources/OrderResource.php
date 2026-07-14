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
                            ->label('Request ID'),
                        \Filament\Forms\Components\TextInput::make('cashier'),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Status')
                    ->schema([
                        \Filament\Forms\Components\Select::make('status')
                            ->options([
                                'Queued'           => 'Queued',
                                'Paid'             => 'Paid',
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

                \Filament\Schemas\Components\Section::make('Payment')
                    ->schema([
                        \Filament\Forms\Components\Select::make('payment_method')
                            ->options([
                                'COD'         => 'Cash / COD',
                                'wallet'      => 'Lesh Wallet',
                                'CardEWallet' => 'Card / E-wallet',
                                'subscription'=> 'Subscription',
                            ]),
                        \Filament\Forms\Components\TextInput::make('ref_code')
                            ->label('Payment Ref Code'),
                        \Filament\Forms\Components\TextInput::make('signature')
                            ->label('Payment Signature'),
                        \Filament\Forms\Components\TextInput::make('amount_paid')
                            ->numeric()
                            ->prefix('₱')
                            ->label('Amount Paid'),
                        \Filament\Forms\Components\TextInput::make('payment_fee')
                            ->numeric()
                            ->prefix('₱')
                            ->label('Payment Fee'),
                        \Filament\Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Paid At'),
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
                            ->prefix('₱')
                            ->label('Total Discount'),
                        \Filament\Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->required()
                            ->prefix('₱'),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Discount Breakdown')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('subscription_discount')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱')
                            ->label('Subscription Discount'),
                        \Filament\Forms\Components\TextInput::make('voucher_discount')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱')
                            ->label('Voucher Discount'),
                        \Filament\Forms\Components\TextInput::make('perk_discount')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱')
                            ->label('Perk Discount'),
                        \Filament\Forms\Components\TextInput::make('voucher_codes')
                            ->label('Voucher Codes Applied'),
                        \Filament\Forms\Components\Select::make('subscription_id')
                            ->label('Subscription Plan')
                            ->relationship('subscription', 'name')
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\TextInput::make('subscription_items_used')
                            ->numeric()
                            ->default(0)
                            ->label('Subscription Items Used'),
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
                    ->sortable()
                    ->copyable(),
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('time')
                    ->label('Time'),
                \Filament\Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning'  => 'Queued',
                        'success'  => 'Paid',
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
                \Filament\Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'wallet' => 'success',
                        'CardEWallet' => 'info',
                        'COD' => 'warning',
                        'subscription' => 'primary',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('subtotal')
                    ->money('PHP')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('delivery_fee')
                    ->money('PHP')
                    ->label('Delivery')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('subscription_discount')
                    ->money('PHP')
                    ->label('Sub Disc.')
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('voucher_discount')
                    ->money('PHP')
                    ->label('Voucher Disc.')
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('perk_discount')
                    ->money('PHP')
                    ->label('Perk Disc.')
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('discount')
                    ->money('PHP')
                    ->label('Total Disc.')
                    ->color('success'),
                \Filament\Tables\Columns\TextColumn::make('total')
                    ->money('PHP')
                    ->sortable()
                    ->weight('bold'),
                \Filament\Tables\Columns\TextColumn::make('voucher_codes')
                    ->label('Vouchers')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('subscription_items_used')
                    ->label('Sub Items')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('ref_code')
                    ->label('Pay Ref')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('amount_paid')
                    ->money('PHP')
                    ->label('Amt Paid')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->label('Paid At')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('cashier')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Queued'           => 'Queued',
                        'Paid'             => 'Paid',
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
                \Filament\Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'COD'         => 'Cash / COD',
                        'wallet'      => 'Lesh Wallet',
                        'CardEWallet' => 'Card / E-wallet',
                        'subscription'=> 'Subscription',
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
