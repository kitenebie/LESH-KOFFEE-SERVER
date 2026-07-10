<?php

namespace App\Filament\Resources\TopupLoyaltyTierResource\Pages;

use App\Filament\Resources\TopupLoyaltyTierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTopupLoyaltyTier extends EditRecord
{
    protected static string $resource = TopupLoyaltyTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
