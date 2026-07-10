<?php

namespace App\Services;

use App\Repositories\Interfaces\StampRepositoryInterface;

class StampService
{
    public function __construct(
        protected StampRepositoryInterface $stampRepository
    ) {}

    public function getAchievements(int $userId)
    {
        return $this->stampRepository->getAchievements($userId);
    }

    public function getHistory(int $achievementId)
    {
        return $this->stampRepository->getHistory($achievementId);
    }
}
