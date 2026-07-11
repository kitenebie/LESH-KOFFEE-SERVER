<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Filament\Resources\VoucherResource\RelationManagers;
use App\Models\Voucher;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-ticket';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Rewards';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Voucher Details')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        \Filament\Forms\Components\Select::make('type')
                            ->options([
                                'percent' => 'Percentage (%)',
                                'fixed'   => 'Fixed Amount (₱)',
                            ])
                            ->required()
                            ->default('percent'),
                        \Filament\Forms\Components\TextInput::make('discount')
                            ->numeric()
                            ->required()
                            ->helperText('For percent: 0.20 = 20%. For fixed: 300 = ₱300'),
                        \Filament\Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Display name shown to customers'),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Conditions & Limits')
                    ->description('Set requirements for when this voucher can be applied.')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('min_order_amount')
                            ->label('Minimum Order Amount')
                            ->numeric()
                            ->nullable()
                            ->prefix('₱')
                            ->helperText('Minimum order total required to use this voucher (leave empty for no minimum)'),
                        \Filament\Forms\Components\TextInput::make('max_discount')
                            ->label('Maximum Discount Cap')
                            ->numeric()
                            ->nullable()
                            ->prefix('₱')
                            ->helperText('Maximum discount cap for percentage vouchers (leave empty for no cap)'),
                        \Filament\Forms\Components\TextInput::make('valid_hours')
                            ->label('Valid Hours After Claiming')
                            ->numeric()
                            ->nullable()
                            ->suffix('hours')
                            ->helperText('How many hours the voucher is valid after a user claims it. Leave empty for 30-day default.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                \Filament\Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'info'    => 'percent',
                        'success' => 'fixed',
                    ]),
                \Filament\Tables\Columns\TextColumn::make('discount')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->type === 'percent'
                        ? (($record->discount * 100) . '%')
                        : ('₱' . number_format($record->discount, 2))
                    ),
                \Filament\Tables\Columns\TextColumn::make('label')
                    ->limit(30),
                \Filament\Tables\Columns\TextColumn::make('min_order_amount')
                    ->label('Min Order')
                    ->money('PHP')
                    ->placeholder('None')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('valid_hours')
                    ->label('Validity')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}h" : '30 days')
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                \Filament\Tables\Columns\TextColumn::make('userVouchers_count')
                    ->counts('userVouchers')
                    ->label('Claimed'),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percent' => 'Percentage',
                        'fixed'   => 'Fixed',
                    ]),
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
            'index'  => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'edit'   => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}
