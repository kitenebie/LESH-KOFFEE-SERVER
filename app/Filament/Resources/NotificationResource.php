<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Notification;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-bell';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'User Management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Notification')
                    ->schema([
                        \Filament\Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\Select::make('type')
                            ->options([
                                'order'   => 'Order',
                                'promo'   => 'Promo',
                                'loyalty' => 'Loyalty',
                                'wallet'  => 'Wallet',
                                'system'  => 'System',
                            ])
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('icon')
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Textarea::make('message')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Toggle::make('is_unread')
                            ->label('Unread')
                            ->default(true),
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
                \Filament\Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'warning' => 'order',
                        'info'    => 'promo',
                        'success' => 'loyalty',
                        'primary' => 'wallet',
                        'gray'    => 'system',
                    ]),
                \Filament\Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                \Filament\Tables\Columns\TextColumn::make('message')
                    ->limit(60),
                \Filament\Tables\Columns\IconColumn::make('is_unread')
                    ->boolean()
                    ->label('Unread'),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'order'   => 'Order',
                        'promo'   => 'Promo',
                        'loyalty' => 'Loyalty',
                        'wallet'  => 'Wallet',
                        'system'  => 'System',
                    ]),
                \Filament\Tables\Filters\TernaryFilter::make('is_unread')
                    ->label('Unread'),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit'   => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
