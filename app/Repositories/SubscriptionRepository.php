<?php

namespace App\Repositories;

use App\Models\Subscription;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function getAll()
    {
        return Subscription::where('is_active', true)->get();
    }

    public function getById(int $id)
    {
        return Subscription::findOrFail($id);
    }
}
