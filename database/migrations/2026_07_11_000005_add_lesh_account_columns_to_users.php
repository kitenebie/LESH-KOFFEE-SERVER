<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('lesh_acc', 19)->nullable()->unique()->after('phone'); // 16 digits formatted: XXXX-XXXX-XXXX-XXXX
            $table->string('lesh_exp', 5)->nullable()->after('lesh_acc'); // MM/YY
            $table->string('lesh_cvv', 60)->nullable()->after('lesh_exp'); // hashed for security
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['lesh_acc', 'lesh_exp', 'lesh_cvv']);
        });
    }
};
