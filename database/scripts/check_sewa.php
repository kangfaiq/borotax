<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Domain\Reklame\Models\PermohonanSewaReklame;

$p = PermohonanSewaReklame::where('nomor_tiket', 'SEWA-20260316-0007')
    ->with('skpdReklame')
    ->first();

$skpd = $p->skpdReklame;
echo "Permohonan: {$p->nomor_tiket} | status={$p->status}\n";
echo "SKPD: {$skpd->nomor_skpd} | status={$skpd->status}\n";
echo "Kode Billing: {$skpd->kode_billing}\n";

$cetakUrl = URL::signedRoute('sewa-reklame.skpd.cetak', ['skpdId' => $skpd->id]);
$unduhUrl = URL::signedRoute('sewa-reklame.skpd.unduh', ['skpdId' => $skpd->id]);
echo "\nCetak URL: {$cetakUrl}\n";
echo "Unduh URL: {$unduhUrl}\n";
