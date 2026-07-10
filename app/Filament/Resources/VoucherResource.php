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
                            ->helperText('e.g. 0.2 for 20% or 50 for ₱50 fixed'),
                        \Filament\Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255),
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
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('label')
                    ->limit(40),
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
