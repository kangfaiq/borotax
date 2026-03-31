<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STPD - BoroTax</title>
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

        /* --- KOP SURAT --- */
        .kop-table td {
            border: 1px solid #000;
            vertical-align: middle;
        }

        /* --- TABLE --- */
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

        .section-title {
            font-weight: bold;
            margin: 10px 0 4px 0;
            font-size: 10pt;
        }

        .info-table td {
            border: none;
            padding: 1px 4px;
            font-size: 10pt;
        }

        .terbilang {
            font-style: italic;
            padding: 4px 6px;
            border: 1px solid #000;
            border-top: none;
            font-size: 10pt;
        }

        .footer-table td {
            border: none;
        }

        /* --- Print --- */
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
    <div class="page">
        {{-- === KOP SURAT === --}}
        <table class="kop-table" style="margin-bottom: 2px;">
            <tr>
                <td style="width: 20%; text-align: center; border-right: none;">
                    <img src="{{ $isPdf ? public_path('images/logo-pemkab.png') : asset('images/logo-pemkab.png') }}"
                        style="width: 130px;">
                </td>
                <td style="width: 60%; text-align: center; border-left: none; border-right: none;">
                    <h3 style="margin: 0; font-size: 12pt;">PEMERINTAH KABUPATEN BOJONEGORO</h3>
                    <h2 style="margin: 0; font-size: 14pt;">BADAN PENDAPATAN DAERAH</h2>
                    <p style="margin: 0; font-size: 9pt;">Jl. P. Mas Tumapel No.1 Telepon (0353) 881826</p>
                    <p style="margin: 0; font-size: 8pt;">Narahubung Layanan: 081333688233 (PBB) ; 085173023368
                        (BPHTB);<br>085172330531 (PDL1); 085172240531 (PDL2); 082233099997 (PDL3)</p>
                    <div
                        style="font-weight: bold; font-size: 14pt; letter-spacing: 2px; margin-top: 2px; text-decoration:underline;">
                        BOJONEGORO</div>
                </td>
                <td style="width: 20%; border-left: none;"></td>
            </tr>
            <tr>
                <td colspan="3" class="center bold" style="font-size: 13pt; padding: 6px;">
                    SURAT TAGIHAN PAJAK DAERAH (STPD)
                </td>
            </tr>
        </table>

        {{-- === NOMOR & MASA PAJAK === --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="width: 30%; border-top: none;">Nomor Dokumen</td>
                <td style="border-top: none;">: {{ $stpdDocumentNumber ?? $tax->stpd_number ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kode Billing Penagihan</td>
                <td>: {{ $stpdPaymentCode ?? $tax->billing_code ?? '-' }}</td>
            </tr>
            <tr>
                <td>Masa/Tahun Pajak</td>
                <td>:
                    {{ $tax->masa_pajak_bulan ? \Carbon\Carbon::create()->month($tax->masa_pajak_bulan)->translatedFormat('F') : '-' }}
                    {{ $tax->masa_pajak_tahun }}
                </td>
            </tr>
        </table>

        {{-- === SECTION I: DASAR HUKUM === --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td colspan="4" style="border-top: none; text-align: justify; padding: 6px;">
                    <strong>I.</strong>&nbsp;&nbsp;Berdasarkan Pasal 131 Peraturan Daerah Kabupaten Bojonegoro Nomor 5
                    Tahun 2023 tentang Pajak
                    Daerah dan Retribusi Daerah sebagaimana telah diubah dengan Peraturan Daerah Kabupaten
                    Bojonegoro Nomor 8 Tahun 2025, atas pelaksanaan kewajiban Pajak Daerah dari:
                </td>
            </tr>
        </table>

        {{-- Wajib Pajak --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td rowspan="3" style="width: 15%; font-weight: bold; vertical-align: middle; border-top: none;">
                    Wajib<br>Pajak</td>
                <td style="width: 22%; font-weight: bold; border-top: none;">NPWPD</td>
                <td style="border-top: none;">{{ $wajibPajak->npwpd ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Nama Wajib Pajak</td>
                <td>{{ strtoupper($wajibPajak->nama_lengkap ?? '-') }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Alamat</td>
                <td>{{ $wajibPajak->alamat ?? '-' }}</td>
            </tr>
        </table>

        {{-- Objek Pajak --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td rowspan="3" style="width: 15%; font-weight: bold; vertical-align: middle; border-top: none;">
                    Objek<br>Pajak</td>
                <td style="width: 22%; font-weight: bold; border-top: none;">NOPD</td>
                <td style="border-top: none;">{{ $taxObject->nopd ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Nama Objek Pajak</td>
                <td>{{ strtoupper($taxObject->nama_objek_pajak ?? '-') }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Alamat Objek Pajak</td>
                <td>{{ $taxObject->alamat_objek ?? '-' }}</td>
            </tr>
        </table>

        {{-- === SECTION II: PERHITUNGAN === --}}
        @php
            $isPembetulan = isset($pembetulanKe) && $pembetulanKe > 0;
            $isMblbTax = !empty($isMblb);
            $pokokPajak = (float) $tax->amount;
            $opsenAmount = (float) ($tax->opsen ?? 0);
            $kredit = (float) ($kreditPajak ?? 0);

            $totalPajak = $pokokPajak + $opsenAmount;
            $telahDibayar = $isPembetulan ? $kredit : $totalPajak;
            $masihHarusDibayarPokok = $totalPajak - $telahDibayar;
            $sanksiNominal = (float) ($sanksi ?? 0);
            // OPD tidak dikenakan denda
            if ($taxObject && $taxObject->is_opd) {
                $sanksiNominal = 0;
            }
            $jumlahBayar = $masihHarusDibayarPokok + $sanksiNominal;

            // Tanggal bayar pokok pajak
            $tglBayarPokok = $tax->paid_at ? \Carbon\Carbon::parse($tax->paid_at)->format('d/m/Y') : '-';

            // Hitung bulan terlambat & tarif sanksi dari jatuh tempo vs tanggal bayar
            $masaPajak = \Carbon\Carbon::create($tax->masa_pajak_tahun, $tax->masa_pajak_bulan, 1);
            $jatuhTempo = \App\Domain\Tax\Models\Tax::hitungJatuhTempoSelfAssessment(
                (int) $tax->masa_pajak_bulan,
                (int) $tax->masa_pajak_tahun
            );
            $tanggalBayar = $tax->paid_at ? \Carbon\Carbon::parse($tax->paid_at) : \Carbon\Carbon::now();
            $bulanTerlambat = ($taxObject && $taxObject->is_opd) ? 0 : \App\Domain\Tax\Models\Tax::hitungBulanTerlambat($jatuhTempo, $tanggalBayar);
            $tarifSanksi = ($taxObject && $taxObject->is_opd) ? 0 : \App\Domain\Tax\Models\Tax::getTarifSanksi($masaPajak);
            $tarifPersen = (int) ($tarifSanksi * 100);
        @endphp

        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td colspan="3" style="border-top: none; padding: 6px;">
                    <strong>II.</strong>&nbsp;&nbsp;Perhitungan jumlah yang masih harus dibayar adalah sebagai berikut:
                </td>
            </tr>
        </table>

        @if($isMblbTax && isset($mblbDetails) && $mblbDetails->count() > 0)
        {{-- MBLB mineral detail --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr class="center bold" style="font-size: 9pt;">
                <td style="width: 5%; border-top: none;">No</td>
                <td style="width: 30%; border-top: none;">Jenis MBLB</td>
                <td style="width: 20%; border-top: none;">Harga Patokan (Rp)</td>
                <td style="width: 15%; border-top: none;">Volume (m&sup3;)</td>
                <td style="width: 30%; border-top: none;">Subtotal DPP (Rp)</td>
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
                <td class="right">{{ number_format((float) $tax->omzet, 0, ',', '.') }}</td>
            </tr>
        </table>
        @endif

        <table style="margin-bottom: 0; border-top: none;">
            <tr class="center bold">
                <td style="width: 15%; border-top: none;">Komponen</td>
                <td style="width: 55%; border-top: none;">Uraian</td>
                <td style="width: 30%; border-top: none;">Nominal (Rp)</td>
            </tr>
            <tr class="center" style="font-size: 8pt;">
                <td>(1)</td>
                <td>(2)</td>
                <td>(3)</td>
            </tr>
            <tr>
                <td rowspan="{{ $isMblbTax ? 4 : 3 }}" class="center bold" style="vertical-align: middle;">Pokok<br>Pajak</td>
                <td>1. &nbsp;Pokok Pajak yang harus dibayar</td>
                <td class="right">{{ number_format($pokokPajak, 0, ',', '.') }}</td>
            </tr>
            @if($isMblbTax)
            <tr>
                <td>2. &nbsp;Opsen Pajak MBLB (25% x 1)</td>
                <td class="right">{{ number_format($opsenAmount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>3. &nbsp;Total Pajak yang telah dibayar<br>
                    <span style="font-size: 9pt; font-style: italic;">&nbsp;&nbsp;&nbsp;&nbsp;(tanggal bayar pokok
                        pajak: {{ $tglBayarPokok }})</span>
                </td>
                <td class="right">{{ number_format($telahDibayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>4. &nbsp;Pajak yang masih harus dibayar (1+2-3)</td>
                <td class="right">{{ number_format($masihHarusDibayarPokok, 0, ',', '.') }}</td>
            </tr>
            @else
            <tr>
                <td>2. &nbsp;Pokok Pajak yang telah dibayar<br>
                    <span style="font-size: 9pt; font-style: italic;">&nbsp;&nbsp;&nbsp;&nbsp;(tanggal bayar pokok
                        pajak: {{ $tglBayarPokok }})</span>
                </td>
                <td class="right">{{ number_format($telahDibayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>3. &nbsp;Pokok Pajak yang masih harus dibayar</td>
                <td class="right">{{ number_format($masihHarusDibayarPokok, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td class="center bold" style="vertical-align: middle;">Sanksi</td>
                <td>{{ $isMblbTax ? '5' : '4' }}. &nbsp;Sanksi administratif bunga Pasal 131<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;ayat (4) Perda 5/2023 jo. Perda 8/2025<br>
                    <span style="font-size: 9pt; font-style: italic;">&nbsp;&nbsp;&nbsp;&nbsp;({{ $tarifPersen }}% x
                        {{ $bulanTerlambat }}bln x Pokok)</span>
                </td>
                <td class="right">{{ number_format($sanksiNominal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="center bold" style="vertical-align: middle;">Jumlah</td>
                <td class="bold">{{ $isMblbTax ? '6' : '5' }}. &nbsp;Jumlah yang masih harus dibayar ({{ $isMblbTax ? '4+5' : '3+4' }})</td>
                <td class="right bold">{{ number_format($jumlahBayar, 0, ',', '.') }}</td>
            </tr>
        </table>

        {{-- TERBILANG --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="border-top: none; padding: 6px;">
                    <strong>Terbilang</strong> &nbsp;:&nbsp;
                    {{ ucwords(\NumberFormatter::create('id_ID', \NumberFormatter::SPELLOUT)->format(abs($jumlahBayar))) }}
                    Rupiah
                </td>
            </tr>
        </table>

        {{-- TANGGAL BAYAR --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="width: 30%; font-weight: bold; border-top: none;">Tanggal Bayar STPD</td>
                <td style="border-top: none;">:
                    {{ $tax->payment_expired_at ? \Carbon\Carbon::parse($tax->payment_expired_at)->translatedFormat('d F Y') : '-' }}
                </td>
            </tr>
        </table>

        {{-- === SECTION III: PERHATIAN === --}}
        <table style="border-top: none;">
            <tr>
                <td style="border-top: none; padding: 6px;">
                    <strong>III. Perhatian</strong><br>
                    @if(isset($stpdManual) && $stpdManual->tipe === 'pokok_sanksi')
                    Terdapat pokok pajak yang belum dibayar sebesar <strong>Rp {{ number_format((float) $stpdManual->pokok_belum_dibayar, 0, ',', '.') }}</strong>
                    beserta sanksi administratif sebesar <strong>Rp {{ number_format((float) $stpdManual->sanksi_dihitung, 0, ',', '.') }}</strong>
                    (proyeksi s.d. tanggal {{ $stpdManual->proyeksi_tanggal_bayar?->translatedFormat('d F Y') ?? '-' }}).
                    Wajib Pajak diharapkan segera melunasi seluruh tagihan pajak daerah tersebut.
                    @elseif(!empty($isSanksiBelumLunas) && ($sanksiBelumDibayar ?? 0) > 0)
                    Terdapat sanksi administratif yang belum terbayarkan sebesar <strong>Rp {{ number_format($sanksiBelumDibayar, 0, ',', '.') }}</strong>.
                    Wajib Pajak diharapkan segera melunasi sanksi tersebut.
                    @else
                    Dokumen ini merupakan rincian pelunasan sanksi administratif pajak daerah yang telah terbayar.
                    @endif
                </td>
            </tr>
        </table>

        {{-- === TANDA TANGAN === --}}
        <table class="footer-table" style="width: 100%; margin-top: 15px;">
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%; text-align: center; font-size: 10pt;">
                    Bojonegoro, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    <strong>KEPALA BADAN PENDAPATAN DAERAH</strong><br>
                    <strong>KABUPATEN BOJONEGORO</strong>
                    <div style="margin: 12px auto; font-style: italic; font-size: 9pt;">
                        Ditandatangani secara elektronik (TTE)
                    </div>
                    @if($pimpinan)
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ strtoupper($pimpinan->nama) }}
                        </div>
                        <div style="font-size: 9pt;">{{ $pimpinan->pangkat }}</div>
                        <div style="font-size: 9pt;">NIP. {{ $pimpinan->nip }}</div>
                    @else
                        <div style="font-weight: bold; text-decoration: underline;">
                            ..........................................
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>

</html>