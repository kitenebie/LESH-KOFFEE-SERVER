<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoResource\Pages;
use App\Models\Promo;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PromoResource extends Resource
{
    protected static ?string $model = Promo::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-megaphone';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Marketing';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Promo Details')
                    ->schema([
                        \Filament\Forms\Components\Select::make('voucher_id')
                            ->label('Linked Voucher')
                            ->relationship('voucher', 'code')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        \Filament\Forms\Components\TextInput::make('heading')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('subheading')
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('badge')
                            ->maxLength(255),
                        \Filament\Forms\Components\ColorPicker::make('color')
                            ->label('Banner Color'),
                        \Filament\Forms\Components\FileUpload::make('image')
                            ->image()
                            ->imageEditor()
                            ->openable()
                            ->downloadable()
                            ->directory('promos')
                            ->columnSpanFull(),
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
                \Filament\Tables\Columns\ImageColumn::make('image')
                    ->height(50),
                \Filament\Tables\Columns\TextColumn::make('heading')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('subheading'),
                \Filament\Tables\Columns\TextColumn::make('badge'),
                \Filament\Tables\Columns\TextColumn::make('voucher.code')
                    ->label('Voucher'),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
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
            'index'  => Pages\ListPromos::route('/'),
            'create' => Pages\CreatePromo::route('/create'),
            'edit'   => Pages\EditPromo::route('/{record}/edit'),
        ];
    }
}
