<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Voucher;
use Carbon\Carbon;

class PromoSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Look up voucher IDs by code
        $lesh50 = Voucher::where('code', 'LESH50')->first();
        $leshfree = Voucher::where('code', 'LESHFREE')->first();

        $promos = [
            [
                'id' => 1,
                'voucher_id' => $lesh50 ? $lesh50->id : 1,
                'color' => '#B36534',
                'heading' => '50% off',
                'subheading' => '1st order',
                'badge' => 'Free delivery',
                'image' => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?q=80&w=400&auto=format&fit=crop',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'voucher_id' => $leshfree ? $leshfree->id : 2,
                'color' => '#4A3525',
                'heading' => "50% off +\n₱120 off",
                'subheading' => 'fresh picks',
                'badge' => 'Free delivery',
                'image' => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?q=80&w=400&auto=format&fit=crop',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'voucher_id' => null,
                'color' => '#B36534',
                'heading' => "Up to\n10% off",
                'subheading' => 'fresh picks',
                'badge' => 'Lesh Shop',
                'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?q=80&w=400&auto=format&fit=crop',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('promos')->insert($promos);
    }
}
