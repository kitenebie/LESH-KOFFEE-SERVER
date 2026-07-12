<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'User Management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Personal Information')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('first_name')
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn (string $state): string => bcrypt($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        \Filament\Forms\Components\TextInput::make('avatar')
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Membership & Rewards')
                    ->schema([
                        \Filament\Forms\Components\Select::make('member_level')
                            ->options([
                                'Bronze'   => 'Bronze',
                                'Silver'   => 'Silver',
                                'Gold'     => 'Gold',
                                'Platinum' => 'Platinum',
                                'Diamond'  => 'Diamond',
                            ])
                            ->default('Bronze')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (\Filament\Schemas\Components\Utilities\Set $set, ?string $state) {
                                $labels = [
                                    'Bronze'   => 'Lesh Kaffe Bronze Member',
                                    'Silver'   => 'Lesh Kaffe Silver Member',
                                    'Gold'     => 'Lesh Kaffe Gold Member',
                                    'Platinum' => 'Lesh Kaffe Platinum Member',
                                    'Diamond'  => 'Lesh Kaffe Diamond Member',
                                ];
                                $set('member_level_label', $labels[$state] ?? '');
                            }),
                        \Filament\Forms\Components\TextInput::make('member_level_label')
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('loyalty_points')
                            ->numeric()
                            ->default(0),
                        \Filament\Forms\Components\TextInput::make('stamps_collected')
                            ->numeric()
                            ->default(0),
                        \Filament\Forms\Components\TextInput::make('stamps_required')
                            ->numeric()
                            ->default(8),
                        \Filament\Forms\Components\Select::make('active_subscription_id')
                            ->label('Active Subscription')
                            ->relationship('activeSubscription', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Location')
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('joined_date'),
                        \Filament\Forms\Components\TextInput::make('latitude')
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('longitude')
                            ->numeric(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('avatar')
                    ->circular(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                \Filament\Tables\Columns\BadgeColumn::make('member_level')
                    ->colors([
                        'warning' => 'Bronze',
                        'gray'    => 'Silver',
                        'success' => 'Gold',
                        'info'    => 'Platinum',
                        'primary' => 'Diamond',
                    ]),
                \Filament\Tables\Columns\TextColumn::make('loyalty_points')
                    ->numeric()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('joined_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('member_level')
                    ->options([
                        'Bronze'   => 'Bronze',
                        'Silver'   => 'Silver',
                        'Gold'     => 'Gold',
                        'Platinum' => 'Platinum',
                        'Diamond'  => 'Diamond',
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
        return [
            RelationManagers\MembershipCardRelationManager::class,
            RelationManagers\AddressesRelationManager::class,
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\LoyaltyTransactionsRelationManager::class,
            RelationManagers\StampAchievementsRelationManager::class,
            RelationManagers\UserVouchersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
