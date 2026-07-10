<?php

namespace App\Repositories\Interfaces;

interface LoyaltyRepositoryInterface
{
    public function getTransactions(int $userId);
}
