<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserVoucherResource\Pages;
use App\Models\UserVoucher;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UserVoucherResource extends Resource
{
    protected static ?string $model = UserVoucher::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-gift';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Rewards';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('User Voucher')
                    ->schema([
                        \Filament\Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\Select::make('voucher_id')
                            ->label('Voucher')
                            ->relationship('voucher', 'code')
                            ->required()
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('description')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\DatePicker::make('expires_at'),
                        \Filament\Forms\Components\Toggle::make('is_used')
                            ->label('Used')
                            ->default(false),
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
                \Filament\Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->copyable(),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->limit(40),
                \Filament\Tables\Columns\TextColumn::make('expires_at')
                    ->date()
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_used')
                    ->boolean()
                    ->label('Used'),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_used')
                    ->label('Used'),
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
            'index'  => Pages\ListUserVouchers::route('/'),
            'create' => Pages\CreateUserVoucher::route('/create'),
            'edit'   => Pages\EditUserVoucher::route('/{record}/edit'),
        ];
    }
}
