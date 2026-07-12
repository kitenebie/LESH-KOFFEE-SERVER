<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MembershipCardRelationManager extends RelationManager
{
    protected static string $relationship = 'membershipCard';

    protected static ?string $title = 'Membership Card';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('card_tier')
                    ->options([
                        'Bronze'   => 'Bronze',
                        'Silver'   => 'Silver',
                        'Gold'     => 'Gold',
                        'Platinum' => 'Platinum',
                        'Diamond'  => 'Diamond',
                    ])
                    ->default('Bronze')
                    ->required(),
                \Filament\Forms\Components\TextInput::make('card_number')
                    ->label('Card Number')
                    ->placeholder('XXXX-XXXX-XXXX-XXXX')
                    ->maxLength(19)
                    ->disabled()
                    ->dehydrated(false),
                \Filament\Forms\Components\TextInput::make('card_exp')
                    ->label('Expiry (MM/YY)')
                    ->maxLength(5)
                    ->disabled()
                    ->dehydrated(false),
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\BadgeColumn::make('card_tier')
                    ->colors([
                        'warning' => 'Bronze',
                        'gray'    => 'Silver',
                        'success' => 'Gold',
                        'info'    => 'Platinum',
                        'primary' => 'Diamond',
                    ]),
                \Filament\Tables\Columns\TextColumn::make('card_number')
                    ->label('Card Number')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('card_exp')
                    ->label('Expires'),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Create Card')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['card_number'] = \App\Models\MembershipCard::generateCardNumber();
                        $data['card_exp'] = now()->addYears(5)->format('m/y');
                        $data['card_cvv'] = bcrypt(str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT));
                        return $data;
                    }),
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
