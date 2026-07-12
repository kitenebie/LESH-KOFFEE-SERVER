<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('card_tier', ['Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond'])->default('Bronze');
            $table->string('card_number')->unique(); // XXXX-XXXX-XXXX-XXXX
            $table->string('card_exp'); // MM/YY
            $table->string('card_cvv'); // bcrypt hashed
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_cards');
    }
};
