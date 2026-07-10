<?php

namespace App\Filament\Resources\TopupLoyaltyTierResource\Pages;

use App\Filament\Resources\TopupLoyaltyTierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTopupLoyaltyTiers extends ListRecords
{
    protected static string $resource = TopupLoyaltyTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
