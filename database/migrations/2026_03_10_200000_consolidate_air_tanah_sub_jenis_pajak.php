<?php

use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $airTanah = JenisPajak::where('kode', '41108')->first();
        if (!$airTanah) {
            return;
        }

        // Create or find the single PAT record
        $pat = SubJenisPajak::updateOrCreate(
            ['kode' => 'PAT'],
            [
                'jenis_pajak_id' => $airTanah->id,
                'nama'           => 'Pajak Air Tanah',
                'tarif_persen'   => 20.00,
                'is_insidentil'  => false,
                'is_active'      => true,
                'urutan'         => 1,
            ]
        );

        // Get old sub jenis IDs to migrate
        $oldKodes = ['AIR_SUMUR_BOR', 'AIR_SUMUR_GALI', 'AIR_MATA_AIR'];
        $oldIds   = SubJenisPajak::whereIn('kode', $oldKodes)->pluck('id')->toArray();

        if (!empty($oldIds)) {
            // Migrate tax_objects references
            DB::table('tax_objects')
                ->whereIn('sub_jenis_pajak_id', $oldIds)
                ->update(['sub_jenis_pajak_id' => $pat->id]);

            // Migrate skpd_air_tanah references (if column exists)
            if (DB::getSchemaBuilder()->hasColumn('skpd_air_tanah', 'sub_jenis_pajak_id')) {
                DB::table('skpd_air_tanah')
                    ->whereIn('sub_jenis_pajak_id', $oldIds)
                    ->update(['sub_jenis_pajak_id' => $pat->id]);
            }

            // Deactivate old records
            SubJenisPajak::whereIn('kode', $oldKodes)->update(['is_active' => false]);
        }
    }

    public function down(): void
    {
        // Re-activate old records
        SubJenisPajak::whereIn('kode', ['AIR_SUMUR_BOR', 'AIR_SUMUR_GALI', 'AIR_MATA_AIR'])
            ->update(['is_active' => true]);
    }
};
