<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Populate NULL bentuk values in tax_objects for reklame records.
     * Default to 'persegi' since most reklame are rectangular (panjang × lebar).
     */
    public function up(): void
    {
        // Get jenis_pajak_id for reklame (kode 41104)
        $jenisPajakId = DB::table('jenis_pajak')->where('kode', '41104')->value('id');

        if ($jenisPajakId) {
            DB::table('tax_objects')
                ->where('jenis_pajak_id', $jenisPajakId)
                ->whereNull('bentuk')
                ->update(['bentuk' => 'persegi']);
        }
    }

    public function down(): void
    {
        // No rollback needed — data was NULL before, setting back to NULL is destructive
    }
};
