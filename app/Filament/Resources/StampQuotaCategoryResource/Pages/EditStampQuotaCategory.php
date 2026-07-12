<?php

namespace App\Filament\Resources\StampQuotaCategoryResource\Pages;

use App\Filament\Resources\StampQuotaCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditStampQuotaCategory extends EditRecord
{
    protected static string $resource = StampQuotaCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
