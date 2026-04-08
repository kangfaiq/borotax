<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKRD Sewa Tanah - {{ $skrd->nomor_skrd }}</title>
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
    @if($skrd->status === 'draft')
    <div style="position: fixed; top: 35%; left: 10%; width: 80%; text-align: center; z-index: 9999; pointer-events: none;">
        <span style="font-size: 120px; font-weight: bold; color: rgba(255, 0, 0, 0.12); letter-spacing: 20px; transform: rotate(-45deg); display: inline-block; font-family: Arial, sans-serif;">DRAFT</span>
    </div>
    @endif
    @php
        $jenisPajak = $skrd->jenisPajak;
        $subJenisPajak = $skrd->subJenisPajak;

        $tarifNominal = (float) $skrd->tarif_nominal;
        $durasi = (int) ($skrd->durasi ?? 1);
        $jumlahRetribusi = (float) ($skrd->jumlah_retribusi ?? 0);
        $luasM2 = (float) ($skrd->luas_m2 ?? 0);
        $jumlahReklame = (int) ($skrd->jumlah_reklame ?? 1);
        $tarifPajakPersen = (float) ($skrd->tarif_pajak_persen ?? 25);

        $satuanLabel = $skrd->satuan_label ?? match($skrd->satuan_waktu ?? 'perTahun') {
            'perTahun' => 'per Tahun',
            'perBulan' => 'per Bulan',
            default => 'per Tahun',
        };

        // Kode rekening
        $kodeJenisPajak = $jenisPajak->billing_kode_override ?? $jenisPajak->kode ?? '41104';
        $kodeRekening = substr($kodeJenisPajak, 0, 1) . '.' . substr($kodeJenisPajak, 1, 1) . '.' . substr($kodeJenisPajak, 2, 1) . '.' . substr($kodeJenisPajak, 3);

        // Kohir
        $kohir = $skrd->kode_billing ? substr($skrd->kode_billing, -8) : '-';

        // Penyelenggaraan
        $penyelenggaraan = $subJenisPajak ? $subJenisPajak->nama : '-';
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
                    <strong>Masa Retribusi</strong><br>
                    {{ $skrd->masa_berlaku_mulai ? $skrd->masa_berlaku_mulai->format('d/m/Y') : 'dd/mm/yyyy' }}<br>
                    s.d.<br>
                    {{ $skrd->masa_berlaku_sampai ? $skrd->masa_berlaku_sampai->format('d/m/Y') : 'dd/mm/yyyy' }}
                </td>
            </tr>
            <tr>
                <td class="center bold" style="font-size: 12pt; padding: 6px;">
                    SURAT KETETAPAN RETRIBUSI DAERAH (SKRD)
                </td>
                <td style="text-align: center; font-size: 9pt; vertical-align: middle;">
                    <strong>Kohir:</strong><br>
                    {{ $kohir }}
                </td>
            </tr>
        </table>

        {{-- === JENIS RETRIBUSI === --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td rowspan="2" style="width: 12%; font-weight: bold; vertical-align: middle; text-align: center; border-top: none;">
                    Jenis<br>Retribusi</td>
                <td style="width: 22%; font-weight: bold; border-top: none;">Kode Rekening</td>
                <td style="border-top: none;">{{ $kodeRekening }}</td>
            </tr>
            <tr>
                <td class="bold">Jenis Retribusi</td>
                <td>RETRIBUSI SEWA TANAH</td>
            </tr>
        </table>

        {{-- === WAJIB BAYAR === --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td rowspan="3" style="width: 12%; font-weight: bold; vertical-align: middle; text-align: center; border-top: none;">
                    Wajib<br>Bayar</td>
                <td style="width: 22%; font-weight: bold; border-top: none;">NPWPD</td>
                <td style="border-top: none;">{{ $skrd->npwpd ?? '-' }}</td>
            </tr>
            <tr>
                <td class="bold">Nama Wajib Bayar</td>
                <td>{{ strtoupper($skrd->nama_wajib_pajak) }}</td>
            </tr>
            <tr>
                <td class="bold">Alamat Wajib Bayar</td>
                <td>{{ $skrd->alamat_wajib_pajak }}</td>
            </tr>
        </table>

        {{-- === OBJEK RETRIBUSI === --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td rowspan="3" style="width: 12%; font-weight: bold; vertical-align: middle; text-align: center; border-top: none;">
                    Objek<br>Retribusi</td>
                <td style="width: 22%; font-weight: bold; border-top: none;">Sub Jenis</td>
                <td style="border-top: none;">{{ $penyelenggaraan }}</td>
            </tr>
            <tr>
                <td class="bold">Nama Objek</td>
                <td>{{ strtoupper($skrd->nama_objek) }}</td>
            </tr>
            <tr>
                <td class="bold">Alamat Objek</td>
                <td>{{ $skrd->alamat_objek }}</td>
            </tr>
        </table>

        {{-- === PERHITUNGAN === --}}
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
            <tr>
                <td rowspan="7" class="center bold" style="vertical-align: middle;">Retribusi</td>
                <td colspan="2" class="bold">1. &nbsp;Luas (m²)</td>
                <td class="right">{{ number_format($luasM2, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="bold">2. &nbsp;Jumlah Reklame</td>
                <td class="right">{{ $jumlahReklame }}</td>
            </tr>
            <tr>
                <td colspan="2" class="bold">3. &nbsp;Harga Sub Jenis ({{ $satuanLabel }})</td>
                <td class="right">{{ number_format($tarifNominal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="bold">4. &nbsp;Tarif Retribusi</td>
                <td class="right">{{ number_format($tarifPajakPersen, 0) }}%</td>
            </tr>
            <tr>
                <td colspan="2" class="bold">5. &nbsp;Durasi</td>
                <td class="right">{{ $durasi }}</td>
            </tr>
            <tr>
                <td colspan="2" class="bold">6. &nbsp;Kategori</td>
                <td class="right">{{ match($skrd->satuan_waktu ?? 'perTahun') { 'perTahun' => 'Tahunan', 'perBulan' => 'Bulanan', default => 'Tahunan' } }}</td>
            </tr>
            <tr>
                <td colspan="2" class="bold">7. &nbsp;Pokok Retribusi Terutang (1 x 2 x 3 x 4 x 5)</td>
                <td class="right bold">{{ number_format($jumlahRetribusi, 0, ',', '.') }}</td>
            </tr>
        </table>

        {{-- TERBILANG --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="border-top: none; padding: 6px;">
                    <strong>Terbilang</strong> &nbsp;:&nbsp;
                    <em>{{ ucwords(\NumberFormatter::create('id_ID', \NumberFormatter::SPELLOUT)->format(abs($jumlahRetribusi))) }} Rupiah</em>
                </td>
            </tr>
        </table>

        {{-- JATUH TEMPO --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="border-top: none; padding: 4px 6px;">
                    <strong>Jatuh Tempo</strong> &nbsp;:&nbsp;
                    {{ $skrd->jatuh_tempo ? $skrd->jatuh_tempo->translatedFormat('d F Y') : '-' }}
                </td>
            </tr>
        </table>

        {{-- TEMPAT PEMBAYARAN --}}
        <table style="margin-bottom: 0; border-top: none;">
            <tr>
                <td style="width: 30%; font-weight: bold; vertical-align: middle; border-top: none; padding: 4px 6px;">Tempat Pembayaran</td>
                <td style="width: 40%; border-top: none; padding: 4px 6px;">Bank Jatim / BNI / QRIS / Tokopedia / Indomaret / Alfamart</td>
                <td style="width: 30%; text-align: center; font-weight: bold; border-top: none; padding: 4px 6px;">{{ $skrd->kode_billing ?? '-' }}</td>
            </tr>
        </table>

        {{-- CATATAN --}}
        <table style="border-top: none;">
            <tr>
                <td style="border-top: none; padding: 6px; font-size: 9pt;">
                    <ol style="margin: 0; padding-left: 16px;">
                        <li>Apabila pembayaran melebihi jatuh tempo pembayaran retribusi maka dikenakan sanksi
                            administratif berupa bunga sebesar 1% (satu persen) dari pokok retribusi.</li>
                        <li>Surat Ketetapan Retribusi Daerah (SKRD) bukan merupakan bukti pembayaran retribusi.</li>
                    </ol>
                </td>
            </tr>
        </table>

        {{-- === TANDA TANGAN === --}}
        <table class="footer-table" style="width: 100%; margin-top: 10px;">
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%; text-align: center; font-size: 10pt;">
                    Bojonegoro, {{ $skrd->tanggal_verifikasi ? $skrd->tanggal_verifikasi->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>

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

                    <div style="margin: 12px auto; width: 80px; height: 80px;">
                        <img src="data:image/svg+xml;base64, {{ base64_encode((new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer(new \BaconQrCode\Renderer\RendererStyle\RendererStyle(100), new \BaconQrCode\Renderer\Image\SvgImageBackEnd())))->writeString(route('skrd-sewa.show', $skrd->id))) }}"
                            alt="QR Code" style="width: 80px; height: 80px;">
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
