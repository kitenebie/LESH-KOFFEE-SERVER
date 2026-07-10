<?php

namespace App\Repositories;

use App\Models\LeshWallet;
use App\Models\WalletTransaction;
use App\Repositories\Interfaces\WalletRepositoryInterface;

class WalletRepository implements WalletRepositoryInterface
{
    public function getByUser(int $userId)
    {
        return LeshWallet::with('transactions')
            ->where('user_id', $userId)
            ->first();
    }

    public function getTransactions(int $userId)
    {
        return WalletTransaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function credit(int $userId, float $amount, string $description)
    {
        $wallet = LeshWallet::where('user_id', $userId)->firstOrFail();

        return $wallet->credit($amount, $description);
    }

    public function debit(int $userId, float $amount, string $description)
    {
        $wallet = LeshWallet::where('user_id', $userId)->firstOrFail();

        return $wallet->debit($amount, $description);
    }
}
