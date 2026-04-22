<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Histori Pajak {{ $npwpd }} - {{ $tahun }}</title>
<style>
    @page { margin: 18mm 14mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #111; }
    h1 { font-size: 13pt; margin: 0 0 4px; }
    .meta { font-size: 8.5pt; color: #444; margin-bottom: 10px; }
    .summary { width: 100%; margin: 8px 0 12px; border-collapse: collapse; }
    .summary td { padding: 5px 8px; border: 1px solid #ccc; font-size: 8.5pt; }
    .summary .label { background: #f3f4f6; font-weight: bold; width: 18%; }
    table.detail { width: 100%; border-collapse: collapse; font-size: 7.8pt; }
    table.detail th, table.detail td { border: 1px solid #888; padding: 4px 5px; vertical-align: top; }
    table.detail th { background: #e5e7eb; font-weight: bold; }
    .right { text-align: right; }
    .footer { margin-top: 12px; font-size: 7.5pt; color: #555; text-align: right; }
    .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 7pt; background: #e5e7eb; }
</style>
</head>
<body>
    <h1>Histori Pajak per Wajib Pajak</h1>
    <div class="meta">
        NPWPD: <strong>{{ $npwpd }}</strong> &nbsp;|&nbsp;
        Tahun Pajak: <strong>{{ $tahun }}</strong> &nbsp;|&nbsp;
        Dicetak: {{ $tanggalCetak->translatedFormat('d M Y H:i') }}
    </div>

    <table class="summary">
        <tr>
            <td class="label">Total Dokumen</td><td>{{ number_format($ringkasan['total_dokumen']) }}</td>
            <td class="label">Total Tagihan</td><td class="right">Rp {{ number_format($ringkasan['total_tagihan'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Total Terbayar</td><td class="right">Rp {{ number_format($ringkasan['total_terbayar'], 0, ',', '.') }}</td>
            <td class="label">Total Tunggakan</td><td class="right">Rp {{ number_format($ringkasan['total_tunggakan'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="detail">
        <thead>
            <tr>
                <th>Jenis Dokumen</th>
                <th>Jenis Pajak</th>
                <th>NOPD</th>
                <th>Objek Pajak</th>
                <th>Nomor</th>
                <th>Masa</th>
                <th>Terbit</th>
                <th>Jatuh Tempo</th>
                <th class="right">Tagihan</th>
                <th class="right">Terbayar</th>
                <th class="right">Sisa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
                <tr>
                    <td><span class="badge">{{ $r->jenisDokumen->label() }}</span></td>
                    <td>{{ $r->jenisPajak }}</td>
                    <td>{{ $r->nopd ?? '-' }}</td>
                    <td>{{ $r->namaObjekPajak ?? '-' }}</td>
                    <td>{{ $r->nomor }}</td>
                    <td>{{ $r->masa }}</td>
                    <td>{{ $r->tanggalTerbit?->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ $r->jatuhTempo?->format('d-m-Y') ?? '-' }}</td>
                    <td class="right">{{ number_format($r->jumlahTagihan, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($r->jumlahTerbayar, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($r->jumlahSisa(), 0, ',', '.') }}</td>
                    <td>{{ $r->statusLabel }}</td>
                </tr>
            @endforeach
            @if($rows->isEmpty())
                <tr><td colspan="12" style="text-align:center; padding:14px;">Tidak ada data.</td></tr>
            @endif
        </tbody>
    </table>

    <div class="footer">Borotax &mdash; Sistem Pengelolaan Pajak Daerah</div>
</body>
</html>
