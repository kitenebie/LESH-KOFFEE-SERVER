<?php

namespace App\Repositories;

use App\Models\StampAchievement;
use App\Models\StampHistory;
use App\Models\Category;
use App\Repositories\Interfaces\StampRepositoryInterface;

class StampRepository implements StampRepositoryInterface
{
    public function getAchievements(int $userId)
    {
        // Auto-create stamp achievements for user if they have none
        $count = StampAchievement::where('user_id', $userId)->count();
        if ($count === 0) {
            $this->createDefaultAchievements($userId);
        }

        return StampAchievement::with(['histories'])
            ->where('user_id', $userId)
            ->orderBy('id', 'asc')
            ->get();
    }

    public function getHistory(int $achievementId)
    {
        return StampHistory::with('product')
            ->where('stamp_achievement_id', $achievementId)
            ->orderBy('stamped_date', 'desc')
            ->get();
    }

    /**
     * Create default stamp achievements for a new user based on product categories.
     */
    private function createDefaultAchievements(int $userId): void
    {
        $categoryConfigs = [
            [
                'category' => 'Drinks',
                'icon' => 'cafe-outline',
                'color' => '#4A3525',
                'accent_color' => '#B36534',
                'label' => 'Coffee Lover',
                'description' => 'Order 8 drinks to earn a free cup!',
                'required' => 8,
                'reward' => '1 Free Drink (any size)',
            ],
            [
                'category' => 'Foods',
                'icon' => 'fast-food-outline',
                'color' => '#7B3F00',
                'accent_color' => '#E07B39',
                'label' => 'Foodie',
                'description' => 'Order 6 food items to earn a free pastry!',
                'required' => 6,
                'reward' => '1 Free Classic Butter Croissant',
            ],
            [
                'category' => 'Desserts',
                'icon' => 'ice-cream-outline',
                'color' => '#6A1B4D',
                'accent_color' => '#D45FA0',
                'label' => 'Sweet Tooth',
                'description' => 'Order 5 desserts to earn a free cheesecake slice!',
                'required' => 5,
                'reward' => '1 Free Blueberry Cheesecake Slice',
            ],
            [
                'category' => 'Pasalubong',
                'icon' => 'gift-outline',
                'color' => '#1B4D3E',
                'accent_color' => '#3DAA7A',
                'label' => 'Heritage Fan',
                'description' => 'Order 4 pasalubong items to earn a free Hopia box!',
                'required' => 4,
                'reward' => '1 Free Lesh Special Ube Hopia Box',
            ],
        ];

        foreach ($categoryConfigs as $config) {
            StampAchievement::create([
                'user_id' => $userId,
                'category' => $config['category'],
                'icon' => $config['icon'],
                'color' => $config['color'],
                'accent_color' => $config['accent_color'],
                'label' => $config['label'],
                'description' => $config['description'],
                'collected' => 0,
                'required' => $config['required'],
                'reward' => $config['reward'],
            ]);
        }
    }
}
