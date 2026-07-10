<?php

namespace App\Filament\Resources\StampAchievementResource\Pages;

use App\Filament\Resources\StampAchievementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStampAchievements extends ListRecords
{
    protected static string $resource = StampAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
