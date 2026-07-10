<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $subscriptions = [
            [
                'id' => 1,
                'name' => 'Daily Grind Basic',
                'description' => '5 Iced Americanos per week',
                'price' => 399.00,
                'drinks' => 5,
                'icon' => 'cafe-outline',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Daily Grind Plus',
                'description' => '7 any drinks per week + 10% food discount',
                'price' => 599.00,
                'drinks' => 7,
                'icon' => 'sparkles-outline',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Daily Grind Premium',
                'description' => '10 any drinks per week + free pastry',
                'price' => 799.00,
                'drinks' => 10,
                'icon' => 'diamond-outline',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('subscriptions')->insert($subscriptions);
    }
}
