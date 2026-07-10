<?php

namespace App\Services;

use App\Repositories\Interfaces\SubscriptionRepositoryInterface;

class SubscriptionService
{
    public function __construct(
        protected SubscriptionRepositoryInterface $subscriptionRepository
    ) {}

    public function getAllSubscriptions()
    {
        return $this->subscriptionRepository->getAll();
    }

    public function getSubscription(int $id)
    {
        return $this->subscriptionRepository->getById($id);
    }
}
