<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;

echo "=== JENIS PAJAK ===\n";
$jps = JenisPajak::orderBy('kode')->get();
foreach ($jps as $jp) {
    echo $jp->kode . ' | ' . $jp->nama . ' | tipe: ' . $jp->tipe_assessment . ' | opsen: ' . $jp->opsen_persen . ' | ' . $jp->id . "\n";
}

echo "\n=== SUB JENIS PAJAK (MBLB) ===\n";
$subs = SubJenisPajak::where('kode', 'like', 'MBLB%')->get();
foreach ($subs as $sub) {
    $parentName = $sub->jenisPajak ? $sub->jenisPajak->nama : 'NULL';
    $parentKode = $sub->jenisPajak ? $sub->jenisPajak->kode : 'NULL';
    echo $sub->kode . ' | ' . $sub->nama . ' | parent: ' . $parentKode . ' (' . $parentName . ') | ' . $sub->jenis_pajak_id . "\n";
}

echo "\n=== SUB JENIS PAJAK (AIR TANAH) ===\n";
$airTanah = JenisPajak::where('nama', 'like', '%Air Tanah%')->first();
if ($airTanah) {
    echo "Air Tanah ID: " . $airTanah->id . " | kode: " . $airTanah->kode . "\n";
    $subs = SubJenisPajak::where('jenis_pajak_id', $airTanah->id)->get();
    foreach ($subs as $sub) {
        echo "  " . $sub->kode . ' | ' . $sub->nama . "\n";
    }
}

echo "\n=== SUB JENIS PAJAK (WAPU entries) ===\n";
$wapus = SubJenisPajak::where('kode', 'like', '%WAPU%')->get();
foreach ($wapus as $w) {
    $parentName = $w->jenisPajak ? $w->jenisPajak->nama : 'NULL';
    $parentKode = $w->jenisPajak ? $w->jenisPajak->kode : 'NULL';
    echo $w->kode . ' | ' . $w->nama . ' | parent: ' . $parentKode . ' (' . $parentName . ') | jp_id: ' . $w->jenis_pajak_id . "\n";
}

echo "\n=== AIR_SUMUR / PARKIR sub jenis ===\n";
$airs = SubJenisPajak::where('kode', 'like', 'AIR%')->orWhere('kode', 'like', 'PARKIR%')->get();
foreach ($airs as $a) {
    $parentName = $a->jenisPajak ? $a->jenisPajak->nama : 'NULL';
    $parentKode = $a->jenisPajak ? $a->jenisPajak->kode : 'NULL';
    echo $a->kode . ' | ' . $a->nama . ' | parent: ' . $parentKode . ' (' . $parentName . ') | jp_id: ' . $a->jenis_pajak_id . "\n";
}

echo "\n=== ALL SUB JENIS under 41107 (Parkir) ===\n";
$parkir = JenisPajak::where('kode', '41107')->first();
if ($parkir) {
    $subs = SubJenisPajak::where('jenis_pajak_id', $parkir->id)->get();
    foreach ($subs as $sub) {
        echo $sub->kode . ' | ' . $sub->nama . "\n";
    }
    if ($subs->isEmpty()) echo "(none)\n";
}

echo "\n=== ALL SUB JENIS PAJAK (complete) ===\n";
$allSubs = SubJenisPajak::withTrashed()->get();
foreach ($allSubs as $sub) {
    $parentName = $sub->jenisPajak ? $sub->jenisPajak->nama : 'NULL';
    $parentKode = $sub->jenisPajak ? $sub->jenisPajak->kode : 'NULL';
    $deleted = $sub->trashed() ? ' [DELETED]' : '';
    echo $sub->kode . ' | ' . $sub->nama . ' | parent: ' . $parentKode . ' (' . $parentName . ')' . $deleted . "\n";
}

echo "\n=== RECENT TAX OBJECTS ===\n";
$objs = App\Domain\Tax\Models\TaxObject::latest()->take(5)->get();
foreach ($objs as $obj) {
    $jpName = $obj->jenisPajak ? $obj->jenisPajak->nama : 'NULL';
    $sjpName = $obj->subJenisPajak ? $obj->subJenisPajak->nama : 'NULL';
    echo $obj->nama_objek_pajak . ' | JP: ' . $jpName . ' | SJP: ' . $sjpName . ' | jp_id: ' . $obj->jenis_pajak_id . ' | sjp_id: ' . $obj->sub_jenis_pajak_id . "\n";
}
