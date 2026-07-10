<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('rider_name');
            $table->string('rider_phone')->nullable();
            $table->string('rider_avatar')->nullable();
            $table->decimal('rider_latitude', 10, 8)->nullable();
            $table->decimal('rider_longitude', 11, 8)->nullable();
            $table->decimal('user_latitude', 10, 8)->nullable();
            $table->decimal('user_longitude', 11, 8)->nullable();
            $table->integer('estimated_minutes')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_trackings');
    }
};
