<?php

namespace App\Repositories\Interfaces;

interface WalletRepositoryInterface
{
    public function getByUser(int $userId);

    public function getTransactions(int $userId);

    public function credit(int $userId, float $amount, string $description);

    public function debit(int $userId, float $amount, string $description);
}
