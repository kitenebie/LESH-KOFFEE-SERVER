<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeshWalletSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('lesh_wallets')->insert([
            'id' => 1,
            'user_id' => 1,
            'balance' => 500.00,
            'created_at' => Carbon::parse('2025-01-14'),
            'updated_at' => Carbon::now(),
        ]);
    }
}
