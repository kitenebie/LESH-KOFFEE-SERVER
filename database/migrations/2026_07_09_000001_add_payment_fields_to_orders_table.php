<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('ref_code')->nullable()->after('req_id');
            $table->string('signature')->nullable()->after('ref_code');
            $table->decimal('amount_paid', 10, 2)->nullable()->after('signature');
            $table->decimal('payment_fee', 10, 2)->nullable()->after('amount_paid');
            $table->string('payment_method')->nullable()->after('payment_fee');
            $table->timestamp('paid_at')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['ref_code', 'signature', 'amount_paid', 'payment_fee', 'payment_method', 'paid_at']);
        });
    }
};
