@extends('layouts.portal-dashboard')

@section('title', 'Detail Objek Reklame - Borotax Portal')
@section('page-title', 'Detail Objek Reklame')

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

    /* Detail card */
    .detail-card {
        background: var(--bg-card);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        margin-bottom: 24px;
    }

    .detail-card-header {
        background: linear-gradient(140deg, #FF7043 0%, #E64A19 100%);
        padding: 24px 28px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .detail-card-header .dch-icon {
        width: 52px;
        height: 52px;
        border-radius: var(--radius-lg);
        background: rgba(255,255,255,0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .detail-card-header h2 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .detail-card-header .dch-addr {
        font-size: 0.82rem;
        opacity: 0.7;
    }

    .detail-card-header .dch-badge {
        margin-left: auto;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 4px 14px;
        border-radius: var(--radius-full);
        background: rgba(255,255,255,0.2);
    }

    /* Status row */
    .status-row {
        padding: 16px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--border);
        flex-wrap: wrap;
        gap: 10px;
    }

    .status-badge {
        font-size: 0.82rem;
        font-weight: 700;
        padding: 5px 16px;
        border-radius: var(--radius-full);
    }

    .status-badge.aktif       { background: #E8F5E9; color: #2E7D32; }
    .status-badge.kadaluarsa  { background: #FFEBEE; color: #C62828; }
    .status-badge.pending     { background: #FFF8E1; color: #F57F17; }

    .expiry-info {
        font-size: 0.82rem;
        color: var(--text-tertiary);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .expiry-info.warning { color: #F57F17; font-weight: 600; }
    .expiry-info.danger  { color: #C62828; font-weight: 600; }

    /* Detail sections */
    .detail-section {
        padding: 24px 28px;
        border-bottom: 1px solid var(--border);
    }

    .detail-section:last-child { border-bottom: none; }

    .ds-title {
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

    .ds-title i { color: #E64A19; }

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

    /* Extension button area */
    .ext-actions {
        padding: 24px 28px;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .btn-extend {
        background: linear-gradient(140deg, #FF7043 0%, #E64A19 100%);
        color: #fff;
        border: none;
        padding: 12px 24px;
        border-radius: var(--radius-full);
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        transition: all var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-extend:hover { box-shadow: var(--shadow-lg); transform: translateY(-1px); color: #fff; }

    .ext-notice {
        font-size: 0.78rem;
        color: var(--text-tertiary);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .ext-notice i { color: #1565C0; }

    /* Request history */
    .req-card {
        background: var(--bg-surface-variant);
        border-radius: var(--radius-md);
        padding: 14px 18px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .req-card:last-child { margin-bottom: 0; }

    .req-status {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: var(--radius-full);
        white-space: nowrap;
    }

    .req-status.diajukan          { background: #E3F2FD; color: #1565C0; }
    .req-status.menungguVerifikasi { background: #E3F2FD; color: #1565C0; }
    .req-status.diproses           { background: #FFF8E1; color: #F57F17; }
    .req-status.disetujui          { background: #E8F5E9; color: #2E7D32; }
    .req-status.ditolak            { background: #FFEBEE; color: #C62828; }

    .req-info { flex: 1; min-width: 0; }

    .req-info .req-dur {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .req-info .req-date {
        font-size: 0.75rem;
        color: var(--text-tertiary);
    }

    .req-note {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        font-style: italic;
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* SKPD mini list */
    .skpd-mini {
        background: var(--bg-surface-variant);
        border-radius: var(--radius-md);
        padding: 12px 18px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: inherit;
        transition: all var(--transition);
    }

    .skpd-mini:hover { background: var(--bg-surface); }
    .skpd-mini:last-child { margin-bottom: 0; }

    .skpd-mini .sm-nomor {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .skpd-mini .sm-date {
        font-size: 0.72rem;
        color: var(--text-tertiary);
        margin-left: auto;
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

    /* Alerts */
    .alert-success {
        background: #E8F5E9;
        border: 1px solid #A5D6A7;
        color: #2E7D32;
        padding: 14px 20px;
        border-radius: var(--radius-md);
        font-size: 0.85rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-error {
        background: #FFEBEE;
        border: 1px solid #EF9A9A;
        color: #C62828;
        padding: 14px 20px;
        border-radius: var(--radius-md);
        font-size: 0.85rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    @media (max-width: 768px) {
        .detail-grid { grid-template-columns: 1fr; }
        .media-compare-grid { grid-template-columns: 1fr; }
        .detail-section { padding: 20px 20px; }
        .detail-card-header { padding: 20px 20px; }
        .status-row { padding: 14px 20px; }
        .ext-actions { padding: 20px 20px; flex-direction: column; align-items: stretch; }
        .btn-extend { justify-content: center; }
    }
</style>
@endsection

@section('content')
    <a href="{{ route('portal.reklame.objects') }}" class="obj-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Objek
    </a>

    @session('success')
        <div class="alert-success">
            <i class="bi bi-check-circle-fill"></i>
            {{ $value }}
        </div>
    @endsession

    @session('error')
        <div class="alert-error">
            <i class="bi bi-exclamation-circle-fill"></i>
            {{ $value }}
        </div>
    @endsession

    @php
        $isExpired = $object->isKadaluarsa();
        $statusClass = match($object->status) {
            'aktif' => $isExpired ? 'kadaluarsa' : 'aktif',
            'kadaluarsa' => 'kadaluarsa',
            default => 'pending',
        };
        $statusLabel = match($object->status) {
            'aktif' => $isExpired ? 'Kadaluarsa' : 'Aktif',
            'kadaluarsa' => 'Kadaluarsa',
            default => 'Pending',
        };
    @endphp

    <div class="detail-card">
        {{-- Header --}}
        <div class="detail-card-header">
            <div class="dch-icon"><i class="bi bi-signpost-2-fill"></i></div>
            <div>
                <h2>{{ $object->nama_reklame }}</h2>
                <div class="dch-addr">{{ $object->alamat_reklame }}, {{ $object->kelurahan }}, {{ $object->kecamatan }}</div>
            </div>
            <span class="dch-badge">{{ $object->kelompok_lokasi ? 'Kelompok ' . $object->kelompok_lokasi : '' }}</span>
        </div>

        {{-- Status Row --}}
        <div class="status-row">
            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
            @if($object->masa_berlaku_sampai)
                @php
                    $expiryClass = $isExpired ? 'danger' : ($object->sisa_hari <= 30 ? 'warning' : '');
                @endphp
                <span class="expiry-info {{ $expiryClass }}">
                    <i class="bi bi-calendar3"></i>
                    @if($isExpired)
                        Berlaku s/d {{ $object->masa_berlaku_sampai->translatedFormat('d F Y') }} (Kadaluarsa)
                    @else
                        Berlaku s/d {{ $object->masa_berlaku_sampai->translatedFormat('d F Y') }} ({{ $object->sisa_hari }} hari lagi)
                    @endif
                </span>
            @endif
        </div>

        {{-- Detail Objek --}}
        <div class="detail-section">
            <div class="ds-title">
                <i class="bi bi-signpost-2"></i> Informasi Objek Reklame
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nama Reklame</span>
                    <span class="detail-value">{{ $object->nama_reklame }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">NPWPD</span>
                    <span class="detail-value">{{ $object->npwpd ?? '-' }}</span>
                </div>
                <div class="detail-item full">
                    <span class="detail-label">Alamat Reklame</span>
                    <span class="detail-value">{{ $object->alamat_reklame }}, {{ $object->kelurahan }}, {{ $object->kecamatan }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Ukuran</span>
                    <span class="detail-value">{{ $object->ukuran_formatted }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Jumlah Muka</span>
                    <span class="detail-value">{{ $object->jumlah_muka }} Muka</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Kelompok Lokasi</span>
                    <span class="detail-value">Kelompok {{ $object->kelompok_lokasi }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tanggal Pasang</span>
                    <span class="detail-value">{{ $object->tanggal_pasang?->translatedFormat('d F Y') ?? '-' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Masa Berlaku Sampai</span>
                    <span class="detail-value">{{ $object->masa_berlaku_sampai?->translatedFormat('d F Y') ?? '-' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">NOPD</span>
                    <span class="detail-value">{{ $object->nopd ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Perpanjangan Action --}}
        <div class="ext-actions">
            @if($canRequestExtension)
                <a href="{{ route('portal.reklame.request-extension', $object->id) }}" class="btn-extend">
                    <i class="bi bi-arrow-repeat"></i>
                    Ajukan Perpanjangan
                </a>
                <span class="ext-notice">
                    <i class="bi bi-info-circle"></i>
                    @if($isExpired)
                        Reklame Anda sudah kadaluarsa. Segera ajukan perpanjangan.
                    @else
                        Masa berlaku tinggal {{ $object->sisa_hari }} hari. Anda dapat mengajukan perpanjangan.
                    @endif
                </span>
            @elseif($hasActiveRequest)
                <span class="ext-notice">
                    <i class="bi bi-hourglass-split"></i>
                    Anda memiliki pengajuan perpanjangan yang sedang diproses. Silakan tunggu hingga selesai.
                </span>
            @else
                <span class="ext-notice">
                    <i class="bi bi-check-circle"></i>
                    Masa berlaku reklame masih panjang. Perpanjangan dapat diajukan saat masa berlaku tinggal 30 hari atau kurang.
                </span>
            @endif
        </div>
    </div>

    @include('portal.reklame.partials.media-history', [
        'histories' => $fotoHistories,
        'title' => 'Histori Foto Objek',
        'icon' => 'bi-camera',
    ])

    @include('portal.reklame.partials.media-history', [
        'histories' => $materiHistories,
        'title' => 'Histori Materi Reklame',
        'icon' => 'bi-megaphone',
    ])

    {{-- Riwayat Pengajuan --}}
    @if($object->reklameRequests->isNotEmpty())
        <div class="detail-card">
            <div class="detail-section">
                <div class="ds-title">
                    <i class="bi bi-clock-history"></i> Riwayat Pengajuan Perpanjangan
                </div>
                @foreach($object->reklameRequests as $req)
                    <div class="req-card">
                        <span class="req-status {{ str_replace(' ', '', $req->status) }}">{{ $req->status_label }}</span>
                        <div class="req-info">
                            <div class="req-dur">
                                Perpanjangan {{ $req->durasi_perpanjangan_hari }} hari
                            </div>
                            <div class="req-date">
                                Diajukan {{ $req->tanggal_pengajuan?->translatedFormat('d M Y, H:i') }}
                            </div>
                        </div>
                        @if($req->catatan_pengajuan)
                            <div class="req-note" title="{{ $req->catatan_pengajuan }}">
                                {{ $req->catatan_pengajuan }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- SKPD Terkait --}}
    @if($object->skpdReklame->isNotEmpty())
        <div class="detail-card">
            <div class="detail-section">
                <div class="ds-title">
                    <i class="bi bi-file-earmark-text"></i> SKPD Terkait
                </div>
                @foreach($object->skpdReklame as $skpd)
                    <a href="{{ route('portal.reklame.skpd-detail', $skpd->id) }}" class="skpd-mini">
                        <i class="bi bi-file-earmark-text" style="color: #2E7D32;"></i>
                        <span class="sm-nomor">{{ $skpd->nomor_skpd ?? '-' }}</span>
                        <span class="sm-date">{{ $skpd->tanggal_buat?->translatedFormat('d M Y') }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endsection
