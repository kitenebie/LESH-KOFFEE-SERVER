<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->date('date');
            $table->string('time');
            $table->string('status')->default('Queued');
            $table->string('current_step')->default('queue');
            $table->enum('fulfillment', ['DineIn', 'Delivery'])->default('DineIn');
            $table->string('ref_no')->nullable();
            $table->string('req_id')->nullable();
            $table->string('cashier')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
