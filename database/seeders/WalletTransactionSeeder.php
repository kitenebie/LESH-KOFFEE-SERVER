<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WalletTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $transactions = [
            [
                'id' => 1,
                'wallet_id' => 1,
                'user_id' => 1,
                'type' => 'credit',
                'amount' => 200.00,
                'description' => 'Wallet Top-Up',
                'transaction_date' => Carbon::parse('2026-07-06'),
                'created_at' => Carbon::parse('2026-07-06'),
                'updated_at' => Carbon::parse('2026-07-06'),
            ],
            [
                'id' => 2,
                'wallet_id' => 1,
                'user_id' => 1,
                'type' => 'debit',
                'amount' => 260.00,
                'description' => 'Order #LK-88935',
                'transaction_date' => Carbon::parse('2026-07-06'),
                'created_at' => Carbon::parse('2026-07-06'),
                'updated_at' => Carbon::parse('2026-07-06'),
            ],
            [
                'id' => 3,
                'wallet_id' => 1,
                'user_id' => 1,
                'type' => 'credit',
                'amount' => 300.00,
                'description' => 'Wallet Top-Up',
                'transaction_date' => Carbon::parse('2026-07-01'),
                'created_at' => Carbon::parse('2026-07-01'),
                'updated_at' => Carbon::parse('2026-07-01'),
            ],
            [
                'id' => 4,
                'wallet_id' => 1,
                'user_id' => 1,
                'type' => 'debit',
                'amount' => 220.00,
                'description' => 'Order #LK-85412',
                'transaction_date' => Carbon::parse('2026-07-04'),
                'created_at' => Carbon::parse('2026-07-04'),
                'updated_at' => Carbon::parse('2026-07-04'),
            ],
        ];

        DB::table('wallet_transactions')->insert($transactions);
    }
}
