<?php

use App\Domain\HistoriPajak\Dto\DokumenPajakRow;
use App\Enums\JenisDokumenPajak;
use Carbon\Carbon;

it('mengubah status menjadi menunggu pembayaran jika belum melewati jatuh tempo', function () {
    $row = new DokumenPajakRow(
        jenisDokumen: JenisDokumenPajak::BILLING,
        jenisPajak: 'PBJT',
        nopd: '1',
        namaObjekPajak: 'Objek Uji',
        nomor: 'INV-001',
        masa: 'Apr 2026',
        tanggalTerbit: Carbon::parse('2026-04-01'),
        jatuhTempo: now()->addDays(2),
        jumlahTagihan: 200000,
        jumlahTerbayar: 0,
        status: 'expired',
        statusLabel: 'Kedaluwarsa',
    );

    expect($row->effectiveStatus())->toBe('menunggu_pembayaran')
        ->and($row->effectiveStatusLabel())->toBe('Menunggu Pembayaran');
});

it('mengubah status menjadi lewat jatuh tempo jika sudah melewati jatuh tempo', function () {
    $row = new DokumenPajakRow(
        jenisDokumen: JenisDokumenPajak::BILLING,
        jenisPajak: 'PBJT',
        nopd: '1',
        namaObjekPajak: 'Objek Uji',
        nomor: 'INV-002',
        masa: 'Apr 2026',
        tanggalTerbit: Carbon::parse('2026-04-01'),
        jatuhTempo: now()->subDay(),
        jumlahTagihan: 200000,
        jumlahTerbayar: 0,
        status: 'expired',
        statusLabel: 'Kedaluwarsa',
    );

    expect($row->effectiveStatus())->toBe('lewat_jatuh_tempo')
        ->and($row->effectiveStatusLabel())->toBe('Lewat Jatuh Tempo');
});