<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserAddressSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $addresses = [
            [
                'id' => 1,
                'user_id' => 1,
                'label' => 'Home',
                'address' => 'Manila Gate Road, Manila, Philippines',
                'is_default' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'label' => 'Office',
                'address' => 'Lesh HQ Building, Makati City, Philippines',
                'is_default' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('user_addresses')->insert($addresses);
    }
}
