<?php

namespace App\Repositories;

use App\Models\StampAchievement;
use App\Models\StampHistory;
use App\Repositories\Interfaces\StampRepositoryInterface;

class StampRepository implements StampRepositoryInterface
{
    public function getAchievements(int $userId)
    {
        return StampAchievement::with(['histories.product'])
            ->where('user_id', $userId)
            ->get();
    }

    public function getHistory(int $achievementId)
    {
        return StampHistory::with('product')
            ->where('stamp_achievement_id', $achievementId)
            ->orderBy('stamped_date', 'desc')
            ->get();
    }
}
