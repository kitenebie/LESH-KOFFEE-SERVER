<?php

namespace App\Services;

use App\Repositories\Interfaces\LoyaltyRepositoryInterface;

class LoyaltyService
{
    public function __construct(
        protected LoyaltyRepositoryInterface $loyaltyRepository
    ) {}

    public function getTransactions(int $userId)
    {
        return $this->loyaltyRepository->getTransactions($userId);
    }
}
