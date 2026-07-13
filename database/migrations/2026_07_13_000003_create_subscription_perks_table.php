<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Subscription perks — bonus discounts on categories
        // Examples:
        //   "10% food discount" → perk_type=category_discount, discount_type=percent, discount_value=10, category_id=2 (food)
        //   "₱30 food discount" → perk_type=category_discount, discount_type=fixed, discount_value=30, category_id=2
        Schema::create('subscription_perks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->enum('perk_type', ['category_discount', 'free_delivery', 'loyalty_multiplier'])->default('category_discount');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete(); // which category gets the discount
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent'); // percent or fixed amount
            $table->decimal('discount_value', 10, 2)->default(0); // 10 = 10% or ₱10
            $table->decimal('max_discount', 10, 2)->nullable(); // cap for percent discounts (optional)
            $table->integer('usage_limit_per_order')->nullable(); // max items this discount applies to per order (null = unlimited)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['subscription_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_perks');
    }
};
