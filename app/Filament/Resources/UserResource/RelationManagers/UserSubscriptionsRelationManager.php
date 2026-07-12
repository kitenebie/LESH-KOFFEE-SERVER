<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UserSubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'userSubscriptions';

    protected static ?string $title = 'Subscriptions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('subscription_id')
                    ->label('Subscription Plan')
                    ->relationship('subscription', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                \Filament\Forms\Components\DateTimePicker::make('starts_at')
                    ->required()
                    ->default(now()),
                \Filament\Forms\Components\DateTimePicker::make('expires_at')
                    ->required()
                    ->default(now()->addDays(30)),
                \Filament\Forms\Components\TextInput::make('drinks_remaining')
                    ->numeric()
                    ->required()
                    ->default(0),
                \Filament\Forms\Components\TextInput::make('drinks_used')
                    ->numeric()
                    ->default(0),
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'active'    => 'Active',
                        'expired'   => 'Expired',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('subscription.name')
                    ->label('Plan')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger'  => 'expired',
                        'gray'    => 'completed',
                        'warning' => 'cancelled',
                    ]),
                \Filament\Tables\Columns\TextColumn::make('drinks_remaining')
                    ->label('Remaining')
                    ->numeric(),
                \Filament\Tables\Columns\TextColumn::make('drinks_used')
                    ->label('Used')
                    ->numeric(),
                \Filament\Tables\Columns\TextColumn::make('starts_at')
                    ->label('Started')
                    ->dateTime('M d, Y')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active'    => 'Active',
                        'expired'   => 'Expired',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
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
