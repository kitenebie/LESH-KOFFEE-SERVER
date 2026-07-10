<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoyaltyTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $transactions = [
            [
                'id' => 1,
                'user_id' => 1,
                'type' => 'earned',
                'points' => 150,
                'description' => 'Order #LK-90214',
                'transaction_date' => Carbon::parse('2026-07-07'),
                'created_at' => Carbon::parse('2026-07-07'),
                'updated_at' => Carbon::parse('2026-07-07'),
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'type' => 'earned',
                'points' => 100,
                'description' => 'Order #LK-88935',
                'transaction_date' => Carbon::parse('2026-07-06'),
                'created_at' => Carbon::parse('2026-07-06'),
                'updated_at' => Carbon::parse('2026-07-06'),
            ],
            [
                'id' => 3,
                'user_id' => 1,
                'type' => 'redeemed',
                'points' => 200,
                'description' => 'Free Drink Voucher',
                'transaction_date' => Carbon::parse('2026-07-05'),
                'created_at' => Carbon::parse('2026-07-05'),
                'updated_at' => Carbon::parse('2026-07-05'),
            ],
            [
                'id' => 4,
                'user_id' => 1,
                'type' => 'earned',
                'points' => 250,
                'description' => 'Order #LK-85412',
                'transaction_date' => Carbon::parse('2026-07-04'),
                'created_at' => Carbon::parse('2026-07-04'),
                'updated_at' => Carbon::parse('2026-07-04'),
            ],
            [
                'id' => 5,
                'user_id' => 1,
                'type' => 'bonus',
                'points' => 500,
                'description' => 'Gold Member Bonus',
                'transaction_date' => Carbon::parse('2026-07-01'),
                'created_at' => Carbon::parse('2026-07-01'),
                'updated_at' => Carbon::parse('2026-07-01'),
            ],
        ];

        DB::table('loyalty_transactions')->insert($transactions);
    }
}
