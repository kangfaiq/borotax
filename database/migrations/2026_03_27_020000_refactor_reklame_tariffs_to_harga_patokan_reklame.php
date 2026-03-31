<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reklame_tariffs', function (Blueprint $table) {
            $table->foreignUuid('harga_patokan_reklame_id')->nullable()->after('sub_jenis_pajak_id');
        });

        $detailRows = DB::table('sub_jenis_pajak')
            ->where('kode', 'like', 'RKL_%')
            ->get(['id', 'kode', 'nama', 'nama_lengkap', 'is_insidentil', 'is_active', 'urutan']);

        $umbrellaMap = DB::table('sub_jenis_pajak')
            ->whereIn('kode', ['REKLAME_TETAP', 'REKLAME_KAIN'])
            ->pluck('id', 'kode');

        foreach ($detailRows as $detailRow) {
            $parentId = (bool) $detailRow->is_insidentil
                ? ($umbrellaMap['REKLAME_KAIN'] ?? null)
                : ($umbrellaMap['REKLAME_TETAP'] ?? null);

            if (!$parentId) {
                continue;
            }

            DB::table('harga_patokan_reklame')->updateOrInsert(
                ['kode' => $detailRow->kode],
                [
                    'id' => DB::table('harga_patokan_reklame')->where('kode', $detailRow->kode)->value('id') ?? (string) str()->uuid(),
                    'sub_jenis_pajak_id' => $parentId,
                    'nama' => $detailRow->nama,
                    'nama_lengkap' => $detailRow->nama_lengkap,
                    'is_insidentil' => $detailRow->is_insidentil,
                    'is_active' => $detailRow->is_active,
                    'urutan' => $detailRow->urutan,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $hargaPatokanMap = DB::table('harga_patokan_reklame')->pluck('id', 'kode');

        foreach ($detailRows as $detailRow) {
            $hargaPatokanId = $hargaPatokanMap[$detailRow->kode] ?? null;

            if (!$hargaPatokanId) {
                continue;
            }

            DB::table('reklame_tariffs')
                ->where('sub_jenis_pajak_id', $detailRow->id)
                ->update(['harga_patokan_reklame_id' => $hargaPatokanId]);
        }

        Schema::table('reklame_tariffs', function (Blueprint $table) {
            $table->dropForeign(['sub_jenis_pajak_id']);
            $table->dropUnique('reklame_tariffs_unique');
            $table->dropColumn('sub_jenis_pajak_id');
        });

        Schema::table('reklame_tariffs', function (Blueprint $table) {
            $table->foreign('harga_patokan_reklame_id')->references('id')->on('harga_patokan_reklame')->cascadeOnDelete();
            $table->unique(
                ['harga_patokan_reklame_id', 'kelompok_lokasi', 'satuan_waktu', 'berlaku_mulai'],
                'reklame_tariffs_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('reklame_tariffs', function (Blueprint $table) {
            $table->dropForeign(['harga_patokan_reklame_id']);
            $table->dropUnique('reklame_tariffs_unique');
            $table->foreignUuid('sub_jenis_pajak_id')->nullable()->after('id');
        });

        $detailRows = DB::table('sub_jenis_pajak')
            ->where('kode', 'like', 'RKL_%')
            ->pluck('id', 'kode');

        $hargaPatokanRows = DB::table('harga_patokan_reklame')->get(['id', 'kode']);

        foreach ($hargaPatokanRows as $hargaPatokanRow) {
            $subJenisId = $detailRows[$hargaPatokanRow->kode] ?? null;

            if (!$subJenisId) {
                continue;
            }

            DB::table('reklame_tariffs')
                ->where('harga_patokan_reklame_id', $hargaPatokanRow->id)
                ->update(['sub_jenis_pajak_id' => $subJenisId]);
        }

        Schema::table('reklame_tariffs', function (Blueprint $table) {
            $table->dropForeign(['harga_patokan_reklame_id']);
            $table->dropColumn('harga_patokan_reklame_id');
        });

        Schema::table('reklame_tariffs', function (Blueprint $table) {
            $table->foreign('sub_jenis_pajak_id')->references('id')->on('sub_jenis_pajak')->cascadeOnDelete();
            $table->unique(
                ['sub_jenis_pajak_id', 'kelompok_lokasi', 'satuan_waktu', 'berlaku_mulai'],
                'reklame_tariffs_unique'
            );
        });
    }
};