<?php

use Database\Seeders\AsetReklamePemkabSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows weekly rental rates for public reklame assets', function (): void {
    $this->seed(AsetReklamePemkabSeeder::class);

    $response = $this->get(route('publik.sewa-reklame'));

    $response->assertOk();
    $response->assertSee('Per Minggu');
    $response->assertSee('harga_sewa_per_minggu');
    $response->assertSee('124000');
});

it('exposes google maps links for public reklame assets with coordinates', function (): void {
    $this->seed(AsetReklamePemkabSeeder::class);

    $aset = \App\Domain\Reklame\Models\AsetReklamePemkab::where('kode_aset', 'NB001')->firstOrFail();

    $this->get(route('publik.sewa-reklame'))
        ->assertOk()
        ->assertSee('Buka Google Maps')
        ->assertSee('"lat":"-7.1686316"', false)
        ->assertSee('"lng":"111.8925701"', false);

    $this->get(route('sewa-reklame.form', $aset->id))
        ->assertOk()
        ->assertSee('Buka lokasi aset di Google Maps')
        ->assertSee('https://www.google.com/maps?q=-7.1686316,111.8925701', false);
});