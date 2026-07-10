<?php

namespace App\Filament\Resources\UserVoucherResource\Pages;

use App\Filament\Resources\UserVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserVouchers extends ListRecords
{
    protected static string $resource = UserVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
