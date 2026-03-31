<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ strtoupper($letter->letter_type->value) }} - BoroTax</title>
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

        .kop-table td {
            border: 1px solid #000;
            vertical-align: middle;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }

        td, th {
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

        .no-border { border: none !important; }
        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }

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
    @php
        $baseAmount      = (float) ($letter->base_amount ?? 0);
        $taxPaid         = (float) ($tax?->amount ?? 0);
        // Untuk SKPDKBT, sudah dibayar = billing awal + billing turunan SKPDKB sebelumnya
        if ($letter->letter_type === \App\Enums\TaxAssessmentLetterType::SKPDKBT) {
            $taxPaid += (float) ($letter->parentLetter?->generatedTax?->amount ?? 0);
        }
        $pokokSisa       = $baseAmount - $taxPaid;
        $interestAmount  = (float) ($letter->interest_amount ?? 0);
        $surchargeAmount = (float) ($letter->surcharge_amount ?? 0);
        $totalSanksi     = $interestAmount + $surchargeAmount;
        $totalAssessment = (float) ($letter->total_assessment ?? 0);

        // SKPDLB-specific
        $pajakSeharusnya  = $taxPaid - $baseAmount; // yang seharusnya terutang
        $totalKompensasi  = $letter->compensations?->sum(fn($c) => (float) ($c->allocation_amount ?? 0)) ?? 0;

        $masaPajak = ($tax?->masa_pajak_bulan && $tax?->masa_pajak_tahun)
            ? \Carbon\Carbon::create($tax->masa_pajak_tahun, $tax->masa_pajak_bulan, 1)->translatedFormat('F Y')
            : '-';

        $fullTitle = match ($letter->letter_type) {
            \App\Enums\TaxAssessmentLetterType::SKPDKB  => 'SURAT KETETAPAN PAJAK DAERAH KURANG BAYAR (SKPDKB)',
            \App\Enums\TaxAssessmentLetterType::SKPDKBT => 'SURAT KETETAPAN PAJAK DAERAH KURANG BAYAR TAMBAHAN (SKPDKBT)',
            \App\Enums\TaxAssessmentLetterType::SKPDLB  => 'SURAT KETETAPAN PAJAK DAERAH LEBIH BAYAR (SKPDLB)',
            \App\Enums\TaxAssessmentLetterType::SKPDN   => 'SURAT KETETAPAN PAJAK DAERAH NIHIL (SKPDN)',
        };
    @endphp

    <div class="page">
        {{-- === KOP SURAT === --}}
        <table class="kop-table" style="margin-bottom: 2px;">
            <tr>
                <td style="width: 20%; text-align: center; border-right: none;">
                    <img src="{{ $isPdf ?? true ? public_path('images/logo-pemkab.png') : asset('images/logo-pemkab.png') }}"
                        style="width: 130px;">
                </td>
                <td style="width: 60%; text-align: center; border-left: none; border-right: none;">
                    <h3 style="margin: 0; font-size: 12pt;">PEMERINTAH KABUPATEN BOJONEGORO</h3>
                    <h2 style="margin: 0; font-size: 14pt;">BADAN PENDAPATAN DAERAH</h2>
                    <p style="margin: 0; font-size: 9pt;">Jl. P. Mas Tumapel No. 1 Telp. (0353) 881826</p>
                    <div style="font-weight: bold; font-size: 14pt; letter-spacing: 2px; margin-top: 2px; text-decoration: underline;">
                        BOJONEGORO</div>
                </td>
                <td style="width: 20%; border-left: none;"></td>
            </tr>
            <tr>
                <td colspan="3" class="center bold" style="font-size: 13pt; padding: 6px;">
                    {{ $fullTitle }}
                </td>
            </tr>
        </table>

        {{-- === NOMOR & MASA === --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="width: 30%; border-top: none;">Nomor</td>
                <td style="border-top: none;">: {{ $letter->document_number ?? '-' }}</td>
            </tr>
            <tr>
                <td>Masa/Tahun Pajak</td>
                <td>: {{ $masaPajak }}</td>
            </tr>
        </table>

        {{-- === SECTION I === --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td colspan="3" style="border-top: none; padding: 6px;">
                    <strong>I.</strong>&nbsp;&nbsp;Telah dilakukan pemeriksaan atas pelaksanaan kewajiban pajak dari:
                </td>
            </tr>
        </table>

        {{-- Wajib Pajak --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td rowspan="3" style="width: 15%; font-weight: bold; vertical-align: middle; border-top: none; text-align: center;">
                    Wajib<br>Pajak</td>
                <td style="width: 27%; font-weight: bold; border-top: none;">NPWPD</td>
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
                <td rowspan="3" style="width: 15%; font-weight: bold; vertical-align: middle; border-top: none; text-align: center;">
                    Objek<br>Pajak</td>
                <td style="width: 27%; font-weight: bold; border-top: none;">NOPD</td>
                <td style="border-top: none;">{{ $taxObject->nopd ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Nama Objek Pajak</td>
                <td>{{ strtoupper($taxObject->nama_usaha ?? $taxObject->nama_objek_pajak ?? '-') }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Alamat Objek Pajak</td>
                <td>{{ $taxObject->alamat_objek ?? '-' }}</td>
            </tr>
        </table>

        @if($letter->letter_type === \App\Enums\TaxAssessmentLetterType::SKPDLB)

        {{-- === SECTION II — SKPDLB === --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td colspan="3" style="border-top: none; padding: 6px;">
                    <strong>II.</strong>&nbsp;&nbsp;Dari pemeriksaan tersebut di atas, terdapat kelebihan pembayaran pajak sebagai berikut:
                </td>
            </tr>
        </table>

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

            {{-- Pokok --}}
            <tr>
                <td rowspan="3" class="center bold" style="vertical-align: middle;">Pokok<br>Pajak</td>
                <td>1. &nbsp;Jumlah pajak yang seharusnya terutang</td>
                <td class="right">{{ number_format($pajakSeharusnya, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>
                    2. &nbsp;Jumlah kredit pajak / pajak yang telah dibayar
                    @if($tax?->paid_at)
                        <br><span style="font-size: 9pt; font-style: italic;">&nbsp;&nbsp;&nbsp;&nbsp;(tanggal bayar: {{ $tax->paid_at->format('d/m/Y') }})</span>
                    @endif
                </td>
                <td class="right">{{ number_format($taxPaid, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bold">3. &nbsp;Kelebihan pembayaran pajak (2-1)</td>
                <td class="right bold">{{ number_format($baseAmount, 0, ',', '.') }}</td>
            </tr>

            {{-- Kompensasi --}}
            @php
                $kompNo = 4;
                $kompensasiList = $letter->compensations ?? collect();
                $kompensasiCount = $kompensasiList->count();
                $kompensasiEmpty = $kompensasiCount === 0;
                $kompensasiRowspan = $kompensasiEmpty ? 1 : ($kompensasiCount + 1);
            @endphp
            @if($kompensasiEmpty)
            <tr>
                <td rowspan="{{ $kompensasiRowspan }}" class="center bold" style="vertical-align: middle;">Kompen&shy;sasi</td>
                <td>{{ $kompNo++ }}. &nbsp;Dikompensasikan ke billing lain</td>
                <td class="right">-</td>
            </tr>
            @else
            @foreach($kompensasiList as $kompensasi)
            <tr>
                @if($loop->first)
                <td rowspan="{{ $kompensasiRowspan }}" class="center bold" style="vertical-align: middle;">Kompen&shy;sasi</td>
                @endif
                <td>{{ $kompNo++ }}. &nbsp;Dikompensasikan ke billing {{ $kompensasi->targetTax?->billing_code ?? '-' }}</td>
                <td class="right">{{ number_format((float) ($kompensasi->allocation_amount ?? 0), 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @endif
            <tr>
                @if($kompensasiEmpty)<td class="center bold" style="vertical-align: middle;"></td>@endif
                <td class="bold">{{ $kompNo }}. &nbsp;Sisa kredit yang tersedia</td>
                <td class="right bold">{{ number_format((float) ($letter->available_credit ?? 0), 0, ',', '.') }}</td>
            </tr>
        </table>

        {{-- TERBILANG --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="border-top: none; padding: 4px 6px;">
                    <strong>Terbilang</strong> &nbsp;:&nbsp;
                    {{ ucwords(\NumberFormatter::create('id_ID', \NumberFormatter::SPELLOUT)->format(abs($baseAmount))) }} Rupiah
                </td>
            </tr>
        </table>

        {{-- === SECTION III — SKPDLB === --}}
        <table style="border-top: none;">
            <tr>
                <td style="border-top: none; padding: 6px;">
                    <strong>III. Perhatian</strong><br>
                    Kelebihan pembayaran pajak sebesar {{ $letter->letter_type->getLabel() }} ini dapat dikompensasikan ke tagihan pajak lain
                    atau dimohonkan restitusi sesuai ketentuan peraturan perundang-undangan yang berlaku.
                    @if($letter->notes)
                        <br><em>{{ $letter->notes }}</em>
                    @endif
                </td>
            </tr>
        </table>

        @else

        {{-- === SECTION II — SKPDKB / SKPDKBT === --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td colspan="3" style="border-top: none; padding: 6px;">
                    <strong>II.</strong>&nbsp;&nbsp;Dari pemeriksaan tersebut di atas, jumlah yang masih harus dibayar adalah sebagai berikut:
                </td>
            </tr>
        </table>

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

            {{-- Pokok Pajak --}}
            <tr>
                <td rowspan="3" class="center bold" style="vertical-align: middle;">Pokok<br>Pajak</td>
                <td>1. &nbsp;Pokok Pajak yang harus dibayar</td>
                <td class="right">{{ number_format($baseAmount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>
                    2. &nbsp;Pokok Pajak yang telah dibayar
                    @if($tax?->paid_at)
                        <br><span style="font-size: 9pt; font-style: italic;">&nbsp;&nbsp;&nbsp;&nbsp;(tanggal bayar pokok pajak: {{ $tax->paid_at->format('d/m/Y') }})</span>
                    @endif
                </td>
                <td class="right">{{ number_format($taxPaid, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>3. &nbsp;Pokok Pajak yang masih harus dibayar</td>
                <td class="right">{{ number_format($pokokSisa, 0, ',', '.') }}</td>
            </tr>

            {{-- Sanksi --}}
            <tr>
                <td rowspan="5" class="center bold" style="vertical-align: middle;">Sanksi</td>
                <td>
                    4. &nbsp;Sanksi administratif bunga Pasal 130 ayat (1)<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;Perda 5/2023 jo. Perda 8/2025
                    @if($letter->interest_rate > 0 && $letter->interest_months > 0)
                        <br><span style="font-size: 9pt; font-style: italic;">&nbsp;&nbsp;&nbsp;&nbsp;({{ number_format((float) $letter->interest_rate, 1, ',', '.') }}% x {{ $letter->interest_months }}bln x Pokok)</span>
                    @endif
                </td>
                <td class="right">{{ number_format($interestAmount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>5. &nbsp;Sanksi administratif bunga Pasal 130 ayat (2)<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;Perda 5/2023 jo. Perda 8/2025</td>
                <td class="right">-</td>
            </tr>
            <tr>
                <td>
                    6. &nbsp;Sanksi administratif kenaikan Pasal 130 ayat (2)<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;huruf a Perda 5/2023 jo. Perda 8/2025
                    @if($letter->surcharge_rate > 0)
                        <br><span style="font-size: 9pt; font-style: italic;">&nbsp;&nbsp;&nbsp;&nbsp;({{ number_format((float) $letter->surcharge_rate, 0, ',', '.') }}% x Pokok)</span>
                    @endif
                </td>
                <td class="right">{{ number_format($surchargeAmount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>7. &nbsp;Sanksi administratif kenaikan Pasal 130 ayat (2)<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;huruf b Perda 5/2023 jo. Perda 8/2025</td>
                <td class="right">-</td>
            </tr>
            <tr>
                <td class="bold">8. &nbsp;Jumlah sanksi administratif (4+5+6+7)</td>
                <td class="right bold">{{ number_format($totalSanksi, 0, ',', '.') }}</td>
            </tr>

            {{-- Jumlah --}}
            <tr>
                <td class="center bold" style="vertical-align: middle;">Jumlah</td>
                <td class="bold">9. &nbsp;Jumlah yang masih harus dibayar (3+8)</td>
                <td class="right bold">{{ number_format($totalAssessment, 0, ',', '.') }}</td>
            </tr>
        </table>

        {{-- TERBILANG --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="border-top: none; padding: 4px 6px;">
                    <strong>Terbilang</strong> &nbsp;:&nbsp;
                    {{ ucwords(\NumberFormatter::create('id_ID', \NumberFormatter::SPELLOUT)->format(abs($totalAssessment))) }} Rupiah
                </td>
            </tr>
        </table>

        {{-- KODE BILLING --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="width: 30%; font-weight: bold; border-top: none;">Kode Billing</td>
                <td style="border-top: none;">: {{ $generatedTax?->billing_code ?? $tax?->billing_code ?? '-' }}</td>
            </tr>
        </table>

        {{-- === SECTION III — SKPDKB/SKPDKBT === --}}
        <table style="border-top: none;">
            <tr>
                <td style="border-top: none; padding: 6px;">
                    <strong>III. Perhatian</strong><br>
                    Jumlah yang masih harus dibayar pada dokumen {{ $letter->letter_type->getLabel() }} ini wajib dilunasi dalam jangka waktu
                    1 (satu) bulan sejak tanggal diterbitkan.
                    @if($letter->notes)
                        <br><em>{{ $letter->notes }}</em>
                    @endif
                </td>
            </tr>
        </table>

        @endif

        {{-- === TANDA TANGAN === --}}
        <table class="footer-table" style="width: 100%; margin-top: 15px;">
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%; text-align: center; font-size: 10pt;">
                    Bojonegoro, {{ $letter->verified_at ? $letter->verified_at->translatedFormat('d F Y') : ($letter->issue_date ? $letter->issue_date->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y')) }}<br>
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