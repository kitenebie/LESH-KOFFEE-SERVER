<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ProductCustomizationSeeder::class,
            SubscriptionSeeder::class,
            LeshWalletSeeder::class,
            WalletTransactionSeeder::class,
            LoyaltyTransactionSeeder::class,
            LeshPointsSeeder::class,        // After LoyaltyTransaction — links them
            OrderSeeder::class,
            DeliveryTrackingSeeder::class,
            NotificationSeeder::class,
            VoucherSeeder::class,         // Must be before StampSeeder (user_vouchers needs voucher_id)
            PromoSeeder::class,
            StampSeeder::class,           // Needs user, products, vouchers
            StoreSeeder::class,
            UserAddressSeeder::class,
        ]);
    }
}
