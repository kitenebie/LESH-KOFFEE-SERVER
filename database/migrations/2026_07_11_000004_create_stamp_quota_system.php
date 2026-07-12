<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Membership tier levels with stamp quotas
        Schema::create('stamp_quota_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Bronze, Silver, Gold, Diamond, Platinum
            $table->string('slug')->unique(); // bronze, silver, gold, diamond, platinum
            $table->integer('rank')->default(0); // order: 1=Bronze, 2=Silver, 3=Gold...
            $table->string('color', 7)->default('#CD7F32'); // hex badge color
            $table->string('icon')->nullable(); // icon name
            $table->integer('reward_points')->default(0); // points awarded on completing this tier
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Requirements per tier (e.g., Bronze: 5 drinks, 3 food, 4 pasalubong)
        Schema::create('stamp_quota_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stamp_quota_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories'); // product category (drinks, food, pasalubong)
            $table->integer('required_count'); // e.g., 5 purchases
            $table->integer('points_per_stamp')->default(0); // points awarded per individual stamp
            $table->timestamps();

            $table->unique(['stamp_quota_category_id', 'category_id'], 'sqr_category_unique');
        });

        // User's progress toward each requirement
        Schema::create('user_stamp_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stamp_quota_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stamp_quota_requirement_id')->constrained()->cascadeOnDelete();
            $table->integer('current_count')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'stamp_quota_requirement_id'], 'usp_user_req_unique');
        });

        // Add membership tier to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('stamp_quota_category_id')->nullable()->after('active_subscription_id')
                ->constrained('stamp_quota_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['stamp_quota_category_id']);
            $table->dropColumn('stamp_quota_category_id');
        });

        Schema::dropIfExists('user_stamp_progress');
        Schema::dropIfExists('stamp_quota_requirements');
        Schema::dropIfExists('stamp_quota_categories');
    }
};
