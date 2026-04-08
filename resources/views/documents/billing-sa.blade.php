<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Billing - BoroTax</title>
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
            line-height: normal;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .no-border {
            border: none !important;
        }

        .no-border-top {
            border-top: none !important;
        }

        .no-border-bottom {
            border-bottom: none !important;
        }

        .no-border-right {
            border-right: none !important;
        }

        .no-border-left {
            border-left: none !important;
        }

        .kop-table td {
            border: 1px solid #000;
            vertical-align: middle;
        }

        .section-header {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .terbilang {
            font-style: italic;
            padding: 6px;
            border: 1px solid #000;
            border-top: none;
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

        .page {
            width: auto;
            margin: 0;
            padding: 20px;
            border: 3px solid #000;
            position: relative;
        }

        .historical-watermark {
            position: absolute;
            top: 44%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-28deg);
            font-size: 40pt;
            font-weight: bold;
            letter-spacing: 4px;
            color: rgba(180, 35, 24, 0.11);
            text-align: center;
            white-space: nowrap;
            z-index: 0;
        }

        .historical-note {
            border: 2px solid #991b1b;
            background: #fff1f2;
            padding: 8px 10px;
            margin-bottom: 4px;
            font-size: 9pt;
            line-height: 1.45;
        }

        .content-layer {
            position: relative;
            z-index: 1;
        }
    </style>
</head>

<body>
    <div class="page">
        @if(!empty($hasNewerPembetulan) && !empty($latestPembetulan))
            <div class="historical-watermark">SUDAH DIPEMBETULKAN</div>
        @endif

        <div class="content-layer">
        {{-- HEADER --}}
        <table class="kop-table" style="margin-bottom: 2px;">
            <tr>
                <td style="width: 15%; text-align: center; border-right: none;">
                    <img src="{{ $isPdf ? public_path('images/logo-pemkab.png') : asset('images/logo-pemkab.png') }}"
                        style="width: 100px;">
                </td>
                <td style="width: 60%; text-align: center; border-left: none; border-right: 1px solid #000;">
                    <h3 style="margin: 0; font-size: 12pt;">PEMERINTAH KABUPATEN BOJONEGORO</h3>
                    <h2 style="margin: 0; font-size: 14pt;">BADAN PENDAPATAN DAERAH</h2>
                    <p style="margin: 0; font-size: 9pt;">Jl. P. Mas Tumapel No.1 Telepon (0353) 881826</p>
                    <p style="margin: 0; font-size: 8pt;">Narahubung Layanan: 081333688233 (PBB) ; 085173023368
                        (BPHTB);<br>085172330531 (PDL1); 085172240531 (PDL2); 082233099997 (PDL3)</p>
                    <div
                        style="font-weight: bold; font-size: 14pt; letter-spacing: 2px; margin-top: 2px; text-decoration:underline;">
                        BOJONEGORO</div>
                </td>
                <td style="width: 25%; padding: 4px;">
                    <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">
                        MASA PAJAK<br>
                        @if($tax->masa_pajak_bulan)
                            {{ sprintf('%02d', $tax->masa_pajak_bulan) }} - {{ $tax->masa_pajak_tahun }}
                        @else
                            TAHUN {{ $tax->masa_pajak_tahun }}
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="center bold" style="font-size: 12pt; padding: 6px;">
                    CETAKAN KODE BILLING PAJAK DAERAH
                </td>
                <td class="center">
                    <div style="font-size: 8pt; font-weight: bold;">KOHIR:</div>
                    <div style="font-size: 10pt;">{{ substr($tax->billing_code, -8) }}</div>
                </td>
            </tr>
        </table>

        {{-- PERHATIAN --}}
        @php
            $isSarangWaletTax = !empty($isSarangWalet);
            if ($isSarangWaletTax) {
                $jatuhTempo = \Carbon\Carbon::parse($tax->payment_expired_at);
            } else {
                $jatuhTempo = \App\Domain\Tax\Models\Tax::hitungJatuhTempoSelfAssessment(
                    (int) $tax->masa_pajak_bulan,
                    (int) $tax->masa_pajak_tahun
                );
            }
        @endphp
        <div style="border: 1px solid #000; padding: 4px; font-size: 9pt; margin-bottom: 2px; border-top: none;">
            <span class="bold" style="text-decoration: underline;">PERHATIAN</span><br>
            Gunakan Kode Billing berikut untuk melakukan pembayaran Pajak Daerah.<br>
            Kode Billing berlaku sampai tanggal: <strong>{{ $jatuhTempo->translatedFormat('d-F-Y') }}</strong>
        </div>

        @if(!empty($hasNewerPembetulan) && !empty($latestPembetulan) && !empty($historicalBillingNote))
        <div class="historical-note">
            <span class="bold">CATATAN HISTORIS BILLING</span><br>
            {{ $historicalBillingNote }}
        </div>
        @endif

        {{-- JENIS PAJAK --}}
        <table style="margin-bottom: 2px;">
            <tr>
                <td rowspan="3" style="width: 20%; font-weight: bold; vertical-align: middle; text-align: center;">Jenis Pajak</td>
                <td style="width: 20%; font-weight: bold;">Kode Rekening</td>
                <td colspan="2">
                    {{ substr($tax->jenisPajak->kode, 0, 1) }}.{{ substr($tax->jenisPajak->kode, 1, 1) }}.{{ substr($tax->jenisPajak->kode, 2, 1) }}.{{ substr($tax->jenisPajak->kode, 3) }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Jenis Pajak</td>
                <td colspan="2">
                    @php
                        $isSarangWaletTax = !empty($isSarangWalet);
                        $namaJenis = strtoupper($tax->jenisPajak->nama);
                        if ($isSarangWaletTax) {
                            $labelJenisPajak = $namaJenis;
                        } else {
                            $labelJenisPajak = str_starts_with($namaJenis, 'PBJT') ? $namaJenis : 'PBJT ' . $namaJenis;
                        }
                    @endphp
                    {{ $labelJenisPajak }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Jenis Usaha</td>
                <td colspan="2">
                    {{ strtoupper($tax->subJenisPajak->nama ?? $taxObject->nama_objek_pajak ?? '-') }}
                </td>
            </tr>
        </table>

        {{-- WAJIB PAJAK --}}
        <table style="margin-bottom: 2px; border-top: none;">
            <tr>
                <td rowspan="3" style="width: 20%; font-weight: bold; vertical-align: middle; text-align: center;">Wajib Pajak</td>
                <td style="width: 20%; font-weight: bold;">NPWPD</td>
                <td colspan="2">{{ $wajibPajak->npwpd ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Nama Wajib Pajak</td>
                <td colspan="2">{{ strtoupper($wajibPajak->nama_lengkap ?? '-') }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Alamat Wajib Pajak</td>
                <td colspan="2">{{ $wajibPajak->alamat ?? '-' }}</td>
            </tr>
        </table>

        {{-- OBJEK PAJAK --}}
        <table style="margin-bottom: 2px; border-top: none;">
            <tr>
                <td rowspan="3" style="width: 20%; font-weight: bold; vertical-align: middle; text-align: center;">Objek Pajak</td>
                <td style="width: 20%; font-weight: bold;">NOPD</td>
                <td colspan="2">{{ $taxObject->nopd ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Nama Usaha</td>
                <td colspan="2">{{ strtoupper($taxObject->nama_objek_pajak ?? '-') }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Alamat Usaha</td>
                <td colspan="2">{{ $taxObject->alamat_objek ?? '-' }}</td>
            </tr>
        </table>

        {{-- HITUNGAN --}}
        @php
            $omzet = (float) $tax->omzet;
            $tarif = (float) $tax->tarif_persentase;
            $pokokPajak = (float) $tax->amount;
            $opsenAmount = (float) ($tax->opsen ?? 0);
            $isMblbTax = !empty($isMblb);
            $isSarangWaletTax = !empty($isSarangWalet);
            $isPpjTax = ($tax->jenisPajak->kode ?? '') === '41105';
            $labelDpp = $isPpjTax ? 'Nilai Jual Tenaga Listrik (NJTL)' : 'Jumlah Omzet/Penerimaan Bruto';
            $isPembetulanBayar = isset($pembetulanKe) && $pembetulanKe > 0 && isset($parentPaid) && $parentPaid;
            $kredit = (float) ($kreditPajak ?? 0);
            $sisaBayar = $pokokPajak + $opsenAmount - $kredit;
            $jumlahBayar = $isPembetulanBayar ? $sisaBayar : ($pokokPajak + $opsenAmount);
        @endphp

        @if($isMblbTax && isset($mblbDetails) && $mblbDetails->count() > 0)
            {{-- MBLB: Detail mineral table --}}
            <table style="margin-bottom: 0;">
                <tr class="center bold" style="font-size: 9pt;">
                    <td style="width: 5%;">No</td>
                    <td style="width: 30%;">Jenis MBLB</td>
                    <td style="width: 20%;">Harga Patokan (Rp)</td>
                    <td style="width: 15%;">Volume (m&sup3;)</td>
                    <td style="width: 30%;">Subtotal DPP (Rp)</td>
                </tr>
                @foreach($mblbDetails as $i => $detail)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $detail->jenis_mblb }}</td>
                    <td class="right">{{ number_format((float) $detail->harga_patokan, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $detail->volume, 2, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $detail->subtotal_dpp, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="bold">
                    <td colspan="4" class="right">Total DPP</td>
                    <td class="right">{{ number_format($omzet, 0, ',', '.') }}</td>
                </tr>
            </table>

            @if($isPembetulanBayar)
            <table style="margin-bottom: 0; border-top: none;">
                <tr class="center bold">
                    <td style="width: 15%;">Komponen</td>
                    <td style="width: 60%;">Uraian</td>
                    <td style="width: 25%;">Nominal (Rp)</td>
                </tr>
                <tr>
                    <td class="center bold">DPP</td>
                    <td style="font-weight: bold;">1. Total DPP Mineral Pembetulan</td>
                    <td class="right">{{ number_format($omzet, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td rowspan="4" class="center bold" style="vertical-align: middle;">Pajak</td>
                    <td style="font-weight: bold;">2. Tarif Pajak MBLB <br><span
                            style="font-weight: normal; font-size: 9pt;">(Sesuai Perda No 8 Tahun 2025)</span></td>
                    <td class="right">{{ $tarif }}%</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">3. Pokok Pajak MBLB Pembetulan (1 x 2)</td>
                    <td class="right">{{ number_format($pokokPajak, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">4. Opsen Pajak MBLB (25% x 3)</td>
                    <td class="right">{{ number_format($opsenAmount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">5. Pajak yang telah Dibayar (Kredit Pajak)</td>
                    <td class="right">{{ number_format($kredit, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="center bold">Jumlah</td>
                    <td style="font-weight: bold;">6. Pajak yang masih harus dibayar (3+4-5)</td>
                    <td class="right bold">{{ number_format($sisaBayar, 0, ',', '.') }}</td>
                </tr>
            </table>
            @else
            <table style="margin-bottom: 0; border-top: none;">
                <tr class="center bold">
                    <td style="width: 15%;">Komponen</td>
                    <td style="width: 60%;">Uraian</td>
                    <td style="width: 25%;">Nominal (Rp)</td>
                </tr>
                <tr>
                    <td class="center bold">DPP</td>
                    <td style="font-weight: bold;">1. Total DPP Mineral</td>
                    <td class="right">{{ number_format($omzet, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td rowspan="3" class="center bold" style="vertical-align: middle;">Pajak</td>
                    <td style="font-weight: bold;">2. Tarif Pajak MBLB <br><span
                            style="font-weight: normal; font-size: 9pt;">(Sesuai Perda No 8 Tahun 2025)</span></td>
                    <td class="right">{{ $tarif }}%</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">3. Pokok Pajak MBLB (1 x 2)</td>
                    <td class="right">{{ number_format($pokokPajak, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">4. Opsen Pajak MBLB (25% x 3)</td>
                    <td class="right">{{ number_format($opsenAmount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="center bold">Jumlah</td>
                    <td style="font-weight: bold;">5. Total yang harus dibayar (3+4)</td>
                    <td class="right bold">{{ number_format($pokokPajak + $opsenAmount, 0, ',', '.') }}</td>
                </tr>
            </table>
            @endif

        @elseif($isSarangWaletTax && isset($sarangWaletDetail) && $sarangWaletDetail)
            {{-- SARANG WALET: Detail table --}}
            <table style="margin-bottom: 0;">
                <tr class="center bold" style="font-size: 9pt;">
                    <td style="width: 5%;">No</td>
                    <td style="width: 30%;">Jenis Sarang</td>
                    <td style="width: 20%;">Harga Patokan (Rp/kg)</td>
                    <td style="width: 15%;">Volume (kg)</td>
                    <td style="width: 30%;">DPP (Rp)</td>
                </tr>
                <tr>
                    <td class="center">1</td>
                    <td>{{ $sarangWaletDetail->jenis_sarang }}</td>
                    <td class="right">{{ number_format((float) $sarangWaletDetail->harga_patokan, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $sarangWaletDetail->volume_kg, 2, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $sarangWaletDetail->subtotal_dpp, 0, ',', '.') }}</td>
                </tr>
                <tr class="bold">
                    <td colspan="4" class="right">Total DPP</td>
                    <td class="right">{{ number_format($omzet, 0, ',', '.') }}</td>
                </tr>
            </table>

            @if($isPembetulanBayar)
            <table style="margin-bottom: 0; border-top: none;">
                <tr class="center bold">
                    <td style="width: 15%;">Komponen</td>
                    <td style="width: 60%;">Uraian</td>
                    <td style="width: 25%;">Nominal (Rp)</td>
                </tr>
                <tr>
                    <td class="center bold">DPP</td>
                    <td style="font-weight: bold;">1. Total DPP Sarang Walet Pembetulan</td>
                    <td class="right">{{ number_format($omzet, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td rowspan="2" class="center bold" style="vertical-align: middle;">Pajak</td>
                    <td style="font-weight: bold;">2. Tarif Pajak Sarang Burung Walet <br><span
                            style="font-weight: normal; font-size: 9pt;">(Sesuai Perda No 8 Tahun 2025)</span></td>
                    <td class="right">{{ $tarif }}%</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">3. Pokok Pajak Sarang Walet Pembetulan (1 x 2)</td>
                    <td class="right">{{ number_format($pokokPajak, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">4. Pajak yang telah Dibayar (Kredit Pajak)</td>
                    <td class="right">{{ number_format($kredit, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="center bold">Jumlah</td>
                    <td style="font-weight: bold;">5. Pajak yang masih harus dibayar (3-4)</td>
                    <td class="right bold">{{ number_format($sisaBayar, 0, ',', '.') }}</td>
                </tr>
            </table>
            @else
            <table style="margin-bottom: 0; border-top: none;">
                <tr class="center bold">
                    <td style="width: 15%;">Komponen</td>
                    <td style="width: 60%;">Uraian</td>
                    <td style="width: 25%;">Nominal (Rp)</td>
                </tr>
                <tr>
                    <td class="center bold">DPP</td>
                    <td style="font-weight: bold;">1. Total DPP Sarang Walet</td>
                    <td class="right">{{ number_format($omzet, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td rowspan="2" class="center bold" style="vertical-align: middle;">Pajak</td>
                    <td style="font-weight: bold;">2. Tarif Pajak Sarang Burung Walet <br><span
                            style="font-weight: normal; font-size: 9pt;">(Sesuai Perda No 8 Tahun 2025)</span></td>
                    <td class="right">{{ $tarif }}%</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">3. Pokok Pajak Sarang Walet (1 x 2)</td>
                    <td class="right">{{ number_format($pokokPajak, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="center bold">Jumlah</td>
                    <td style="font-weight: bold;">4. Total yang harus dibayar</td>
                    <td class="right bold">{{ number_format($pokokPajak, 0, ',', '.') }}</td>
                </tr>
            </table>
            @endif

        @elseif($isPembetulanBayar)
            {{-- FORMAT PEMBETULAN (parent sudah dibayar) --}}
            <table style="margin-bottom: 0;">
                <tr class="center bold">
                    <td style="width: 15%;">Komponen</td>
                    <td style="width: 60%;">Uraian</td>
                    <td style="width: 25%;">Nominal (Rp)</td>
                </tr>
                <tr class="center" style="font-size: 8pt;">
                    <td>(1)</td>
                    <td>(2)</td>
                    <td>(3)</td>
                </tr>
                <tr>
                    <td class="center bold">DPP</td>
                    <td style="font-weight: bold;">1. {{ $labelDpp }} Pembetulan</td>
                    <td class="right">{{ number_format($omzet, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td rowspan="3" class="center bold" style="vertical-align: middle;">Pajak</td>
                    <td style="font-weight: bold;">2. Tarif Pajak <br><span
                            style="font-weight: normal; font-size: 9pt;">(Sesuai Perda No 8 Tahun 2025)</span></td>
                    <td class="right">{{ $tarif }}%</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">3. Pokok Pajak Pembetulan (1 x 2)</td>
                    <td class="right">{{ number_format($pokokPajak, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">4. Pokok Pajak yang telah Dibayar (Kredit Pajak)</td>
                    <td class="right">{{ number_format($kredit, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="center bold">Jumlah</td>
                    <td style="font-weight: bold;">5. Pokok Pajak yang masih harus dibayar (3-4)</td>
                    <td class="right bold">{{ number_format($sisaBayar, 0, ',', '.') }}</td>
                </tr>
            </table>
        @else
            {{-- FORMAT NORMAL --}}
            <table style="margin-bottom: 0;">
                <tr class="center bold">
                    <td style="width: 15%;">Komponen</td>
                    <td style="width: 60%;">Uraian</td>
                    <td style="width: 25%;">Nominal (Rp)</td>
                </tr>
                <tr class="center" style="font-size: 8pt;">
                    <td>(1)</td>
                    <td>(2)</td>
                    <td>(3)</td>
                </tr>
                <tr>
                    <td class="center bold">DPP</td>
                    <td style="font-weight: bold;">1. {{ $labelDpp }}</td>
                    <td class="right">{{ number_format($omzet, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td rowspan="2" class="center bold" style="vertical-align: middle;">Pajak</td>
                    <td style="font-weight: bold;">2. Tarif Pajak <br><span
                            style="font-weight: normal; font-size: 9pt;">(Sesuai Perda No 8 Tahun 2025)</span></td>
                    <td class="right">{{ $tarif }}%</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">3. Pokok Pajak (1 x 2)</td>
                    <td class="right">{{ number_format($pokokPajak, 0, ',', '.') }}</td>
                </tr>
            </table>
        @endif

        {{-- TERBILANG --}}
        <div class="terbilang">
            <strong>TERBILANG:</strong>
            {{ ucwords(\NumberFormatter::create('id_ID', \NumberFormatter::SPELLOUT)->format(abs($jumlahBayar))) }}
            Rupiah
        </div>

        {{-- KETERANGAN --}}
        <div style="border: 1px solid #000; border-top: none; padding: 4px; display: flex;">
            <div style="width: 50px; font-weight: bold; text-align: center;">Ket.</div>
            <div style="border-left: 1px solid #000; padding-left: 10px; flex: 1;">
                @if(($isMblbTax ?? false) && $tax->subJenisPajak && $tax->subJenisPajak->kode === 'MBLB_WAPU')
                    {{-- MBLB WAPU: hanya keterangan input petugas --}}
                    {{ $tax->notes }}
                @else
                    @if($isSarangWaletTax ?? false)
                        PAJAK SARANG BURUNG WALET
                    @elseif($isMblbTax ?? false)
                        PAJAK MBLB (MINERAL BUKAN LOGAM DAN BATUAN)
                    @else
                        PBJT {{ strtoupper($tax->jenisPajak->nama) }}
                    @endif
                    @if($tax->masa_pajak_bulan)
                        MASA
                        {{ strtoupper(\Carbon\Carbon::create(null, $tax->masa_pajak_bulan)->translatedFormat('F')) }}
                    @else
                        TAHUN
                    @endif
                    {{ $tax->masa_pajak_tahun }}
                    @if($tax->notes)
                        <br>{{ $tax->notes }}
                    @endif
                @endif
            </div>
        </div>

        {{-- TEMPAT PEMBAYARAN --}}
        <table style="margin-top: 0; border-top: none;">
            <tr>
                <td style="width: 30%; font-weight: bold; vertical-align: middle;">Tempat Pembayaran</td>
                <td style="width: 40%;">Bank Jatim / BNI / QRIS / Tokopedia / Indomaret / Alfamart</td>
                <td style="width: 30%; text-align: center; font-weight: bold;">{{ $tax->billing_code }}</td>
            </tr>
        </table>

        {{-- CATATAN --}}
        <div style="border: 1px solid #000; border-top: none; padding: 6px; font-size: 9pt;">
            <strong><em>Catatan</em></strong><br>
            <span style="text-align: justify; display: block;">
                Apabila terdapat kesalahan dalam isian Kode Billing atau masa berlakunya berakhir, Kode Billing dapat
                dibuat
                kembali. Tanggung jawab isian Kode Billing ada pada Wajib Pajak yang namanya tercantum di dalamnya.
            </span>
        </div>

        {{-- FOOTER: CEK STATUS PEMBAYARAN --}}
        <table class="footer-table" style="width: 100%; margin-top: 10px;">
            <tr>
                <td style="width: 60%;"></td>
                <td style="width: 40%; text-align: center;">
                    <strong>Cek Status Pembayaran</strong>
                    <div style="margin: 5px auto; width: 80px; height: 80px;">
                        <img src="data:image/svg+xml;base64, {{ base64_encode((new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer(new \BaconQrCode\Renderer\RendererStyle\RendererStyle(100), new \BaconQrCode\Renderer\Image\SvgImageBackEnd())))->writeString(route('portal.billing.check-status', $tax->id))) }}"
                            alt="QR Code" style="width: 80px; height: 80px;">
                    </div>
                </td>
            </tr>
        </table>

        </div>
    </div>
</body>

</html>