<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StampSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Stamp Achievements (user_id is required by migration)
        $achievements = [
            [
                'id' => 1,
                'user_id' => 1,
                'category' => 'Drinks',
                'icon' => 'cafe-outline',
                'color' => '#4A3525',
                'accent_color' => '#B36534',
                'label' => 'Coffee Lover',
                'description' => 'Order 8 drinks to earn a free cup!',
                'collected' => 6,
                'required' => 8,
                'reward' => '1 Free Drink (any size)',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'category' => 'Foods',
                'icon' => 'fast-food-outline',
                'color' => '#7B3F00',
                'accent_color' => '#E07B39',
                'label' => 'Foodie',
                'description' => 'Order 6 food items to earn a free pastry!',
                'collected' => 3,
                'required' => 6,
                'reward' => '1 Free Classic Butter Croissant',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'user_id' => 1,
                'category' => 'Desserts',
                'icon' => 'ice-cream-outline',
                'color' => '#6A1B4D',
                'accent_color' => '#D45FA0',
                'label' => 'Sweet Tooth',
                'description' => 'Order 5 desserts to earn a free cheesecake slice!',
                'collected' => 1,
                'required' => 5,
                'reward' => '1 Free Blueberry Cheesecake Slice',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'user_id' => 1,
                'category' => 'Pasalubong',
                'icon' => 'gift-outline',
                'color' => '#1B4D3E',
                'accent_color' => '#3DAA7A',
                'label' => 'Heritage Fan',
                'description' => 'Order 4 pasalubong items to earn a free Hopia box!',
                'collected' => 0,
                'required' => 4,
                'reward' => '1 Free Lesh Special Ube Hopia Box',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('stamp_achievements')->insert($achievements);

        // Stamp Histories (table: stamp_histories, FK: stamp_achievement_id)
        $stampHistory = [
            ['id' => 1, 'stamp_achievement_id' => 1, 'product_id' => 1, 'product_name' => 'Classic Cappuccino', 'stamped_date' => '2026-07-05', 'stamped_time' => '9:15 AM', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'stamp_achievement_id' => 1, 'product_id' => 2, 'product_name' => 'Caramel Macchiato', 'stamped_date' => '2026-07-05', 'stamped_time' => '2:30 PM', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'stamp_achievement_id' => 1, 'product_id' => 3, 'product_name' => 'Vanilla Sweet Cream Cold Brew', 'stamped_date' => '2026-07-04', 'stamped_time' => '10:00 AM', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'stamp_achievement_id' => 1, 'product_id' => 4, 'product_name' => 'Spanish Latte', 'stamped_date' => '2026-07-03', 'stamped_time' => '8:45 AM', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'stamp_achievement_id' => 1, 'product_id' => 6, 'product_name' => 'Gold Espresso Macchiato', 'stamped_date' => '2026-07-02', 'stamped_time' => '3:20 PM', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'stamp_achievement_id' => 1, 'product_id' => 5, 'product_name' => 'Iced Americano', 'stamped_date' => '2026-07-01', 'stamped_time' => '11:00 AM', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'stamp_achievement_id' => 2, 'product_id' => 7, 'product_name' => 'Classic Butter Croissant', 'stamped_date' => '2026-07-05', 'stamped_time' => '9:20 AM', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'stamp_achievement_id' => 2, 'product_id' => 9, 'product_name' => 'Classic Waffle', 'stamped_date' => '2026-07-04', 'stamped_time' => '11:00 AM', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'stamp_achievement_id' => 2, 'product_id' => 8, 'product_name' => 'Clubhouse Sandwich', 'stamped_date' => '2026-07-02', 'stamped_time' => '1:15 PM', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'stamp_achievement_id' => 3, 'product_id' => 10, 'product_name' => 'Blueberry Cheesecake Slice', 'stamped_date' => '2026-07-03', 'stamped_time' => '3:45 PM', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('stamp_histories')->insert($stampHistory);

        // User Vouchers (earned from stamps)
        $userVouchers = [
            ['id' => 1, 'user_id' => 1, 'voucher_id' => 1, 'code' => 'LESH-FREE-001', 'description' => '1 Free Drink (any size)', 'expires_at' => '2026-08-01', 'is_used' => false, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'user_id' => 1, 'voucher_id' => 2, 'code' => 'LESH-FOOD-002', 'description' => '1 Free Classic Butter Croissant', 'expires_at' => '2026-08-01', 'is_used' => false, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'user_id' => 1, 'voucher_id' => 3, 'code' => 'LESH-SWEET-003', 'description' => '1 Free Blueberry Cheesecake Slice', 'expires_at' => '2026-08-01', 'is_used' => false, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'user_id' => 1, 'voucher_id' => 4, 'code' => 'LESH-PASALUBONG-004', 'description' => '1 Free Lesh Special Ube Hopia Box', 'expires_at' => '2026-08-01', 'is_used' => false, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('user_vouchers')->insert($userVouchers);
    }
}
