<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permohonan_sewa_reklame', function (Blueprint $table) {
            $table->string('satuan_sewa', 10)->nullable()->after('durasi_sewa_hari');
        });

        // Back-fill existing rows based on durasi_sewa_hari
        DB::table('permohonan_sewa_reklame')
            ->whereNull('satuan_sewa')
            ->where('durasi_sewa_hari', '>=', 365)
            ->update(['satuan_sewa' => 'tahun']);

        DB::table('permohonan_sewa_reklame')
            ->whereNull('satuan_sewa')
            ->where('durasi_sewa_hari', '>=', 28)
            ->update(['satuan_sewa' => 'bulan']);

        DB::table('permohonan_sewa_reklame')
            ->whereNull('satuan_sewa')
            ->update(['satuan_sewa' => 'minggu']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_sewa_reklame', function (Blueprint $table) {
            $table->dropColumn('satuan_sewa');
        });
    }
};
