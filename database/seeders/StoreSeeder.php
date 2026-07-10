<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Store info
        DB::table('stores')->insert([
            'id' => 1,
            'name' => 'Lesh Kaffe × Pasalubong',
            'tagline' => 'A Taste of Home & Heritage',
            'address' => 'Manila Gate Road, Manila, Philippines',
            'phone' => '+63 917 123 4567',
            'email' => 'contact@leshkaffe.com',
            'hours' => 'Mon - Sun: 7:00 AM - 10:00 PM',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Spotlight Customer
        DB::table('spotlight_customers')->insert([
            'id' => 1,
            'store_id' => 1,
            'user_id' => 1,
            'name' => 'Ken Naranjo',
            'cups_this_month' => 42,
            'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=150&auto=format&fit=crop',
            'reward' => 'Ken receives 1 Free French Press Brew + 500 bonus points.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
