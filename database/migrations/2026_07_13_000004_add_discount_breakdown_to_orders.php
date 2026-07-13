<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Breakdown of the 'discount' total into individual components
            $table->decimal('subscription_discount', 10, 2)->default(0)->after('discount');
            $table->decimal('voucher_discount', 10, 2)->default(0)->after('subscription_discount');
            $table->decimal('perk_discount', 10, 2)->default(0)->after('voucher_discount');

            // Which voucher codes were applied
            $table->string('voucher_codes')->nullable()->after('perk_discount');

            // Which subscription was used (if subscription payment or perk applied)
            $table->unsignedBigInteger('subscription_id')->nullable()->after('voucher_codes');

            // Number of items covered by subscription in this order
            $table->integer('subscription_items_used')->default(0)->after('subscription_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_discount',
                'voucher_discount',
                'perk_discount',
                'voucher_codes',
                'subscription_id',
                'subscription_items_used',
            ]);
        });
    }
};
