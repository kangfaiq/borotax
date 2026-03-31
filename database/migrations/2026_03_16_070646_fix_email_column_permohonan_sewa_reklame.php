<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permohonan_sewa_reklame', function (Blueprint $table) {
            $table->text('email')->nullable()->comment('🔐 Email pemohon (terenkripsi)')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_sewa_reklame', function (Blueprint $table) {
            $table->string('email', 100)->nullable()->change();
        });
    }
};
