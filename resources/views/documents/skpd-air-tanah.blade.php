<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKPD Air Tanah - {{ $skpd->nomor_skpd }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 9pt;
            color: #000;
            background: #fff;
            line-height: 1.3;
        }

        .page {
            width: auto;
            margin: 0;
            padding: 15px;
            border: 3px solid #000;
            page-break-inside: avoid;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 2px 5px;
            font-size: 9pt;
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
            margin: 10mm 12mm 10mm 12mm;
        }
    </style>
</head>

<body>
    @if($skpd->status === 'draft')
    <div style="position: fixed; top: 35%; left: 10%; width: 80%; text-align: center; z-index: 9999; pointer-events: none;">
        <span style="font-size: 120px; font-weight: bold; color: rgba(255, 0, 0, 0.12); letter-spacing: 20px; transform: rotate(-45deg); display: inline-block; font-family: Arial, sans-serif;">DRAFT</span>
    </div>
    @endif
    @php
        $waterObject = $skpd->waterObject;
        $jenisPajak = $skpd->jenisPajak;
        $subJenisPajak = $skpd->subJenisPajak;

        // Meter & penggunaan
        $meterBefore = (float) $skpd->meter_reading_before;
        $meterAfter = (float) $skpd->meter_reading_after;
        $usage = (float) $skpd->usage;

        // Tarif tiers (JSON encoded di tarif_per_m3)
        $tarifTiers = is_array($skpd->tarif_per_m3)
            ? $skpd->tarif_per_m3
            : json_decode($skpd->tarif_per_m3, true);

        // Fallback: flat rate (backward compatibility)
        if (!is_array($tarifTiers) || empty($tarifTiers)) {
            $tarifTiers = [
                ['min_vol' => 0, 'max_vol' => 99999999, 'npa' => (float) $skpd->tarif_per_m3],
            ];
        }

        // Hitung pemakaian per tier (logika sama dengan BuatSkpdAirTanah::getPreviewPajak)
        $remainingUsage = $usage;
        $tierDetails = [];
        $totalDpp = 0;

        foreach ($tarifTiers as $tier) {
            if ($remainingUsage <= 0) {
                $tierDetails[] = [
                    'min_vol' => $tier['min_vol'],
                    'max_vol' => $tier['max_vol'],
                    'npa' => $tier['npa'],
                    'used' => 0,
                    'npa_total' => 0,
                ];
                continue;
            }

            $maxVolInTier = floatval($tier['max_vol'] - $tier['min_vol'] + 1);
            if ($tier['min_vol'] == 0) {
                $maxVolInTier = floatval($tier['max_vol']);
            }
            if ($tier['max_vol'] == null || $tier['max_vol'] >= 99999999) {
                $maxVolInTier = $remainingUsage;
            }

            $usedInTier = min($remainingUsage, $maxVolInTier);
            $npaTotal = $usedInTier * $tier['npa'];
            $totalDpp += $npaTotal;
            $remainingUsage = round($remainingUsage - $usedInTier, 2);

            $tierDetails[] = [
                'min_vol' => $tier['min_vol'],
                'max_vol' => $tier['max_vol'],
                'npa' => $tier['npa'],
                'used' => $usedInTier,
                'npa_total' => $npaTotal,
            ];
        }

        // Nilai perhitungan
        $dasarPengenaan = (float) ($skpd->dasar_pengenaan ?? $totalDpp);
        $tarifPersen = (float) ($skpd->tarif_persen ?? 20);
        $jumlahPajak = (float) ($skpd->jumlah_pajak ?? 0);

        // Keterangan kriteria SDA
        $kriteriaSdaLabels = [
            '1' => 'Kriteria 1',
            '2' => 'Kriteria 2',
            '3' => 'Kriteria 3',
            '4' => 'Kriteria 4',
        ];
        $kriteriaSda = $waterObject->kriteria_sda ?? null;
        $kriteriaSdaLabel = $kriteriaSdaLabels[$kriteriaSda] ?? ($kriteriaSda ? 'Kriteria ' . $kriteriaSda : '-');

        // Keterangan kelompok pemakaian
        $kelompokLabels = [
            '1' => 'Kelompok 1',
            '2' => 'Kelompok 2',
            '3' => 'Kelompok 3',
            '4' => 'Kelompok 4',
            '5' => 'Kelompok 5',
        ];
        $kelompokPemakaian = $waterObject->kelompok_pemakaian ?? null;
        $kelompokLabel = $kelompokLabels[$kelompokPemakaian] ?? ($kelompokPemakaian ? 'Kelompok ' . $kelompokPemakaian : '-');

        $usesMeter = (bool) ($waterObject->uses_meter ?? ($skpd->meter_reading_before !== null || $skpd->meter_reading_after !== null));
        $objectTypeLabel = $usesMeter ? 'Objek Meter Air' : 'Objek Non Meter Air';

        // Kode rekening: format X.X.X.XX berdasarkan kode jenis pajak
        $kodeJenisPajak = $jenisPajak->kode ?? '41108';
        $kodeRekening = substr($kodeJenisPajak, 0, 1) . '.' . substr($kodeJenisPajak, 1, 1) . '.' . substr($kodeJenisPajak, 2, 1) . '.' . substr($kodeJenisPajak, 3);

        // Kohir: 8 digit terakhir dari kode billing
        $kohir = $skpd->kode_billing ? substr($skpd->kode_billing, -8) : '-';

        // Masa pajak dari periode_bulan (format: YYYY-MM)
        $masaPajak = $skpd->periode_bulan
            ? \Carbon\Carbon::createFromFormat('Y-m', $skpd->periode_bulan)->translatedFormat('F Y')
            : '-';

        // Format label rentang tier
        if (!function_exists('formatTierRange')) {
            function formatTierRange($minVol, $maxVol): string {
                if ($maxVol === null || $maxVol >= 99999999) {
                    return '> ' . number_format($minVol, 0, ',', '.');
                }
                return number_format($minVol, 0, ',', '.') . ' – ' . number_format($maxVol, 0, ',', '.');
            }
        }
    @endphp

    <div class="page">
        {{-- === KOP SURAT === --}}
        <table class="kop-table" style="margin-bottom: 0;">
            <tr>
                <td rowspan="2" style="width: 12%; text-align: center;">
                    <img src="{{ $isPdf ? public_path('images/logo-pemkab.png') : asset('images/logo-pemkab.png') }}"
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
                    {{ $masaPajak }}
                </td>
            </tr>
            <tr>
                <td class="center bold" style="font-size: 12pt; padding: 6px;">
                    SURAT KETETAPAN PAJAK DAERAH (SKPD)
                </td>
                <td style="text-align: center; font-size: 9pt; vertical-align: middle;">
                    <strong>Kohir:</strong><br>
                    {{ $kohir }}
                </td>
            </tr>
        </table>

        {{-- === JENIS PAJAK === --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td rowspan="2" style="width: 12%; font-weight: bold; vertical-align: middle; text-align: center; border-top: none;">
                    Jenis<br>Pajak</td>
                <td style="width: 22%; font-weight: bold; border-top: none;">Kode Rekening</td>
                <td style="border-top: none;">{{ $kodeRekening }}</td>
            </tr>
            <tr>
                <td class="bold">Jenis Pajak</td>
                <td>PAJAK AIR TANAH</td>
            </tr>
        </table>

        {{-- === WAJIB PAJAK === --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td rowspan="3" style="width: 12%; font-weight: bold; vertical-align: middle; text-align: center; border-top: none;">
                    Wajib<br>Pajak</td>
                <td style="width: 22%; font-weight: bold; border-top: none;">NPWPD</td>
                <td style="border-top: none;">{{ $waterObject->npwpd ?? '-' }}</td>
            </tr>
            <tr>
                <td class="bold">Nama Wajib Pajak</td>
                <td>{{ strtoupper($skpd->nama_wajib_pajak) }}</td>
            </tr>
            <tr>
                <td class="bold">Alamat Wajib Pajak</td>
                <td>{{ $skpd->alamat_wajib_pajak }}</td>
            </tr>
        </table>

        {{-- === OBJEK PAJAK === --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td rowspan="6" style="width: 12%; font-weight: bold; vertical-align: middle; text-align: center; border-top: none;">
                    Objek<br>Pajak</td>
                <td style="width: 22%; font-weight: bold; border-top: none;">NOPD</td>
                <td style="border-top: none;">{{ $skpd->nopd ?? '-' }}</td>
            </tr>
            <tr>
                <td class="bold">Nama Objek</td>
                <td>{{ strtoupper($skpd->nama_objek) }}</td>
            </tr>
            <tr>
                <td class="bold">Alamat Objek</td>
                <td>{{ $skpd->alamat_objek }}</td>
            </tr>
            <tr>
                <td class="bold">Jenis Objek Air Tanah</td>
                <td>{{ $objectTypeLabel }}</td>
            </tr>
            <tr>
                <td class="bold">Kriteria SDA</td>
                <td>{{ $kriteriaSdaLabel }}</td>
            </tr>
            <tr>
                <td class="bold">Kelompok</td>
                <td>{{ $kelompokLabel }}</td>
            </tr>
        </table>

        {{-- === PERHITUNGAN (5-column table for tier data) === --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr class="center bold">
                <td style="width: 10%; border-top: none;">Komponen</td>
                <td colspan="3" style="width: 58%; border-top: none;">Uraian</td>
                <td style="width: 32%; border-top: none;">Nominal (Rp)</td>
            </tr>
            <tr class="center" style="font-size: 8pt;">
                <td>(1)</td>
                <td colspan="3">(2)</td>
                <td>(3)</td>
            </tr>

            {{-- DPP --}}
            @php
                $dppDetailRowCount = $usesMeter ? 4 : 3;
                $dppRowspan = 1 + $dppDetailRowCount + 1;
            @endphp
            <tr>
                <td rowspan="{{ $dppRowspan }}" class="center bold" style="vertical-align: middle;">DPP</td>
                <td colspan="3" class="bold">1. &nbsp;Dasar Pengenaan Pajak</td>
                <td class="right">{{ number_format($dasarPengenaan, 0, ',', '.') }}</td>
            </tr>
            @if($usesMeter)
            <tr>
                <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;a. &nbsp;Meter bulan lalu</td>
                <td class="right">{{ number_format($meterBefore, 2, ',', '.') }}</td>
                <td rowspan="5" style="background: #f0f0f0;"></td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;b. &nbsp;Meter bulan ini</td>
                <td class="right">{{ number_format($meterAfter, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;c. &nbsp;Penggunaan</td>
                <td class="right">{{ number_format($usage, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;d. &nbsp;Rincian Perhitungan</td>
            </tr>
            @else
            <tr>
                <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;a. &nbsp;Jenis objek air tanah</td>
                <td class="right">{{ $objectTypeLabel }}</td>
                <td rowspan="4" style="background: #f0f0f0;"></td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;b. &nbsp;Penggunaan langsung</td>
                <td class="right">{{ number_format($usage, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;c. &nbsp;Rincian Perhitungan</td>
            </tr>
            @endif

            <tr>
                <td colspan="3" style="padding: 0;">
                    <table style="margin: 0; border: none;">
                        <tr class="center bold" style="font-size: 9pt;">
                            <td style="width: 25%; border-top: none; border-left: none;">Rentang</td>
                            <td style="width: 25%; border-top: none;">Penggunaan</td>
                            <td style="width: 25%; border-top: none;">NPA (Rp/m³)</td>
                            <td style="width: 25%; border-top: none; border-right: none;">NPA</td>
                        </tr>

                        @foreach($tierDetails as $tier)
                            <tr style="font-size: 9pt;">
                                <td class="center" style="border-left: none; {{ $loop->last ? 'border-bottom: none;' : '' }}">{{ formatTierRange($tier['min_vol'], $tier['max_vol']) }}</td>
                                <td class="right" style="{{ $loop->last ? 'border-bottom: none;' : '' }}">{{ number_format($tier['used'], 2, ',', '.') }}</td>
                                <td class="right" style="{{ $loop->last ? 'border-bottom: none;' : '' }}">{{ number_format($tier['npa'], 0, ',', '.') }}</td>
                                <td class="right" style="border-right: none; {{ $loop->last ? 'border-bottom: none;' : '' }}">{{ number_format($tier['npa_total'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>

            {{-- Tarif Pajak --}}
            <tr>
                <td rowspan="2" class="center bold" style="vertical-align: middle;">Pajak</td>
                <td colspan="3" class="bold">2. &nbsp;Tarif Pajak</td>
                <td class="right">{{ number_format($tarifPersen, 0) }}%</td>
            </tr>

            {{-- Pokok Pajak --}}
            <tr>
                <td colspan="3" class="bold">3. &nbsp;Pokok Pajak (1 x 2)</td>
                <td class="right bold">{{ number_format($jumlahPajak, 0, ',', '.') }}</td>
            </tr>
        </table>

        {{-- TERBILANG --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="border-top: none; padding: 6px;">
                    <strong>Terbilang</strong> &nbsp;:&nbsp;
                    <em>{{ ucwords(\NumberFormatter::create('id_ID', \NumberFormatter::SPELLOUT)->format(abs($jumlahPajak))) }} Rupiah</em>
                </td>
            </tr>
        </table>

        {{-- JATUH TEMPO --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="border-top: none; padding: 4px 6px;">
                    <strong>Jatuh Tempo</strong> &nbsp;:&nbsp;
                    {{ $skpd->jatuh_tempo ? $skpd->jatuh_tempo->translatedFormat('d F Y') : '-' }}
                </td>
            </tr>
        </table>

        {{-- TEMPAT PEMBAYARAN --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="width: 30%; font-weight: bold; vertical-align: middle; border-top: none; padding: 4px 6px;">Tempat Pembayaran</td>
                <td style="width: 40%; border-top: none; padding: 4px 6px;">Bank Jatim / BNI / QRIS / Tokopedia / Indomaret / Alfamart</td>
                <td style="width: 30%; text-align: center; font-weight: bold; border-top: none; padding: 4px 6px;">{{ $skpd->kode_billing ?? '-' }}</td>
            </tr>
        </table>

        {{-- CATATAN --}}
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

        {{-- === TANDA TANGAN === --}}
        <table class="footer-table" style="width: 100%; margin-top: 6px;">
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%; text-align: center; font-size: 10pt;">
                    {{-- 1. Lokasi dan tanggal --}}
                    Bojonegoro, {{ $skpd->tanggal_verifikasi ? $skpd->tanggal_verifikasi->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>

                    @if($pimpinan)
                        @if(is_null($pimpinan->bidang) && is_null($pimpinan->sub_bidang))
                            {{ $pimpinan->jabatan }} {{ $pimpinan->opd }}<br>
                            {{ $pimpinan->kab }}
                        @elseif(is_null($pimpinan->sub_bidang))
                            a.n. {{ $pimpinan->jabatan }} {{ $pimpinan->opd }}<br>
                            {{ $pimpinan->kab }}<br>
                            <span style="font-size: 9pt;">{{ $pimpinan->jabatan }} {{ $pimpinan->bidang }}</span>
                        @else
                            a.n. {{ $pimpinan->jabatan }} {{ $pimpinan->opd }}<br>
                            {{ $pimpinan->kab }}<br>
                            <span style="font-size: 9pt;">{{ $pimpinan->jabatan }} {{ $pimpinan->sub_bidang }}</span><br>
                            <span style="font-size: 9pt;">{{ $pimpinan->bidang }}</span>
                        @endif
                    @else
                        Jabatan<br>
                        Kabupaten
                    @endif

                    {{-- QR Code --}}
                    <div style="margin: 8px auto; width: 70px; height: 70px;">
                        <img src="data:image/svg+xml;base64, {{ base64_encode((new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer(new \BaconQrCode\Renderer\RendererStyle\RendererStyle(100), new \BaconQrCode\Renderer\Image\SvgImageBackEnd())))->writeString(route('skpd-air-tanah.show', $skpd->id))) }}"
                            alt="QR Code" style="width: 70px; height: 70px;">
                    </div>

                    @if($pimpinan)
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ strtoupper($pimpinan->nama) }}
                        </div>
                        @if($pimpinan->pangkat)
                            <div style="font-size: 9pt;">{{ $pimpinan->pangkat }}</div>
                        @endif
                        <div style="font-size: 9pt;">NIP. {{ $pimpinan->nip }}</div>
                    @else
                        <div style="font-weight: bold; text-decoration: underline;">Nama Pejabat</div>
                        <div style="font-size: 9pt;">Pangkat</div>
                        <div style="font-size: 9pt;">NIP</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
