<?php

namespace App\Repositories\Interfaces;

interface StampRepositoryInterface
{
    public function getAchievements(int $userId);

    public function getHistory(int $achievementId);
}
