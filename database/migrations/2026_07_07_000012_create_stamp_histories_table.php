<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stamp_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stamp_achievement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->date('stamped_date');
            $table->string('stamped_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stamp_histories');
    }
};
