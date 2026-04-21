<?php

use Illuminate\Testing\TestView;

it('renders searchable instansi picker for self-assessment billing', function () {
    $view = $this->blade(
        '<x-searchable-instansi-select
            model="instansiId"
            :options="$options"
            label="Instansi / OPD"
            placeholder="Cari instansi / OPD / lembaga..."
            empty-label="-- Opsional, tanpa instansi --"
            help-text="Isi jika billing ini ditagihkan melalui OPD atau instansi tertentu." />',
        [
            'options' => [
                'instansi-1' => 'Dinas Komunikasi dan Informatika (OPD)',
                'instansi-2' => 'Sekretariat Daerah (Instansi)',
            ],
        ]
    );

    expect($view)->toBeInstanceOf(TestView::class);

    $view->assertSee('Instansi / OPD')
        ->assertSee('Cari instansi / OPD / lembaga...')
        ->assertSee('Dinas Komunikasi dan Informatika (OPD)')
        ->assertSee('Sekretariat Daerah (Instansi)')
        ->assertSee('-- Opsional, tanpa instansi --')
        ->assertSee('Tidak ada instansi yang cocok dengan pencarian.')
        ->assertSee('Isi jika billing ini ditagihkan melalui OPD atau instansi tertentu.');
});

it('renders searchable instansi picker for mblb billing', function () {
    $view = $this->blade(
        '<x-searchable-instansi-select
            model="instansiId"
            :options="$options"
            label="Instansi / Lembaga"
            placeholder="Cari instansi / OPD / lembaga..."
            empty-label="-- Opsional, tanpa instansi --"
            help-text="Isi jika tagihan MBLB ini diterbitkan untuk skema WAPU tertentu." />',
        [
            'options' => [
                'instansi-1' => 'Balai Besar Wilayah Sungai Bengawan Solo (Instansi)',
                'instansi-2' => 'Dinas Pekerjaan Umum Sumber Daya Air (OPD)',
            ],
        ]
    );

    expect($view)->toBeInstanceOf(TestView::class);

    $view->assertSee('Instansi / Lembaga')
        ->assertSee('Cari instansi / OPD / lembaga...')
        ->assertSee('Balai Besar Wilayah Sungai Bengawan Solo (Instansi)')
        ->assertSee('Dinas Pekerjaan Umum Sumber Daya Air (OPD)')
        ->assertSee('-- Opsional, tanpa instansi --')
        ->assertSee('Isi jika tagihan MBLB ini diterbitkan untuk skema WAPU tertentu.');
});