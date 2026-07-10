<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryTrackingSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('delivery_trackings')->insert([
            'id' => 1,
            'order_id' => 1,
            'user_id' => 1,
            'rider_name' => 'Carlo D.',
            'rider_phone' => '+63 919 555 0123',
            'rider_avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=150&auto=format&fit=crop',
            'rider_latitude' => 14.309855844425678,
            'rider_longitude' => 121.05000963865385,
            'user_latitude' => 14.308983948962979,
            'user_longitude' => 121.04888389215255,
            'estimated_minutes' => 8,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
