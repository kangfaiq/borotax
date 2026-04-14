@extends('layouts.portal-dashboard')

@section('title', 'Detail SKPD Reklame - Borotax Portal')
@section('page-title', 'Detail SKPD Reklame')

@section('styles')
<style>
    .obj-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        color: var(--text-tertiary);
        margin-bottom: 20px;
        transition: color var(--transition);
    }

    .obj-back:hover { color: var(--primary-dark); }

    /* Document wrapper */
    .doc-wrapper {
        background: var(--bg-card);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }

    /* Document header (government) */
    .doc-header {
        background: linear-gradient(140deg, var(--secondary) 0%, var(--secondary-dark) 100%);
        color: #fff;
        padding: 28px 32px;
        text-align: center;
        position: relative;
    }

    .doc-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #FF7043, #E64A19, #FF7043);
    }

    .doc-header .gov-icon {
        font-size: 2rem;
        margin-bottom: 8px;
        display: block;
    }

    .doc-header h2 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 2px;
        letter-spacing: 0.5px;
    }

    .doc-header .doc-subtitle {
        font-size: 0.82rem;
        opacity: 0.7;
    }

    .doc-nomor {
        margin-top: 10px;
        font-family: 'Courier New', monospace;
        font-size: 0.88rem;
        font-weight: 700;
        background: rgba(255,255,255,0.12);
        display: inline-block;
        padding: 4px 16px;
        border-radius: var(--radius-full);
        letter-spacing: 1px;
    }

    /* Status badge row */
    .doc-status-row {
        padding: 16px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--border);
        flex-wrap: wrap;
        gap: 10px;
    }

    .doc-status {
        font-size: 0.82rem;
        font-weight: 700;
        padding: 5px 16px;
        border-radius: var(--radius-full);
    }

    .doc-status.disetujui  { background: #E8F5E9; color: #2E7D32; }
    .doc-status.ditolak    { background: #FFEBEE; color: #C62828; }
    .doc-status.menunggu   { background: #E3F2FD; color: #1565C0; }
    .doc-status.draft      { background: #FFF8E1; color: #F57F17; }

    .doc-date {
        font-size: 0.78rem;
        color: var(--text-tertiary);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Sections */
    .doc-section {
        padding: 24px 32px;
        border-bottom: 1px solid var(--border);
    }

    .doc-section:last-child { border-bottom: none; }

    .doc-section-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .doc-section-title i { color: #E64A19; }

    /* Detail rows */
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-item.full { grid-column: 1 / -1; }

    .detail-label {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        margin-bottom: 2px;
    }

    .detail-value {
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    /* Calculation table */
    .calc-table {
        width: 100%;
        border-collapse: collapse;
    }

    .calc-table th {
        text-align: left;
        padding: 10px 0;
        font-size: 0.78rem;
        color: var(--text-tertiary);
        font-weight: 600;
        border-bottom: 1px solid var(--border);
    }

    .calc-table td {
        padding: 10px 0;
        font-size: 0.88rem;
        border-bottom: 1px solid var(--border);
    }

    .calc-table td:last-child {
        text-align: right;
        font-weight: 600;
    }

    .calc-table tr.total td {
        border-bottom: none;
        padding-top: 14px;
        font-weight: 800;
        font-size: 1rem;
    }

    .calc-table tr.total td:last-child {
        color: #BF360C;
        font-size: 1.1rem;
    }

    /* Billing card */
    .billing-card {
        background: linear-gradient(140deg, #1E293B 0%, #334155 100%);
        border-radius: var(--radius-lg);
        padding: 24px;
        color: #fff;
    }

    .billing-card-label {
        font-size: 0.78rem;
        opacity: 0.6;
        margin-bottom: 4px;
    }

    .billing-card-code {
        font-family: 'Courier New', monospace;
        font-size: 1.3rem;
        font-weight: 800;
        letter-spacing: 2px;
        margin-bottom: 12px;
    }

    .billing-card-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .billing-card-amount {
        font-size: 1.2rem;
        font-weight: 800;
    }

    .btn-copy-lg {
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
        padding: 8px 18px;
        border-radius: var(--radius-full);
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-copy-lg:hover { background: rgba(255,255,255,0.25); }
    .btn-copy-lg.copied { background: #2E7D32; border-color: #2E7D32; }

    /* Rejection box */
    .rejection-box {
        background: #FFF3E0;
        border: 1px solid #FFE0B2;
        border-radius: var(--radius-md);
        padding: 16px 18px;
    }

    .rejection-box .rb-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #E65100;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .rejection-box .rb-text {
        font-size: 0.82rem;
        color: #795548;
        line-height: 1.6;
    }

    .media-history-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .media-history-card {
        background: var(--bg-surface-variant);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 18px;
    }

    .media-history-meta {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .media-history-heading {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .media-history-subtext,
    .media-history-description,
    .media-preview-filename {
        font-size: 0.75rem;
        color: var(--text-tertiary);
    }

    .media-history-label {
        background: #FFF3E0;
        color: #E65100;
        border-radius: var(--radius-full);
        padding: 4px 10px;
        font-size: 0.7rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .media-history-description {
        margin-bottom: 14px;
        line-height: 1.6;
    }

    .media-compare-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .media-preview-pane {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 14px;
    }

    .media-preview-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 10px;
    }

    .media-preview-frame,
    .media-preview-empty {
        min-height: 180px;
        border-radius: var(--radius-md);
        background: #F8FAFC;
        border: 1px dashed #CBD5E1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .media-preview-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .media-preview-document,
    .media-preview-empty {
        flex-direction: column;
        gap: 8px;
        text-align: center;
        padding: 16px;
        color: #475569;
    }

    .media-preview-document i,
    .media-preview-empty i {
        font-size: 1.8rem;
        color: #E64A19;
    }

    .media-preview-actions {
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .media-preview-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #1565C0;
        text-decoration: none;
    }

    .media-preview-link:hover {
        color: #0D47A1;
    }

    @media (max-width: 768px) {
        .detail-grid { grid-template-columns: 1fr; }
        .media-compare-grid { grid-template-columns: 1fr; }
        .doc-section { padding: 20px 20px; }
        .doc-header { padding: 22px 20px; }
        .doc-status-row { padding: 14px 20px; }
        .billing-card-row { flex-direction: column; align-items: stretch; text-align: center; }
    }
</style>
@endsection

@section('content')
    <a href="{{ route('portal.reklame.skpd-list') }}" class="obj-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar SKPD
    </a>

    @php
        $statusClass = match($skpd->status) {
            'disetujui' => 'disetujui',
            'ditolak' => 'ditolak',
            'menungguVerifikasi' => 'menunggu',
            default => 'draft',
        };
        $statusLabel = match($skpd->status) {
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'menungguVerifikasi' => 'Menunggu Verifikasi',
            default => 'Draft',
        };
    @endphp

    <div class="doc-wrapper">
        {{-- Government Header --}}
        <div class="doc-header">
            <span class="gov-icon">🏛️</span>
            <h2>PEMERINTAH KABUPATEN BOJONEGORO</h2>
            <div class="doc-subtitle">BADAN PENDAPATAN DAERAH</div>
            <div class="doc-nomor">{{ $skpd->nomor_skpd ?? '-' }}</div>
        </div>

        {{-- Status Row --}}
        <div class="doc-status-row">
            <span class="doc-status {{ $statusClass }}">{{ $statusLabel }}</span>
            <span class="doc-date">
                <i class="bi bi-calendar3"></i>
                {{ $skpd->tanggal_buat?->translatedFormat('d F Y') ?? '-' }}
            </span>
        </div>

        {{-- Data Wajib Pajak --}}
        <div class="doc-section">
            <div class="doc-section-title">
                <i class="bi bi-person"></i> Data Wajib Pajak
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nama</span>
                    <span class="detail-value">{{ $skpd->nama_wajib_pajak ?? '-' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">NIK</span>
                    <span class="detail-value">{{ $skpd->nik_wajib_pajak ? substr($skpd->nik_wajib_pajak, 0, 6) . '****' . substr($skpd->nik_wajib_pajak, -4) : '-' }}</span>
                </div>
                <div class="detail-item full">
                    <span class="detail-label">Alamat</span>
                    <span class="detail-value">{{ $skpd->alamat_wajib_pajak ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Data Objek Reklame --}}
        <div class="doc-section">
            <div class="doc-section-title">
                <i class="bi bi-signpost-2"></i> Data Objek Reklame
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nama Reklame</span>
                    <span class="detail-value">{{ $skpd->nama_reklame ?? $skpd->reklameObject?->nama_reklame ?? '-' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Jenis Reklame</span>
                    <span class="detail-value">{{ ucfirst($skpd->jenis_reklame ?? '-') }}</span>
                </div>
                <div class="detail-item full">
                    <span class="detail-label">Alamat Reklame</span>
                    <span class="detail-value">{{ $skpd->alamat_reklame ?? '-' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Luas</span>
                    <span class="detail-value">{{ $skpd->luas_m2 }} m²</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Jumlah Muka</span>
                    <span class="detail-value">{{ $skpd->jumlah_muka }} Muka</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Masa Berlaku Mulai</span>
                    <span class="detail-value">{{ $skpd->masa_berlaku_mulai?->translatedFormat('d F Y') ?? '-' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Masa Berlaku Sampai</span>
                    <span class="detail-value">{{ $skpd->masa_berlaku_sampai?->translatedFormat('d F Y') ?? '-' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Durasi</span>
                    <span class="detail-value">{{ $skpd->durasi ?? '-' }} {{ $skpd->satuan_waktu ?? '' }}</span>
                </div>
            </div>
        </div>

        @include('portal.reklame.partials.media-history', [
            'histories' => $fotoHistories,
            'title' => 'Histori Foto Objek',
            'icon' => 'bi-camera',
            'containerClass' => '',
            'sectionClass' => 'doc-section',
            'titleClass' => 'doc-section-title',
        ])

        @include('portal.reklame.partials.media-history', [
            'histories' => $materiHistories,
            'title' => 'Histori Materi Reklame',
            'icon' => 'bi-megaphone',
            'containerClass' => '',
            'sectionClass' => 'doc-section',
            'titleClass' => 'doc-section-title',
        ])

        {{-- Perhitungan Pajak --}}
        <div class="doc-section">
            <div class="doc-section-title">
                <i class="bi bi-calculator"></i> Perhitungan Pajak
            </div>
            <table class="calc-table">
                <tr>
                    <td>Luas Reklame</td>
                    <td>{{ $skpd->luas_m2 }} m²</td>
                </tr>
                <tr>
                    <td>Jumlah Muka</td>
                    <td>{{ $skpd->jumlah_muka }} muka</td>
                </tr>
                <tr>
                    <td>Durasi</td>
                    <td>{{ $skpd->durasi ?? '-' }} {{ $skpd->satuan_waktu ?? '' }}</td>
                </tr>
                <tr>
                    <td>Tarif Pokok</td>
                    <td>Rp {{ number_format((float)($skpd->tarif_pokok ?? 0), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Dasar Pengenaan Pajak (DPP)</td>
                    <td>Rp {{ number_format((float)($skpd->dasar_pengenaan ?? 0), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tarif Pajak</td>
                    <td>25%</td>
                </tr>
                <tr class="total">
                    <td>Jumlah Pajak Terutang</td>
                    <td>Rp {{ number_format((float)($skpd->jumlah_pajak ?? 0), 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        {{-- Billing (only for approved) --}}
        @if($skpd->status === 'disetujui' && $skpd->kode_billing)
            <div class="doc-section">
                <div class="doc-section-title">
                    <i class="bi bi-credit-card-2-front"></i> Informasi Pembayaran
                </div>
                <div class="billing-card">
                    <div class="billing-card-label">Kode Billing</div>
                    <div class="billing-card-code" id="billingCode">{{ $skpd->kode_billing }}</div>
                    <div class="billing-card-row">
                        <div>
                            <div class="billing-card-label">Jumlah Pembayaran</div>
                            <div class="billing-card-amount">Rp {{ number_format((float)($skpd->jumlah_pajak ?? 0), 0, ',', '.') }}</div>
                        </div>
                        <button class="btn-copy-lg" onclick="copyBillingCode(this)">
                            <i class="bi bi-clipboard"></i> Salin Kode
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Rejection note --}}
        @if($skpd->status === 'ditolak' && $skpd->catatan_verifikasi)
            <div class="doc-section">
                <div class="rejection-box">
                    <div class="rb-title">
                        <i class="bi bi-exclamation-triangle-fill"></i> Catatan Penolakan
                    </div>
                    <div class="rb-text">{{ $skpd->catatan_verifikasi }}</div>
                </div>
            </div>
        @endif

        {{-- Verification info --}}
        @if($skpd->verifikator_nama || $skpd->petugas_nama)
            <div class="doc-section">
                <div class="doc-section-title">
                    <i class="bi bi-shield-check"></i> Verifikasi
                </div>
                <div class="detail-grid">
                    @if($skpd->petugas_nama)
                        <div class="detail-item">
                            <span class="detail-label">Petugas</span>
                            <span class="detail-value">{{ $skpd->petugas_nama }}</span>
                        </div>
                    @endif
                    @if($skpd->verifikator_nama)
                        <div class="detail-item">
                            <span class="detail-label">Verifikator</span>
                            <span class="detail-value">{{ $skpd->verifikator_nama }}</span>
                        </div>
                    @endif
                    @if($skpd->tanggal_verifikasi)
                        <div class="detail-item">
                            <span class="detail-label">Tanggal Verifikasi</span>
                            <span class="detail-value">{{ $skpd->tanggal_verifikasi->translatedFormat('d F Y, H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    function copyBillingCode(btn) {
        const code = document.getElementById('billingCode').textContent.trim();
        navigator.clipboard.writeText(code).then(() => {
            btn.classList.add('copied');
            btn.innerHTML = '<i class="bi bi-check"></i> Tersalin!';
            setTimeout(() => {
                btn.classList.remove('copied');
                btn.innerHTML = '<i class="bi bi-clipboard"></i> Salin Kode';
            }, 2000);
        });
    }
</script>
@endsection
