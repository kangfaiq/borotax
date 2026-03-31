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
            $table->string('nik_hash', 64)->after('nik')->nullable()->index()->comment('Hash NIK untuk pencarian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_sewa_reklame', function (Blueprint $table) {
            $table->dropColumn('nik_hash');
        });
    }
};
