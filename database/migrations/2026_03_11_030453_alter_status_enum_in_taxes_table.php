<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Because doctrine/dbal might have issues with enums or require installation,
        // using raw SQL is safest for simple enum modification in MySQL/MariaDB.
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE taxes MODIFY COLUMN status ENUM('draft', 'pending', 'paid', 'verified', 'rejected', 'expired', 'cancelled', 'partially_paid') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE taxes MODIFY COLUMN status ENUM('draft', 'pending', 'paid', 'verified', 'rejected', 'expired', 'cancelled') DEFAULT 'draft'");
    }
};
