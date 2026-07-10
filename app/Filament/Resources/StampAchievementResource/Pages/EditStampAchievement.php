<?php

namespace App\Filament\Resources\StampAchievementResource\Pages;

use App\Filament\Resources\StampAchievementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStampAchievement extends EditRecord
{
    protected static string $resource = StampAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
