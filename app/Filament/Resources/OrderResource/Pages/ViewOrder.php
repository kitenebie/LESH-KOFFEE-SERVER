<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\RepeatableEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Actions;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ─── Order Header ────────────────────────────────────────
                Section::make('Order Details')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('order_number')
                                ->label('Order #')
                                ->weight('bold')
                                ->size('lg')
                                ->copyable(),
                            TextEntry::make('date')
                                ->label('Date')
                                ->date('M d, Y'),
                            TextEntry::make('time')
                                ->label('Time'),
                        ]),
                        Grid::make(3)->schema([
                            TextEntry::make('status')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'Queued' => 'warning',
                                    'Preparing' => 'primary',
                                    'Ready' => 'info',
                                    'Out For Delivery' => 'info',
                                    'Completed' => 'success',
                                    'Cancelled' => 'danger',
                                    default => 'gray',
                                }),
                            TextEntry::make('fulfillment')
                                ->label('Type')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'DineIn' => 'gray',
                                    'Delivery' => 'info',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn (string $state): string => match ($state) {
                                    'DineIn' => 'Dine In',
                                    'Delivery' => 'Delivery',
                                    default => $state,
                                }),
                            TextEntry::make('current_step')
                                ->label('Current Step')
                                ->formatStateUsing(fn (?string $state): string => ucfirst($state ?? 'N/A')),
                        ]),
                    ]),

                // ─── Customer Info ────────────────────────────────────────
                Section::make('Customer')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('user.name')
                                ->label('Name')
                                ->weight('bold'),
                            TextEntry::make('user.email')
                                ->label('Email')
                                ->copyable(),
                            TextEntry::make('user.phone')
                                ->label('Phone')
                                ->copyable(),
                        ]),
                    ]),

                // ─── Order Items ─────────────────────────────────────────
                Section::make('Order Items')
                    ->icon('heroicon-o-queue-list')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                Grid::make(5)->schema([
                                    TextEntry::make('name')
                                        ->label('Product')
                                        ->weight('bold')
                                        ->columnSpan(2),
                                    TextEntry::make('quantity')
                                        ->label('Qty')
                                        ->alignCenter(),
                                    TextEntry::make('price')
                                        ->label('Unit Price')
                                        ->money('PHP')
                                        ->alignEnd(),
                                    TextEntry::make('line_total')
                                        ->label('Subtotal')
                                        ->money('PHP')
                                        ->alignEnd()
                                        ->state(function ($record) {
                                            return $record->price * $record->quantity;
                                        }),
                                ]),
                                TextEntry::make('customization')
                                    ->label('Customization')
                                    ->formatStateUsing(function ($state) {
                                        if (empty($state) || !is_array($state)) return null;
                                        $parts = [];
                                        foreach ($state as $key => $values) {
                                            if (is_array($values) && count($values) > 0) {
                                                $label = ucfirst($key);
                                                $parts[] = "$label: " . implode(', ', $values);
                                            }
                                        }
                                        return implode(' • ', $parts) ?: null;
                                    })
                                    ->placeholder('No customization')
                                    ->color('primary')
                                    ->icon('heroicon-o-adjustments-horizontal'),
                            ])
                            ->columns(1),
                    ]),

                // ─── Financials ──────────────────────────────────────────
                Section::make('Payment Summary')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('subtotal')
                                ->label('Subtotal')
                                ->money('PHP'),
                            TextEntry::make('delivery_fee')
                                ->label('Delivery Fee')
                                ->money('PHP'),
                            TextEntry::make('discount')
                                ->label('Discount')
                                ->money('PHP')
                                ->color('danger'),
                            TextEntry::make('total')
                                ->label('Total')
                                ->money('PHP')
                                ->weight('bold')
                                ->size('lg')
                                ->color('success'),
                        ]),
                        Grid::make(3)->schema([
                            TextEntry::make('payment_method')
                                ->label('Payment Method')
                                ->formatStateUsing(fn (?string $state): string => ucfirst($state ?? 'N/A'))
                                ->icon('heroicon-o-credit-card'),
                            TextEntry::make('paid_at')
                                ->label('Paid At')
                                ->dateTime('M d, Y h:i A')
                                ->placeholder('Not yet paid'),
                            TextEntry::make('ref_no')
                                ->label('Reference #')
                                ->placeholder('—')
                                ->copyable(),
                        ]),
                    ]),

                // ─── Metadata ────────────────────────────────────────────
                Section::make('Metadata')
                    ->icon('heroicon-o-clock')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('cashier')
                                ->placeholder('—'),
                            TextEntry::make('created_at')
                                ->label('Created')
                                ->dateTime('M d, Y h:i A'),
                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->dateTime('M d, Y h:i A'),
                        ]),
                    ]),
            ]);
    }
}
