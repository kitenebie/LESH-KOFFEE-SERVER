<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UserVouchersRelationManager extends RelationManager
{
    protected static string $relationship = 'userVouchers';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
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
            ->filters([])
            ->headerActions([
                \Filament\Actions\CreateAction::make(),
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
}
