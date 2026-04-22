<?php

use App\Domain\Master\Models\JenisPajak;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $reklameJenisPajakIds = JenisPajak::where('kode', '41104')->pluck('id');

        if ($reklameJenisPajakIds->isEmpty()) {
            return;
        }

        DB::table('tax_objects')
            ->whereIn('jenis_pajak_id', $reklameJenisPajakIds)
            ->whereNull('bentuk')
            ->update(['bentuk' => 'persegi']);
    }

    public function down(): void
    {
        // Tidak di-rollback: backfill bersifat data normalization
    }
};
