<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesh_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('currency', 3)->default('PHP');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('user_id'); // 1 user = 1 wallet
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesh_wallets');
    }
};
