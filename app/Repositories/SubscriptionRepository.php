<?php

namespace App\Repositories;

use App\Models\Subscription;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function getAll()
    {
        // Only return active subscriptions whose offer hasn't expired
        return Subscription::with(['perks.category'])->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->get();
    }

    public function getById(int $id)
    {
        return Subscription::findOrFail($id);
    }
}
