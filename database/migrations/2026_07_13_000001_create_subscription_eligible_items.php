<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot: subscriptions ↔ categories (eligible product categories)
        Schema::create('subscription_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['subscription_id', 'category_id']);
        });

        // Pivot: subscriptions ↔ products (specific eligible products)
        Schema::create('subscription_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['subscription_id', 'product_id']);
        });

        // Redemption type: 'category' = all products in linked categories,
        // 'products' = only specific linked products, 'all' = any product
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('redemption_type', ['all', 'category', 'products'])->default('all')->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('redemption_type');
        });
        Schema::dropIfExists('subscription_products');
        Schema::dropIfExists('subscription_categories');
    }
};
