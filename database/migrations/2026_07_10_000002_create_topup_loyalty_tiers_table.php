<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topup_loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');                              // e.g. "Bronze Tier", "Silver Tier"
            $table->decimal('min_amount', 10, 2);               // Min top-up amount (inclusive)
            $table->decimal('max_amount', 10, 2)->nullable();   // Max top-up amount (inclusive), null = unlimited
            $table->integer('loyalty_points');                   // Points awarded
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topup_loyalty_tiers');
    }
};
