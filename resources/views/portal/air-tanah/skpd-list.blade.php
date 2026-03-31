@extends('layouts.portal-dashboard')

@section('title', 'SKPD Air Tanah - Borotax Portal')
@section('page-title', 'Dokumen SKPD Air Tanah')

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

    /* Tabs */
    .skpd-tabs {
        display: flex;
        gap: 0;
        margin-bottom: 24px;
        border-bottom: 2px solid var(--border);
    }

    .skpd-tab {
        padding: 12px 24px;
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--text-tertiary);
        text-decoration: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: all var(--transition);
    }

    .skpd-tab:hover { color: var(--text-primary); }

    .skpd-tab.active {
        color: #00838F;
        border-bottom-color: #00BCD4;
    }

    /* SKPD cards */
    .skpd-list { display: grid; gap: 14px; }

    .skpd-card {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        overflow: hidden;
        transition: all var(--transition);
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .skpd-card:hover {
        border-color: var(--primary-light);
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .skpd-card-top {
        padding: 18px 22px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .skpd-card-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .skpd-card-icon.approved  { background: #E8F5E9; color: #2E7D32; }
    .skpd-card-icon.rejected  { background: #FFEBEE; color: #C62828; }
    .skpd-card-icon.draft     { background: #FFF8E1; color: #F57F17; }
    .skpd-card-icon.pending   { background: #E3F2FD; color: #1565C0; }

    .skpd-card-info { flex: 1; min-width: 0; }

    .skpd-card-nomor {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .skpd-card-objek {
        font-size: 0.78rem;
        color: var(--text-tertiary);
        margin-bottom: 6px;
    }

    .skpd-card-meta {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .skpd-card-meta span {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .skpd-card-meta span strong { color: var(--text-primary); }

    .skpd-status-badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: var(--radius-full);
        white-space: nowrap;
    }

    .skpd-status-badge.disetujui  { background: #E8F5E9; color: #2E7D32; }
    .skpd-status-badge.ditolak    { background: #FFEBEE; color: #C62828; }
    .skpd-status-badge.draft      { background: #FFF8E1; color: #F57F17; }
    .skpd-status-badge.menunggu   { background: #E3F2FD; color: #1565C0; }

    /* Billing row */
    .skpd-card-billing {
        padding: 12px 22px;
        background: var(--bg-surface-variant);
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .billing-code {
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-primary);
        letter-spacing: 1px;
    }

    .billing-amount {
        font-size: 0.88rem;
        font-weight: 800;
        color: #00838F;
    }

    .btn-copy {
        background: none;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 4px 10px;
        font-size: 0.75rem;
        color: var(--text-tertiary);
        cursor: pointer;
        transition: all var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-copy:hover { border-color: var(--primary); color: var(--primary); }
    .btn-copy.copied { border-color: #2E7D32; color: #2E7D32; }

    /* Rejection note */
    .rejection-note {
        padding: 10px 22px 14px;
        background: #FFF8E1;
        font-size: 0.78rem;
        color: #795548;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .rejection-note i { color: #F57F17; margin-top: 1px; }

    .attachment-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.76rem;
        font-weight: 700;
        color: #00838F;
        text-decoration: none;
    }

    .attachment-link:hover {
        color: #006064;
        text-decoration: underline;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-tertiary);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 16px;
        display: block;
        color: #B0BEC5;
    }

    .empty-state h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-secondary);
        margin-bottom: 6px;
    }

    .empty-state p { font-size: 0.85rem; }

    @media (max-width: 768px) {
        .skpd-card-billing { flex-direction: column; align-items: flex-start; }
    }
</style>
@endsection

@section('content')
    <a href="{{ route('portal.air-tanah.index') }}" class="obj-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Air Tanah
    </a>

    {{-- Tabs --}}
    <div class="skpd-tabs">
        <a href="{{ route('portal.air-tanah.skpd-list', ['tab' => 'selesai']) }}"
           class="skpd-tab {{ $tab === 'selesai' ? 'active' : '' }}">
            Selesai
        </a>
        <a href="{{ route('portal.air-tanah.skpd-list', ['tab' => 'proses']) }}"
           class="skpd-tab {{ $tab === 'proses' ? 'active' : '' }}">
            Dalam Proses
        </a>
    </div>

    @if($skpds->isEmpty())
        <div class="empty-state">
            <i class="bi bi-file-earmark-text"></i>
            <h3>Belum Ada SKPD</h3>
            <p>
                @if($tab === 'proses')
                    Tidak ada SKPD yang sedang dalam proses saat ini.
                @else
                    SKPD akan muncul setelah laporan meteran Anda diverifikasi petugas.
                @endif
            </p>
        </div>
    @else
        <div class="skpd-list">
            @foreach($skpds as $skpd)
                @php
                    $statusClass = match($skpd->status) {
                        'disetujui' => 'approved',
                        'ditolak' => 'rejected',
                        'menungguVerifikasi' => 'pending',
                        default => 'draft',
                    };
                    $statusLabel = match($skpd->status) {
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        'menungguVerifikasi' => 'Menunggu Verifikasi',
                        default => 'Draft',
                    };
                    $badgeClass = match($skpd->status) {
                        'disetujui' => 'disetujui',
                        'ditolak' => 'ditolak',
                        'menungguVerifikasi' => 'menunggu',
                        default => 'draft',
                    };
                @endphp
                <a href="{{ route('portal.air-tanah.skpd-detail', $skpd->id) }}" class="skpd-card">
                    <div class="skpd-card-top">
                        <div class="skpd-card-icon {{ $statusClass }}">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="skpd-card-info">
                            <div class="skpd-card-nomor">{{ $skpd->nomor_skpd ?? '-' }}</div>
                            <div class="skpd-card-objek">{{ $skpd->waterObject?->nama_objek ?? 'Objek Air Tanah' }}</div>
                            <div class="skpd-card-meta">
                                <span>
                                    <i class="bi bi-calendar3"></i>
                                    {{ $skpd->tanggal_buat?->translatedFormat('d M Y') ?? '-' }}
                                </span>
                                <span>
                                    <i class="bi bi-water"></i>
                                    Pemakaian: <strong>{{ number_format($skpd->usage ?? 0) }} m³</strong>
                                </span>
                                @if($skpd->lampiran_path)
                                    <span onclick="event.preventDefault(); event.stopPropagation();">
                                        <a href="{{ $skpd->lampiran_url }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="attachment-link">
                                            <i class="bi bi-paperclip"></i>
                                            Lihat Lampiran
                                        </a>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <span class="skpd-status-badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                    </div>

                    @if($skpd->kode_billing && $skpd->status === 'disetujui')
                        <div class="skpd-card-billing" onclick="event.preventDefault();">
                            <div>
                                <span style="font-size:0.72rem;color:var(--text-tertiary);">Kode Billing</span><br>
                                <span class="billing-code">{{ $skpd->kode_billing }}</span>
                                <button class="btn-copy" onclick="copyBilling(this, '{{ $skpd->kode_billing }}')" title="Salin">
                                    <i class="bi bi-clipboard"></i> Salin
                                </button>
                            </div>
                            <div class="billing-amount">
                                Rp {{ number_format((float)$skpd->jumlah_pajak, 0, ',', '.') }}
                            </div>
                        </div>
                    @endif

                    @if($skpd->status === 'ditolak' && $skpd->catatan_verifikasi)
                        <div class="rejection-note">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>{{ $skpd->catatan_verifikasi }}</span>
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
@endsection

@section('scripts')
<script>
    function copyBilling(btn, code) {
        navigator.clipboard.writeText(code).then(() => {
            btn.classList.add('copied');
            btn.innerHTML = '<i class="bi bi-check"></i> Tersalin';
            setTimeout(() => {
                btn.classList.remove('copied');
                btn.innerHTML = '<i class="bi bi-clipboard"></i> Salin';
            }, 2000);
        });
    }
</script>
@endsection
