<?php

// Check and clean old PPJ entries
require_once __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\Tax;

$oldCodes = ['PPJ_LAIN', 'PPJ_SENDIRI'];
$oldEntries = SubJenisPajak::whereIn('kode', $oldCodes)->get();

foreach ($oldEntries as $entry) {
    $taxCount = Tax::where('sub_jenis_pajak_id', $entry->id)->count();
    echo "{$entry->kode}: {$taxCount} taxes referencing\n";
    
    if ($taxCount === 0) {
        $entry->update(['is_active' => false]);
        $entry->delete(); // soft delete
        echo "  -> Soft deleted and deactivated\n";
    } else {
        echo "  -> Has references, skipping\n";
    }
}

echo "\nRemaining active PPJ entries:\n";
$ppj = \App\Domain\Master\Models\JenisPajak::where('kode', '41105')->first();
if ($ppj) {
    $remaining = SubJenisPajak::where('jenis_pajak_id', $ppj->id)->where('is_active', true)->get(['kode', 'nama', 'tarif_persen']);
    foreach ($remaining as $r) {
        echo "  {$r->kode}: {$r->nama} ({$r->tarif_persen}%)\n";
    }
}
