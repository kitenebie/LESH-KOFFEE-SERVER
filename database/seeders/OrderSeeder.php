<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Active order: LK-90214
        DB::table('orders')->insert([
            'id' => 1,
            'user_id' => 1,
            'order_number' => 'LK-90214',
            'date' => Carbon::parse('2026-07-07'),
            'time' => '6:24 PM',
            'status' => 'Out For Delivery',
            'current_step' => 'delivery',
            'fulfillment' => 'Delivery',
            'ref_no' => '04',
            'cashier' => 'Maria A.',
            'subtotal' => 290.00,
            'delivery_fee' => 14.00,
            'discount' => 0.00,
            'total' => 210.00,
            'created_at' => Carbon::parse('2026-07-07 18:24:00'),
            'updated_at' => Carbon::parse('2026-07-07 18:24:00'),
        ]);

        // Past order: LK-88935
        DB::table('orders')->insert([
            'id' => 2,
            'user_id' => 1,
            'order_number' => 'LK-88935',
            'date' => Carbon::parse('2026-07-06'),
            'time' => '10:30 AM',
            'status' => 'Completed',
            'current_step' => 'rate',
            'fulfillment' => 'Delivery',
            'ref_no' => null,
            'cashier' => null,
            'subtotal' => 220.00,
            'delivery_fee' => 49.00,
            'discount' => 9.00,
            'total' => 260.00,
            'created_at' => Carbon::parse('2026-07-06 10:30:00'),
            'updated_at' => Carbon::parse('2026-07-06 10:30:00'),
        ]);

        // Past order: LK-85412
        DB::table('orders')->insert([
            'id' => 3,
            'user_id' => 1,
            'order_number' => 'LK-85412',
            'date' => Carbon::parse('2026-07-04'),
            'time' => '3:15 PM',
            'status' => 'Completed',
            'current_step' => 'rate',
            'fulfillment' => 'DineIn',
            'ref_no' => '02',
            'cashier' => null,
            'subtotal' => 220.00,
            'delivery_fee' => 0.00,
            'discount' => 0.00,
            'total' => 220.00,
            'created_at' => Carbon::parse('2026-07-04 15:15:00'),
            'updated_at' => Carbon::parse('2026-07-04 15:15:00'),
        ]);

        // Order Items for LK-90214
        // Gold Espresso Macchiato (product_id 6)
        DB::table('order_items')->insert([
            'id' => 1,
            'order_id' => 1,
            'product_id' => 6,
            'name' => 'Gold Espresso Macchiato',
            'quantity' => 1,
            'price' => 195.00,
            'created_at' => Carbon::parse('2026-07-07 18:24:00'),
            'updated_at' => Carbon::parse('2026-07-07 18:24:00'),
        ]);

        // Classic Waffle (product_id 9)
        DB::table('order_items')->insert([
            'id' => 2,
            'order_id' => 1,
            'product_id' => 9,
            'name' => 'Classic Waffle',
            'quantity' => 1,
            'price' => 95.00,
            'created_at' => Carbon::parse('2026-07-07 18:24:00'),
            'updated_at' => Carbon::parse('2026-07-07 18:24:00'),
        ]);

        // Order Items for LK-88935
        // Spanish Latte (product_id 4)
        DB::table('order_items')->insert([
            'id' => 3,
            'order_id' => 2,
            'product_id' => 4,
            'name' => 'Spanish Latte',
            'quantity' => 1,
            'price' => 155.00,
            'created_at' => Carbon::parse('2026-07-06 10:30:00'),
            'updated_at' => Carbon::parse('2026-07-06 10:30:00'),
        ]);

        // Chocolate Cookie (product_id 11)
        DB::table('order_items')->insert([
            'id' => 4,
            'order_id' => 2,
            'product_id' => 11,
            'name' => 'Chocolate Cookie',
            'quantity' => 1,
            'price' => 65.00,
            'created_at' => Carbon::parse('2026-07-06 10:30:00'),
            'updated_at' => Carbon::parse('2026-07-06 10:30:00'),
        ]);

        // Order Items for LK-85412
        // Iced Americano (product_id 5)
        DB::table('order_items')->insert([
            'id' => 5,
            'order_id' => 3,
            'product_id' => 5,
            'name' => 'Iced Americano',
            'quantity' => 2,
            'price' => 110.00,
            'created_at' => Carbon::parse('2026-07-04 15:15:00'),
            'updated_at' => Carbon::parse('2026-07-04 15:15:00'),
        ]);
    }
}
