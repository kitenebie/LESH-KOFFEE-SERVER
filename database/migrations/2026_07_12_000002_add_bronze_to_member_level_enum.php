<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter the ENUM column to include 'Bronze' as a valid value
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `member_level` ENUM('Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond') NOT NULL DEFAULT 'Bronze'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `member_level` ENUM('Silver', 'Gold', 'Platinum', 'Diamond') NOT NULL DEFAULT 'Silver'");
    }
};
