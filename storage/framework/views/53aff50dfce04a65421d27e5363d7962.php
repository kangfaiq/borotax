<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKPD Reklame - <?php echo e($skpd->nomor_skpd); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
            line-height: 1.4;
        }

        .page {
            width: auto;
            margin: 0;
            padding: 20px;
            border: 3px solid #000;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 3px 6px;
            font-size: 10pt;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .no-border {
            border: none !important;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .kop-table td {
            border: 1px solid #000;
            vertical-align: middle;
        }

        .footer-table td {
            border: none;
        }

        @media print {
            html, body {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                background: #fff;
            }

            .page {
                margin: 0;
                padding: 5mm 10mm;
                border: none;
                overflow: hidden;
            }
        }

        @page {
            size: 215mm 330mm;
            margin: 12mm 15mm 12mm 15mm;
        }
    </style>
</head>

<body>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($skpd->status === 'draft'): ?>
    <div style="position: fixed; top: 35%; left: 10%; width: 80%; text-align: center; z-index: 9999; pointer-events: none;">
        <span style="font-size: 120px; font-weight: bold; color: rgba(255, 0, 0, 0.12); letter-spacing: 20px; transform: rotate(-45deg); display: inline-block; font-family: Arial, sans-serif;">DRAFT</span>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php
        $taxObject = $skpd->reklameObject;
        $jenisPajak = $skpd->jenisPajak;
        $subJenisPajak = $skpd->subJenisPajak;

        // Nilai perhitungan (sudah terenkripsi)
        $tarifPokok = (float) $skpd->tarif_pokok;
        $luasM2 = (float) $skpd->luas_m2;
        $jumlahMuka = (int) $skpd->jumlah_muka;
        $durasi = (int) ($skpd->durasi ?? 1);
        $jumlahReklame = (int) ($skpd->jumlah_reklame ?? 1);
        $penyesuaianLokasi = (float) ($skpd->penyesuaian_lokasi ?? 1);
        $penyesuaianProduk = (float) ($skpd->penyesuaian_produk ?? 1);
        $nilaiStrategis = (float) ($skpd->nilai_strategis ?? 0);
        $pokokPajakDasar = (float) ($skpd->pokok_pajak_dasar ?? 0);
        $dasarPengenaan = (float) ($skpd->dasar_pengenaan ?? 0);
        $jumlahPajak = (float) ($skpd->jumlah_pajak ?? 0);

        // Dimensi dari snapshot skpd_reklame (fallback ke tax_object untuk data lama)
        $panjang = (float) ($skpd->panjang ?? ($taxObject->panjang ?? 0));
        $lebar = (float) ($skpd->lebar ?? ($taxObject->lebar ?? 0));
        $bentuk = $skpd->bentuk ?? ($taxObject->bentuk ?? 'persegi');

        // Rumus luas berdasarkan bentuk (tampilkan angka aktual)
        $fmtP = number_format($panjang, 2, ',', '.');
        $fmtL = number_format($lebar, 2, ',', '.');
        $rumusLuas = match($bentuk) {
            'trapesium' => "½ × ({$fmtP}+{$fmtL}) × t",
            'lingkaran' => "3,14 × ({$fmtP}/2)²",
            'elips' => "3,14 × ({$fmtP}/2) × ({$fmtL}/2)",
            'segitiga' => "½ × {$fmtP} × {$fmtL}",
            default => "{$fmtP} × {$fmtL}",
        };

        // NSPR dan NJOPR (dari snapshot, fallback untuk data lama)
        $nspr = $skpd->nspr !== null ? (float) $skpd->nspr : $tarifPokok;
        $njopr = $skpd->njopr !== null ? (float) $skpd->njopr : 0;

        // Satuan label (dari snapshot, fallback lookup)
        $satuanLabel = $skpd->satuan_label;
        if (!$satuanLabel) {
            $satuanLabel = match($skpd->satuan_waktu ?? 'perTahun') {
                'perTahun' => 'Th/m²',
                'perBulan' => 'Bln/m²',
                'perMinggu' => 'Minggu/m²',
                'perHari' => 'Hari/m²',
                'perLembar' => 'Lembar',
                'perMingguPerBuah' => 'Minggu/buah',
                'perHariPerBuah' => 'Hari/buah',
                default => 'Th/m²',
            };
        }

        // Tarif pajak
        $tarifPersen = 25;

        // Penyelenggaraan (ganti karakter khusus yang tidak didukung DomPDF)
        $penyelenggaraan = $subJenisPajak
            ? 'Reklame ' . str_replace(['≥', '≤'], ['>=', '<='], $subJenisPajak->nama)
            : ($skpd->jenis_reklame ?? '-');

        // Kelompok strategis
        $kelompokStrategis = $skpd->kelompok_lokasi
            ? 'Strategis ' . strtoupper($skpd->kelompok_lokasi)
            : '-';

        // Kode rekening: format X.X.X.XX berdasarkan kode jenis pajak (sama dengan billing SA)
        $kodeJenisPajak = $jenisPajak->kode ?? '41104';
        $kodeRekening = substr($kodeJenisPajak, 0, 1) . '.' . substr($kodeJenisPajak, 1, 1) . '.' . substr($kodeJenisPajak, 2, 1) . '.' . substr($kodeJenisPajak, 3);

        // Kohir: 8 digit terakhir dari kode billing (sama seperti billing SA)
        $kohir = $skpd->kode_billing ? substr($skpd->kode_billing, -8) : '-';

        // Deteksi sewa reklame milik Pemkab
        $isSewaPemkab = !empty($skpd->aset_reklame_pemkab_id);
        $asetPemkab = $isSewaPemkab ? $skpd->asetReklamePemkab : null;

        // Sewa Pemkab: bentuk selalu Persegi Panjang, DPP = jumlah_pajak / tarif
        if ($isSewaPemkab) {
            $bentuk = 'Persegi Panjang';
            $rumusLuas = "{$fmtP} × {$fmtL}";
        }
    ?>

    <div class="page">
        
        <table class="kop-table" style="margin-bottom: 0;">
            <tr>
                <td rowspan="2" style="width: 12%; text-align: center;">
                    <img src="<?php echo e($isPdf ? public_path('images/logo-pemkab.png') : asset('images/logo-pemkab.png')); ?>"
                        style="width: 110px; height: auto;">
                </td>
                <td style="text-align: center;">
                    <h3 style="margin: 0; font-size: 11pt;">PEMERINTAH KABUPATEN BOJONEGORO</h3>
                    <h2 style="margin: 0; font-size: 13pt;">BADAN PENDAPATAN DAERAH</h2>
                    <p style="margin: 0; font-size: 8pt;">Jl. P. Mas Tumapel No.1 Telepon (0353) 881826</p>
                    <p style="margin: 0; font-size: 7pt;">Narahubung Layanan: 081333688233 (PBB) ; 085173023368 (BPHTB);
                        085172330531 (PDL1); 085172240531 (PDL2); 082233099997 (PDL3)</p>
                    <div style="font-weight: bold; font-size: 13pt; letter-spacing: 2px; margin-top: 2px; text-decoration:underline;">
                        BOJONEGORO</div>
                </td>
                <td style="width: 20%; text-align: center; font-size: 9pt; vertical-align: middle;">
                    <strong>Masa Pajak</strong><br>
                    <?php echo e($skpd->masa_berlaku_mulai ? $skpd->masa_berlaku_mulai->format('d/m/Y') : 'dd/mm/yyyy'); ?><br>
                    s.d.<br>
                    <?php echo e($skpd->masa_berlaku_sampai ? $skpd->masa_berlaku_sampai->format('d/m/Y') : 'dd/mm/yyyy'); ?>

                </td>
            </tr>
            <tr>
                <td class="center bold" style="font-size: 12pt; padding: 6px;">
                    SURAT KETETAPAN PAJAK DAERAH (SKPD)
                </td>
                <td style="text-align: center; font-size: 9pt; vertical-align: middle;">
                    <strong>Kohir:</strong><br>
                    <?php echo e($kohir); ?>

                </td>
            </tr>
        </table>

        
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td rowspan="2" style="width: 12%; font-weight: bold; vertical-align: middle; text-align: center; border-top: none;">
                    Jenis<br>Pajak</td>
                <td style="width: 22%; font-weight: bold; border-top: none;">Kode Rekening</td>
                <td style="border-top: none;"><?php echo e($kodeRekening); ?></td>
            </tr>
            <tr>
                <td class="bold">Jenis Pajak</td>
                <td>PAJAK REKLAME</td>
            </tr>
        </table>

        
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td rowspan="3" style="width: 12%; font-weight: bold; vertical-align: middle; text-align: center; border-top: none;">
                    Wajib<br>Pajak</td>
                <td style="width: 22%; font-weight: bold; border-top: none;">NPWPD</td>
                <td style="border-top: none;"><?php echo e($skpd->npwpd ?? $taxObject->npwpd ?? '-'); ?></td>
            </tr>
            <tr>
                <td class="bold">Nama Wajib Pajak</td>
                <td><?php echo e(strtoupper($skpd->nama_wajib_pajak)); ?></td>
            </tr>
            <tr>
                <td class="bold">Alamat Wajib Pajak</td>
                <td><?php echo e($skpd->alamat_wajib_pajak); ?></td>
            </tr>
        </table>

        
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td rowspan="<?php echo e($isSewaPemkab ? 4 : 6); ?>" style="width: 12%; font-weight: bold; vertical-align: middle; text-align: center; border-top: none;">
                    Objek<br>Pajak</td>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSewaPemkab): ?>
                <td style="width: 22%; font-weight: bold; border-top: none;">Kode Aset</td>
                <td style="border-top: none;"><?php echo e($asetPemkab->kode_aset ?? '-'); ?></td>
                <?php else: ?>
                <td style="width: 22%; font-weight: bold; border-top: none;">NOPD</td>
                <td style="border-top: none;"><?php echo e($taxObject->nopd ?? '-'); ?></td>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isSewaPemkab): ?>
            <tr>
                <td class="bold">Jenis Reklame</td>
                <td><?php echo e($penyelenggaraan); ?></td>
            </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <tr>
                <td class="bold">Nama Objek</td>
                <td><?php echo e(strtoupper($skpd->nama_reklame)); ?></td>
            </tr>
            <tr>
                <td class="bold">Alamat Objek</td>
                <td><?php echo e($skpd->alamat_reklame); ?></td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isSewaPemkab): ?>
            <tr>
                <td class="bold">Kelompok Strategis</td>
                <td><?php echo e($kelompokStrategis); ?></td>
            </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isSewaPemkab): ?>
            <tr>
                <td class="bold">Penyelenggaraan</td>
                <td><?php echo e($penyelenggaraan); ?></td>
            </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSewaPemkab && $skpd->permohonanSewa?->jenis_reklame_dipasang): ?>
            <tr>
                <td class="bold">Materi Terpasang</td>
                <td><?php echo e($skpd->permohonanSewa->jenis_reklame_dipasang); ?></td>
            </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </table>

        
        <table style="margin-bottom: 0; border-top: none;">
            <tr class="center bold">
                <td style="width: 12%; border-top: none;">Komponen</td>
                <td colspan="2" style="width: 55%; border-top: none;">Uraian</td>
                <td style="width: 33%; border-top: none;">Nominal (Rp)</td>
            </tr>
            <tr class="center" style="font-size: 8pt;">
                <td>(1)</td>
                <td colspan="2">(2)</td>
                <td>(3)</td>
            </tr>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSewaPemkab): ?>
            
            <tr>
                <td rowspan="10" class="center bold" style="vertical-align: middle;">Pajak</td>
                <td colspan="2" class="bold">1. &nbsp;Spesifikasi Teknis</td>
                <td rowspan="6" style="background: #f0f0f0;"></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;a. Bentuk</td>
                <td class="right"><?php echo e(ucfirst($bentuk)); ?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;b. Panjang (m)</td>
                <td class="right"><?php echo e(number_format($panjang, 2, ',', '.')); ?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;c. Lebar (m)</td>
                <td class="right"><?php echo e(number_format($lebar, 2, ',', '.')); ?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;d. Perhitungan</td>
                <td class="right"><?php echo e($rumusLuas); ?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;e. Luas (m²)</td>
                <td class="right"><?php echo e(number_format($luasM2, 2, ',', '.')); ?></td>
            </tr>
            <tr>
                <td colspan="2" class="bold">2. &nbsp;Kategori Penyelenggaraan</td>
                <td class="right"><?php echo e(match($skpd->satuan_waktu ?? 'perTahun') { 'perTahun' => 'Tahunan', 'perBulan' => 'Bulanan', 'perMinggu' => 'Mingguan', default => 'Tahunan' }); ?></td>
            </tr>
            <tr>
                <td colspan="2" class="bold">3. &nbsp;Durasi Penyelenggaraan</td>
                <td class="right"><?php echo e($durasi); ?></td>
            </tr>
            <tr>
                <td colspan="2" class="bold">4. &nbsp;Pajak Reklame</td>
                <td class="right"><?php echo e(number_format($tarifPokok, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td colspan="2" class="bold">5. &nbsp;Pokok Pajak Terutang (3 x 4)</td>
                <td class="right bold"><?php echo e(number_format($jumlahPajak, 0, ',', '.')); ?></td>
            </tr>
            <?php else: ?>
            
            <tr>
                <td rowspan="8" class="center bold" style="vertical-align: middle;">DPP</td>
                <td colspan="2" class="bold">1. &nbsp;Dasar Pengenaan Pajak [(e x f) + (e x g)]</td>
                <td class="right"><?php echo e(number_format(($nspr + $njopr) * $luasM2, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;a. Bentuk</td>
                <td class="right"><?php echo e(ucfirst($bentuk)); ?></td>
                <td rowspan="7" style="background: #f0f0f0;"></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;b. Panjang (m)</td>
                <td class="right"><?php echo e(number_format($panjang, 2, ',', '.')); ?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;c. Lebar (m)</td>
                <td class="right"><?php echo e(number_format($lebar, 2, ',', '.')); ?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;d. Perhitungan</td>
                <td class="right"><?php echo e($rumusLuas); ?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;e. Luas (m²)</td>
                <td class="right"><?php echo e(number_format($luasM2, 2, ',', '.')); ?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;f. NSPR/(<?php echo e($satuanLabel); ?>)</td>
                <td class="right"><?php echo e(number_format($nspr, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;g. NJOPR/(<?php echo e($satuanLabel); ?>)</td>
                <td class="right"><?php echo e(number_format($njopr, 0, ',', '.')); ?></td>
            </tr>
            
            <tr>
                <td rowspan="3" class="center bold" style="vertical-align: middle;">Pajak</td>
                <td colspan="2" class="bold">2. &nbsp;Tarif Pajak</td>
                <td class="right"><?php echo e(number_format($tarifPersen, 0)); ?>%</td>
            </tr>
            <tr>
                <td colspan="2" class="bold">3. &nbsp;Nilai Strategis</td>
                <td class="right"><?php echo e(number_format($nilaiStrategis, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td colspan="2" class="bold">4. &nbsp;Pokok Pajak (1 x 2) + 3</td>
                <td class="right bold"><?php echo e(number_format($jumlahPajak, 0, ',', '.')); ?></td>
            </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </table>

        
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="border-top: none; padding: 6px;">
                    <strong>Terbilang</strong> &nbsp;:&nbsp;
                    <em><?php echo e(ucwords(\NumberFormatter::create('id_ID', \NumberFormatter::SPELLOUT)->format(abs($jumlahPajak)))); ?> Rupiah</em>
                </td>
            </tr>
        </table>

        
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="border-top: none; padding: 4px 6px;">
                    <strong>Jatuh Tempo</strong> &nbsp;:&nbsp;
                    <?php echo e($skpd->jatuh_tempo ? $skpd->jatuh_tempo->translatedFormat('d F Y') : '-'); ?>

                </td>
            </tr>
        </table>

        
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="width: 30%; font-weight: bold; vertical-align: middle; border-top: none; padding: 4px 6px;">Tempat Pembayaran</td>
                <td style="width: 40%; border-top: none; padding: 4px 6px;">Bank Jatim / BNI / QRIS / Tokopedia / Indomaret / Alfamart</td>
                <td style="width: 30%; text-align: center; font-weight: bold; border-top: none; padding: 4px 6px;"><?php echo e($skpd->kode_billing ?? '-'); ?></td>
            </tr>
        </table>

        
        <table style="border-top: none;">
            <tr>
                <td style="border-top: none; padding: 6px; font-size: 9pt;">
                    <ol style="margin: 0; padding-left: 16px;">
                        <li>Apabila pembayaran melebihi jatuh tempo pembayaran pajak maka dikenakan sanksi
                            administratif berupa bunga sebesar 1% (satu persen) dari pokok pajak.</li>
                        <li>Surat Ketetapan Pajak Daerah (SKPD) bukan merupakan bukti pembayaran pajak.</li>
                    </ol>
                </td>
            </tr>
        </table>

        
        <table class="footer-table" style="width: 100%; margin-top: 10px;">
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%; text-align: center; font-size: 10pt;">
                    
                    Bojonegoro, <?php echo e($skpd->tanggal_verifikasi ? $skpd->tanggal_verifikasi->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y')); ?><br>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pimpinan): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_null($pimpinan->bidang) && is_null($pimpinan->sub_bidang)): ?>
                            
                            <?php echo e($pimpinan->jabatan); ?> <?php echo e($pimpinan->opd); ?><br>
                            <?php echo e($pimpinan->kab); ?>

                        <?php elseif(is_null($pimpinan->sub_bidang)): ?>
                            
                            a.n. <?php echo e($pimpinan->jabatan); ?> <?php echo e($pimpinan->opd); ?><br>
                            <?php echo e($pimpinan->kab); ?><br>
                            <span style="font-size: 9pt;"><?php echo e($pimpinan->jabatan); ?> <?php echo e($pimpinan->bidang); ?></span>
                        <?php else: ?>
                            
                            a.n. <?php echo e($pimpinan->jabatan); ?> <?php echo e($pimpinan->opd); ?><br>
                            <?php echo e($pimpinan->kab); ?><br>
                            <span style="font-size: 9pt;"><?php echo e($pimpinan->jabatan); ?> <?php echo e($pimpinan->sub_bidang); ?></span><br>
                            <span style="font-size: 9pt;"><?php echo e($pimpinan->bidang); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php else: ?>
                        Jabatan<br>
                        Kabupaten
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <div style="margin: 12px auto; width: 80px; height: 80px;">
                        <img src="data:image/svg+xml;base64, <?php echo e(base64_encode((new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer(new \BaconQrCode\Renderer\RendererStyle\RendererStyle(100), new \BaconQrCode\Renderer\Image\SvgImageBackEnd())))->writeString(route('skpd-reklame.show', $skpd->id)))); ?>"
                            alt="QR Code" style="width: 80px; height: 80px;">
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pimpinan): ?>
                        
                        <div style="font-weight: bold; text-decoration: underline;">
                            <?php echo e(strtoupper($pimpinan->nama)); ?>

                        </div>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pimpinan->pangkat): ?>
                            <div style="font-size: 9pt;"><?php echo e($pimpinan->pangkat); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <div style="font-size: 9pt;">NIP. <?php echo e($pimpinan->nip); ?></div>
                    <?php else: ?>
                        <div style="font-weight: bold; text-decoration: underline;">Nama Pejabat</div>
                        <div style="font-size: 9pt;">Pangkat</div>
                        <div style="font-size: 9pt;">NIP</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
<?php /**PATH F:\Worx\laragon\www\borotax\resources\views/documents/skpd-reklame.blade.php ENDPATH**/ ?>