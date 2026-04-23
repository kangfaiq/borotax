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