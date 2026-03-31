<?php

/**
 * Fix script: Re-link sub_jenis_pajak records that were misplaced
 * due to JenisPajakSeeder kode remapping (41106/41107/41108).
 *
 * Also fixes:
 * - Air Tanah tipe_assessment → official_air_tanah
 * - TaxObject records with wrong jenis_pajak_id
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\TaxObject;
use Illuminate\Support\Facades\DB;

echo "=== Fix Sub Jenis Pajak Mapping ===\n\n";

$mblb = JenisPajak::where('kode', '41106')->first();
$parkir = JenisPajak::where('kode', '41107')->first();
$airTanah = JenisPajak::where('kode', '41108')->first();

if (!$mblb || !$parkir || !$airTanah) {
    echo "ERROR: Missing jenis pajak records. Aborting.\n";
    exit(1);
}

echo "MBLB (41106) ID: {$mblb->id}\n";
echo "Parkir (41107) ID: {$parkir->id}\n";
echo "Air Tanah (41108) ID: {$airTanah->id}\n\n";

DB::beginTransaction();

try {
    // 1. PARKIR_TETAP: move from MBLB (41106) → Parkir (41107)
    $fixed = SubJenisPajak::where('kode', 'PARKIR_TETAP')
        ->where('jenis_pajak_id', $mblb->id)
        ->update(['jenis_pajak_id' => $parkir->id]);
    echo "1. PARKIR_TETAP → Parkir (41107): {$fixed} record(s) fixed\n";

    // 2. PAT: move from Parkir (41107) → Air Tanah (41108)
    $fixed = SubJenisPajak::where('kode', 'PAT')
        ->where('jenis_pajak_id', $parkir->id)
        ->update(['jenis_pajak_id' => $airTanah->id]);
    echo "2. PAT → Air Tanah (41108): {$fixed} record(s) fixed\n";

    // 3. MBLB_WP: move from Air Tanah (41108) → MBLB (41106)
    $fixed = SubJenisPajak::where('kode', 'MBLB_WP')
        ->where('jenis_pajak_id', $airTanah->id)
        ->update(['jenis_pajak_id' => $mblb->id]);
    echo "3. MBLB_WP → MBLB (41106): {$fixed} record(s) fixed\n";

    // 4. MBLB_WAPU: move from Air Tanah (41108) → MBLB (41106)
    $fixed = SubJenisPajak::where('kode', 'MBLB_WAPU')
        ->where('jenis_pajak_id', $airTanah->id)
        ->update(['jenis_pajak_id' => $mblb->id]);
    echo "4. MBLB_WAPU → MBLB (41106): {$fixed} record(s) fixed\n";

    echo "5. Air Tanah tipe_assessment: skipped (uses kode-based check now)\n";

    // 6. Fix TaxObjects: any TaxObject whose sub_jenis_pajak is MBLB_* should have MBLB as jenis_pajak
    $mblbSubIds = SubJenisPajak::where('kode', 'like', 'MBLB_%')
        ->where('jenis_pajak_id', $mblb->id)
        ->pluck('id');
    $fixedObjs = TaxObject::whereIn('sub_jenis_pajak_id', $mblbSubIds)
        ->where('jenis_pajak_id', '!=', $mblb->id)
        ->update(['jenis_pajak_id' => $mblb->id]);
    echo "6. TaxObjects with MBLB sub_jenis → MBLB jenis_pajak: {$fixedObjs} record(s) fixed\n";

    // 7. Fix TaxObjects: any TaxObject whose sub_jenis_pajak is PAT should have Air Tanah as jenis_pajak
    $patSubIds = SubJenisPajak::where('kode', 'PAT')
        ->where('jenis_pajak_id', $airTanah->id)
        ->pluck('id');
    $fixedObjs = TaxObject::whereIn('sub_jenis_pajak_id', $patSubIds)
        ->where('jenis_pajak_id', '!=', $airTanah->id)
        ->update(['jenis_pajak_id' => $airTanah->id]);
    echo "7. TaxObjects with PAT sub_jenis → Air Tanah jenis_pajak: {$fixedObjs} record(s) fixed\n";

    // 8. Fix TaxObjects: any TaxObject whose sub_jenis_pajak is PARKIR_* should have Parkir as jenis_pajak
    $parkirSubIds = SubJenisPajak::where('kode', 'like', 'PARKIR_%')
        ->where('jenis_pajak_id', $parkir->id)
        ->pluck('id');
    $fixedObjs = TaxObject::whereIn('sub_jenis_pajak_id', $parkirSubIds)
        ->where('jenis_pajak_id', '!=', $parkir->id)
        ->update(['jenis_pajak_id' => $parkir->id]);
    echo "8. TaxObjects with PARKIR sub_jenis → Parkir jenis_pajak: {$fixedObjs} record(s) fixed\n";

    DB::commit();
    echo "\n=== All fixes applied successfully ===\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "\nERROR: " . $e->getMessage() . "\nAll changes rolled back.\n";
    exit(1);
}
