<?php

namespace App\Services;

use App\Repositories\Interfaces\WalletRepositoryInterface;

class WalletService
{
    public function __construct(
        protected WalletRepositoryInterface $walletRepository
    ) {}

    public function getWallet(int $userId)
    {
        $wallet = $this->walletRepository->getByUser($userId);
        $transactions = $this->walletRepository->getTransactions($userId);

        return [
            'wallet' => $wallet,
            'transactions' => $transactions,
        ];
    }

    public function topUp(int $userId, float $amount, string $description = 'Wallet top-up')
    {
        return $this->walletRepository->credit($userId, $amount, $description);
    }

    public function debit(int $userId, float $amount, string $description = 'Payment')
    {
        $wallet = $this->walletRepository->getByUser($userId);

        if (!$wallet || !$wallet->hasSufficientBalance($amount)) {
            throw new \Exception('Insufficient wallet balance.');
        }

        return $this->walletRepository->debit($userId, $amount, $description);
    }
}
