<?php

namespace App\Repositories;

use App\Models\LoyaltyTransaction;
use App\Repositories\Interfaces\LoyaltyRepositoryInterface;

class LoyaltyRepository implements LoyaltyRepositoryInterface
{
    public function getTransactions(int $userId)
    {
        return LoyaltyTransaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
