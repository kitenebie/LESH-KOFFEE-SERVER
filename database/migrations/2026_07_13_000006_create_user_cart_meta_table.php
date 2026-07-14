<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_cart_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('fulfillment_mode', ['DineIn', 'Delivery'])->default('DineIn');
            $table->json('applied_voucher_codes')->nullable(); // ["WELCOME10", "SAVE20"]
            $table->boolean('use_subscription')->default(false); // whether to apply subscription free items
            $table->integer('subscription_items_to_use')->default(0); // how many items to cover with sub
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_cart_meta');
    }
};
