<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->foreignUuid('harga_patokan_reklame_id')->nullable()->after('sub_jenis_pajak_id')->constrained('harga_patokan_reklame')->nullOnDelete();
        });

        $detailRows = DB::table('sub_jenis_pajak')
            ->where('kode', 'like', 'RKL_%')
            ->get(['id', 'kode', 'nama', 'is_insidentil']);

        $hargaPatokanMap = DB::table('harga_patokan_reklame')->pluck('id', 'kode');
        $umbrellaMap = DB::table('sub_jenis_pajak')
            ->whereIn('kode', ['REKLAME_TETAP', 'REKLAME_KAIN'])
            ->pluck('id', 'kode');

        foreach ($detailRows as $detailRow) {
            $hargaPatokanId = $hargaPatokanMap[$detailRow->kode] ?? null;
            $umbrellaId = (bool) $detailRow->is_insidentil
                ? ($umbrellaMap['REKLAME_KAIN'] ?? null)
                : ($umbrellaMap['REKLAME_TETAP'] ?? null);

            if ($umbrellaId) {
                DB::table('tax_objects')
                    ->where('sub_jenis_pajak_id', $detailRow->id)
                    ->update(['sub_jenis_pajak_id' => $umbrellaId]);
            }

            $updates = [];
            if ($umbrellaId) {
                $updates['sub_jenis_pajak_id'] = $umbrellaId;
            }
            if ($hargaPatokanId) {
                $updates['harga_patokan_reklame_id'] = $hargaPatokanId;
            }
            $updates['jenis_reklame'] = $detailRow->nama;

            DB::table('skpd_reklame')
                ->where('sub_jenis_pajak_id', $detailRow->id)
                ->update($updates);
        }

        DB::table('sub_jenis_pajak')
            ->where('kode', 'like', 'RKL_%')
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->dropForeign(['harga_patokan_reklame_id']);
            $table->dropColumn('harga_patokan_reklame_id');
        });
    }
};