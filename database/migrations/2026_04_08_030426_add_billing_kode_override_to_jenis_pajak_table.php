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
        Schema::table('jenis_pajak', function (Blueprint $table) {
            $table->string('billing_kode_override', 10)->nullable()->after('kode')
                ->comment('Override kode untuk billing, jika berbeda dari kode utama (misal: retribusi pakai kode reklame 41104)');
        });
    }

    public function down(): void
    {
        Schema::table('jenis_pajak', function (Blueprint $table) {
            $table->dropColumn('billing_kode_override');
        });
    }
};
