<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add new columns to subscriptions (the "plan/offer" table)
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->integer('drinks_per_week')->default(0)->after('drinks');
            $table->integer('duration_days')->default(30)->after('drinks_per_week');
            $table->timestamp('expires_at')->nullable()->after('is_active'); // when the offer itself expires
        });

        // Create user_subscriptions (tracks each user's active subscription)
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');          // when THIS user's subscription ends
            $table->integer('drinks_remaining')->default(0);
            $table->integer('drinks_used')->default(0);
            $table->enum('status', ['active', 'expired', 'completed', 'cancelled'])->default('active');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['drinks_per_week', 'duration_days', 'expires_at']);
        });
    }
};
