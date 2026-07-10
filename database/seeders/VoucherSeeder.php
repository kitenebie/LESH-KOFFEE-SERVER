<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $vouchers = [
            [
                'id' => 1,
                'code' => 'LESH50',
                'discount' => 0.50,
                'label' => '50% welcome discount!',
                'type' => 'percent',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'code' => 'LESHFREE',
                'discount' => 0.20,
                'label' => 'Free delivery discount applied!',
                'type' => 'percent',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'code' => 'LESH20',
                'discount' => 0.20,
                'label' => '20% off your order!',
                'type' => 'percent',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'code' => 'FRIDAY50',
                'discount' => 0.50,
                'label' => '50% off — Happy Friday!',
                'type' => 'percent',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'code' => 'WELCOME',
                'discount' => 0.10,
                'label' => '10% welcome discount.',
                'type' => 'percent',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('vouchers')->insert($vouchers);
    }
}
