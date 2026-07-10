<?php

namespace App\Filament\Resources\UserVoucherResource\Pages;

use App\Filament\Resources\UserVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserVoucher extends EditRecord
{
    protected static string $resource = UserVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
