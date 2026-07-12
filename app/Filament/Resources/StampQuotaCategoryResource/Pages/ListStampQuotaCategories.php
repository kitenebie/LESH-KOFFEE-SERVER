<?php

namespace App\Filament\Resources\StampQuotaCategoryResource\Pages;

use App\Filament\Resources\StampQuotaCategoryResource;
use Filament\Resources\Pages\ListRecords;

class ListStampQuotaCategories extends ListRecords
{
    protected static string $resource = StampQuotaCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
