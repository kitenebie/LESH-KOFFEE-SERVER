<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesh_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('balance')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('user_id'); // 1 user = 1 points record
        });

        // Add lesh_points_id FK to loyalty_transactions
        Schema::table('loyalty_transactions', function (Blueprint $table) {
            $table->foreignId('lesh_points_id')->nullable()->after('user_id')->constrained('lesh_points')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_transactions', function (Blueprint $table) {
            $table->dropForeign(['lesh_points_id']);
            $table->dropColumn('lesh_points_id');
        });

        Schema::dropIfExists('lesh_points');
    }
};
