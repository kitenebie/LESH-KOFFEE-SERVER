<?php

namespace App\Filament\Resources\LeshWalletResource\Pages;

use App\Filament\Resources\LeshWalletResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeshWallet extends EditRecord
{
    protected static string $resource = LeshWalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
