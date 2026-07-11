<?php

namespace App\Observers;

use App\Models\LeshPoints;
use App\Models\LoyaltyTransaction;

class LoyaltyTransactionObserver
{
    /**
     * After a transaction is created, update the user's points balance.
     */
    public function created(LoyaltyTransaction $transaction): void
    {
        $this->syncBalance($transaction);
    }

    /**
     * After a transaction is updated (e.g. points or type changed), recalculate balance.
     */
    public function updated(LoyaltyTransaction $transaction): void
    {
        $this->syncBalance($transaction);
    }

    /**
     * After a transaction is deleted, recalculate balance.
     */
    public function deleted(LoyaltyTransaction $transaction): void
    {
        $this->syncBalance($transaction);
    }

    /**
     * Recalculate the user's LeshPoints balance from all their transactions.
     */
    private function syncBalance(LoyaltyTransaction $transaction): void
    {
        $userId = $transaction->user_id;
        if (!$userId) return;

        $leshPoints = LeshPoints::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'is_active' => true]
        );

        // Link transaction to LeshPoints record if not already linked
        if (empty($transaction->lesh_points_id)) {
            $transaction->updateQuietly(['lesh_points_id' => $leshPoints->id]);
        }

        $leshPoints->recalculateBalance();
    }
}
