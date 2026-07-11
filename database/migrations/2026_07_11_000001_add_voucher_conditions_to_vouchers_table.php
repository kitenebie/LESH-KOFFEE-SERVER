<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->decimal('min_order_amount', 10, 2)->nullable()->after('is_active');
            $table->decimal('max_discount', 10, 2)->nullable()->after('min_order_amount');
            $table->integer('valid_hours')->nullable()->after('max_discount');
        });

        // Change user_vouchers.expires_at from date to datetime
        Schema::table('user_vouchers', function (Blueprint $table) {
            $table->dateTime('expires_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['min_order_amount', 'max_discount', 'valid_hours']);
        });

        Schema::table('user_vouchers', function (Blueprint $table) {
            $table->date('expires_at')->nullable()->change();
        });
    }
};
