<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stamp_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('accent_color')->nullable();
            $table->string('label');
            $table->text('description')->nullable();
            $table->integer('collected')->default(0);
            $table->integer('required')->default(8);
            $table->string('reward')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stamp_achievements');
    }
};
