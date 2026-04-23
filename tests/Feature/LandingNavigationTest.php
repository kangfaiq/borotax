<?php

use function Pest\Laravel\get;

it('menempatkan cek billing dan histori pajak di submenu layanan publik landing page', function () {
    $response = get(route('home'));

    $response
        ->assertOk()
        ->assertSee('<a href="' . url('/cek-billing') . '"><i class="bi bi-receipt-cutoff"></i> Cek Billing</a>', false)
        ->assertSee('<a href="' . url('/histori-pajak') . '"><i class="bi bi-clock-history"></i> Histori Pajak</a>', false)
        ->assertDontSee('<a href="' . url('/cek-billing') . '">Cek Billing</a>', false)
        ->assertDontSee('<a href="' . url('/histori-pajak') . '">Histori Pajak</a>', false);
});

    it('menampilkan cek billing dan histori pajak sebagai kartu layanan publik di landing page', function () {
        $response = get(route('home'));

        $response
        ->assertOk()
        ->assertSee('Periksa status tagihan dan pembayaran pajak daerah cukup dengan kode billing.')
        ->assertSee('Lihat riwayat dokumen pajak per wajib pajak untuk satu tahun pajak tanpa login.')
        ->assertSee('<a href="' . url('/cek-billing') . '" class="feature-link">Cek Billing <i class="bi bi-arrow-right"></i></a>', false)
        ->assertSee('<a href="' . url('/histori-pajak') . '" class="feature-link">Lihat Histori <i class="bi bi-arrow-right"></i></a>', false);
    });