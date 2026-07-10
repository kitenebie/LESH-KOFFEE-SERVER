<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'name' => 'John Kenneth Naranjo',
            'first_name' => 'John Kenneth',
            'email' => 'ken.naranjo@leshkaffe.com',
            'phone' => '+63 917 123 4567',
            'avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=150&auto=format&fit=crop',
            'member_level' => 'Gold',
            'member_level_label' => 'Lesh Kaffe Gold Member',
            'wallet_balance' => 500.00,
            'loyalty_points' => 1250,
            'stamps_collected' => 6,
            'stamps_required' => 8,
            'subscription_balance' => 0,
            'active_subscription_id' => null,
            'joined_date' => '2025-01-14',
            'latitude' => 14.5547,
            'longitude' => 121.0244,
            'password' => Hash::make('password'),
            'email_verified_at' => Carbon::now(),
            'created_at' => Carbon::parse('2025-01-14'),
            'updated_at' => Carbon::now(),
        ]);
    }
}
