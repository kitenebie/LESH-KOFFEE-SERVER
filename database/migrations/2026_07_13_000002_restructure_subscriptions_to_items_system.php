<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Update subscriptions (the plan/offer table) ─────────────────────
        Schema::table('subscriptions', function (Blueprint $table) {
            // Rename drinks_per_week → items_per_week
            $table->renameColumn('drinks_per_week', 'items_per_week');
            // Rename drinks → items_limit (total maximum items over entire subscription)
            $table->renameColumn('drinks', 'items_limit');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            // expiration_days = how many days the subscription lasts for the user
            $table->integer('expiration_days')->default(360)->after('items_limit');
        });

        // ─── Update user_subscriptions (user's active subscription record) ───
        Schema::table('user_subscriptions', function (Blueprint $table) {
            // Rename drinks columns to items
            $table->renameColumn('drinks_remaining', 'items_remaining');
            $table->renameColumn('drinks_used', 'items_used');
        });

        Schema::table('user_subscriptions', function (Blueprint $table) {
            // items_limit = copied from plan at time of purchase (snapshot)
            $table->integer('items_limit')->default(0)->after('items_used');
            // items_per_week = weekly limit snapshot
            $table->integer('items_per_week')->default(0)->after('items_limit');
            // items_used_this_week = resets every week
            $table->integer('items_used_this_week')->default(0)->after('items_per_week');
            // week_started_at = tracks current week window
            $table->timestamp('week_started_at')->nullable()->after('items_used_this_week');
        });
    }

    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['items_limit', 'items_per_week', 'items_used_this_week', 'week_started_at']);
        });

        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->renameColumn('items_remaining', 'drinks_remaining');
            $table->renameColumn('items_used', 'drinks_used');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('expiration_days');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->renameColumn('items_per_week', 'drinks_per_week');
            $table->renameColumn('items_limit', 'drinks');
        });
    }
};
