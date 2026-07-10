<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeshPointsSeeder extends Seeder
{
    public function run(): void
    {
        // Calculate balance from loyalty transactions:
        // lp1: earned  +150
        // lp2: earned  +100
        // lp3: redeemed -200
        // lp4: earned  +250
        // lp5: bonus   +500
        // Total: 150 + 100 - 200 + 250 + 500 = 800
        // But user profile shows 1250 (includes prior history)
        // Set balance to 1250 to match user.loyaltyPoints

        $leshPointsId = DB::table('lesh_points')->insertGetId([
            'user_id' => 1,
            'balance' => 1250, // Must match user's loyalty_points
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Update existing loyalty_transactions to link to lesh_points
        DB::table('loyalty_transactions')
            ->where('user_id', 1)
            ->update(['lesh_points_id' => $leshPointsId]);
    }
}
