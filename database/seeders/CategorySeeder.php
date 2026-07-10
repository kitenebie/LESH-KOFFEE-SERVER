<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'id' => 1,
                'slug' => 'all',
                'name' => 'All Menu',
                'icon' => 'grid-outline',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'slug' => 'drinks',
                'name' => 'Drinks',
                'icon' => 'cafe-outline',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'slug' => 'foods',
                'name' => 'Foods',
                'icon' => 'fast-food-outline',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'slug' => 'desserts',
                'name' => 'Desserts',
                'icon' => 'ice-cream-outline',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'slug' => 'pasalubong',
                'name' => 'Pasalubong',
                'icon' => 'gift-outline',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('categories')->insert($categories);
    }
}
