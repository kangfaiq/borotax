<?php

use function Pest\Laravel\get;

it('menampilkan halaman cek billing dengan header dan sub-navigation layanan publik', function () {
    get(route('billing.check'))
        ->assertOk()
        ->assertSee('LAYANAN PUBLIK')
        ->assertSee('Periksa status tagihan dan pembayaran pajak daerah tanpa login melalui halaman layanan publik.')
        ->assertSee('<a href="' . url('/cek-billing') . '" class="active">', false)
        ->assertSee('Histori Pajak');
});

it('menampilkan halaman histori pajak dengan header dan sub-navigation layanan publik', function () {
    get(route('histori-pajak.index'))
        ->assertOk()
        ->assertSee('LAYANAN PUBLIK')
        ->assertSee('Lihat riwayat dokumen pajak per wajib pajak untuk satu tahun pajak dengan navigasi layanan publik yang sama seperti halaman publik lainnya.')
        ->assertSee('<a href="' . url('/histori-pajak') . '" class="active">', false)
        ->assertSee('Cek Billing');
});
