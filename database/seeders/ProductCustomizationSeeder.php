<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Carbon\Carbon;

class ProductCustomizationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Drinks customizations (products 1-6)
        $drinkCustomizations = json_encode([
            'size' => [
                'isMultiSelect' => false,
                'options' => [
                    ['name' => 'Regular', 'price' => 0],
                    ['name' => 'Medium', 'price' => 20],
                    ['name' => 'Large', 'price' => 35],
                ]
            ],
            'sweetness' => [
                'isMultiSelect' => false,
                'options' => [
                    ['name' => '100%', 'label' => 'Full Sweet', 'price' => 0],
                    ['name' => '70%', 'label' => 'Medium Sweet', 'price' => 0],
                    ['name' => '50%', 'label' => 'Less Sweet', 'price' => 0],
                    ['name' => '0%', 'label' => 'Unsweetened', 'price' => 0],
                ]
            ],
            'milk' => [
                'isMultiSelect' => false,
                'options' => [
                    ['name' => 'Full Cream', 'price' => 0],
                    ['name' => 'Steamed', 'price' => 0],
                    ['name' => 'Oat', 'price' => 30],
                    ['name' => 'Almond', 'price' => 30],
                ]
            ],
            'addons' => [
                'isMultiSelect' => true,
                'options' => [
                    ['name' => 'Espresso Shot', 'price' => 25],
                    ['name' => 'Caramel Drizzle', 'price' => 15],
                    ['name' => 'Whipped Cream', 'price' => 20],
                    ['name' => 'Coffee Jelly', 'price' => 15],
                ]
            ]
        ]);

        // Foods customizations (products 7-9)
        $foodCustomizations = json_encode([
            'size' => [
                'isMultiSelect' => false,
                'options' => [
                    ['name' => 'Regular', 'price' => 0],
                    ['name' => 'Sharing', 'price' => 60],
                ]
            ],
            'addons' => [
                'isMultiSelect' => true,
                'options' => [
                    ['name' => 'Extra Bacon', 'price' => 45],
                    ['name' => 'Cheese Slice', 'price' => 15],
                    ['name' => 'Fried Egg', 'price' => 20],
                ]
            ]
        ]);

        // Desserts customizations (products 10-11)
        $dessertCustomizations = json_encode([
            'size' => [
                'isMultiSelect' => false,
                'options' => [
                    ['name' => 'Regular', 'price' => 0],
                    ['name' => 'Sharing', 'price' => 40],
                ]
            ],
            'addons' => [
                'isMultiSelect' => true,
                'options' => [
                    ['name' => 'Ice Cream Scoop', 'price' => 40],
                    ['name' => 'Chocolate Syrup', 'price' => 15],
                    ['name' => 'Extra Berries', 'price' => 25],
                ]
            ]
        ]);

        // Pasalubong customizations (products 12-14)
        $pasalubongCustomizations = json_encode([
            'size' => [
                'isMultiSelect' => false,
                'options' => [
                    ['name' => 'Regular', 'price' => 0],
                    ['name' => 'Family Box', 'price' => 90],
                ]
            ],
            'addons' => [
                'isMultiSelect' => true,
                'options' => [
                    ['name' => 'Gift Card', 'price' => 15],
                    ['name' => 'Ribbon Wrap', 'price' => 20],
                ]
            ]
        ]);

        $customizations = [];

        // Drinks (product_id 1-6)
        for ($i = 1; $i <= 6; $i++) {
            $customizations[] = [
                'product_id' => $i,
                'customizations' => $drinkCustomizations,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Foods (product_id 7-9)
        for ($i = 7; $i <= 9; $i++) {
            $customizations[] = [
                'product_id' => $i,
                'customizations' => $foodCustomizations,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Desserts (product_id 10-11)
        for ($i = 10; $i <= 11; $i++) {
            $customizations[] = [
                'product_id' => $i,
                'customizations' => $dessertCustomizations,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Pasalubong (product_id 12-14)
        for ($i = 12; $i <= 14; $i++) {
            $customizations[] = [
                'product_id' => $i,
                'customizations' => $pasalubongCustomizations,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('product_customizations')->insert($customizations);
    }
}
