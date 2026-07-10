<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $notifications = [
            [
                'id' => 1,
                'user_id' => 1,
                'type' => 'order',
                'icon' => 'cafe-outline',
                'title' => 'Order Ready! ☕',
                'message' => 'Your Caramel Macchiato is ready for pick-up at the counter.',
                'is_unread' => true,
                'created_at' => $now->copy()->subMinutes(2),
                'updated_at' => $now->copy()->subMinutes(2),
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'type' => 'promo',
                'icon' => 'gift-outline',
                'title' => 'Buy 1 Get 1 Friday 🎉',
                'message' => 'Every Friday, buy any Barako Brew and get a second cup for free. Valid today only!',
                'is_unread' => true,
                'created_at' => $now->copy()->subHour(),
                'updated_at' => $now->copy()->subHour(),
            ],
            [
                'id' => 3,
                'user_id' => 1,
                'type' => 'loyalty',
                'icon' => 'star-outline',
                'title' => 'Loyalty Milestone 🌟',
                'message' => 'You have collected 6 out of 8 stamps. Only 2 more cups to earn your free drink!',
                'is_unread' => false,
                'created_at' => $now->copy()->subDay(),
                'updated_at' => $now->copy()->subDay(),
            ],
            [
                'id' => 4,
                'user_id' => 1,
                'type' => 'wallet',
                'icon' => 'wallet-outline',
                'title' => 'Wallet Top-Up Successful',
                'message' => '₱200.00 has been added to your Lesh Digital Wallet. New balance: ₱500.00.',
                'is_unread' => false,
                'created_at' => $now->copy()->subDay(),
                'updated_at' => $now->copy()->subDay(),
            ],
            [
                'id' => 5,
                'user_id' => 1,
                'type' => 'promo',
                'icon' => 'pricetag-outline',
                'title' => 'New Voucher Available 🎟️',
                'message' => 'Use code LESH20 for 20% off your next order. Valid until July 31, 2026.',
                'is_unread' => false,
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(3),
            ],
        ];

        DB::table('user_notifications')->insert($notifications);
    }
}
