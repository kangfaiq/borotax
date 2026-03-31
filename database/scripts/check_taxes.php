<?php
// Quick script to check existing tax records
require_once __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Domain\Tax\Models\Tax;
use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Pimpinan;

echo "=== TAX RECORDS ===\n";
$taxes = Tax::all();
foreach ($taxes as $t) {
    echo "ID: {$t->id}\n";
    echo "  billing_code: {$t->billing_code} | status: {$t->status}\n";
    echo "  amount: {$t->amount} | sanksi: {$t->sanksi} | stpd_number: {$t->stpd_number}\n";
    echo "  masa_pajak: {$t->masa_pajak_bulan}/{$t->masa_pajak_tahun}\n";
    echo "  jatuh_tempo: {$t->jatuh_tempo} | jenis_pajak_id: {$t->jenis_pajak_id}\n";
    echo "---\n";
}

echo "\n=== USERS ===\n";
$users = User::all();
foreach ($users as $u) {
    echo "ID: {$u->id} | role: {$u->role} | nama: {$u->nama_lengkap}\n";
}

echo "\n=== PIMPINAN ===\n";
$pimpinans = Pimpinan::all();
foreach ($pimpinans as $p) {
    echo "ID: {$p->id} | nip: {$p->nip} | nama: {$p->nama} | jabatan: {$p->jabatan}\n";
}
