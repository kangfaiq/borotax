@extends('layouts.portal-dashboard')

@section('title', 'Detail STPD Manual - Borotax Portal')
@section('page-title', 'Detail STPD Manual')

@section('styles')
<style>
    .detail-back { display:inline-flex; align-items:center; gap:8px; margin-bottom:18px; color:var(--text-secondary); font-size:0.84rem; font-weight:600; }
    .detail-grid { display:grid; gap:16px; }
    .detail-card { background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-lg); padding:22px; }
    .detail-card h2, .detail-card h3 { color:var(--text-primary); font-weight:800; margin-bottom:12px; }
    .detail-card h2 { font-size:1.12rem; }
    .detail-card h3 { font-size:0.94rem; }
    .detail-copy { color:var(--text-secondary); font-size:0.84rem; line-height:1.7; }
    .detail-meta { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:12px; }
    .detail-meta-item { background:var(--bg-surface); border-radius:var(--radius-md); padding:14px; }
    .detail-meta-label { font-size:0.72rem; text-transform:uppercase; letter-spacing:0.04em; color:var(--text-tertiary); font-weight:700; margin-bottom:6px; }
    .detail-meta-value { font-size:0.86rem; color:var(--text-primary); font-weight:700; }
    .status-pill { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:999px; font-size:0.76rem; font-weight:800; }
    .status-pill.draft { background:#fef3c7; color:#92400e; }
    .status-pill.disetujui { background:#dcfce7; color:#166534; }
    .status-pill.ditolak { background:#fee2e2; color:#b91c1c; }
    .detail-note { white-space:pre-wrap; color:var(--text-secondary); font-size:0.86rem; line-height:1.7; }
    .detail-actions { display:flex; gap:12px; flex-wrap:wrap; }
    .detail-action-link { display:inline-flex; align-items:center; gap:8px; padding:11px 16px; border-radius:var(--radius-md); background:var(--primary); color:#fff; font-size:0.84rem; font-weight:700; }
    .detail-action-link.secondary { background:var(--bg-surface); color:var(--text-primary); border:1px solid var(--border); }
    @media (max-width: 900px) { .detail-meta { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) { .detail-meta { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
    <a href="{{ route('portal.stpd-manual.index') }}" class="detail-back">
        <i class="bi bi-arrow-left"></i>
        Kembali ke Riwayat STPD Manual
    </a>

    <div class="detail-grid">
        <section class="detail-card">
            <div style="display:flex; justify-content:space-between; gap:16px; align-items:flex-start; flex-wrap:wrap; margin-bottom:14px;">
                <div>
                    <h2>{{ $stpd->nomor_stpd ?? 'Draft STPD Manual' }}</h2>
                    <p class="detail-copy">Detail ini menampilkan informasi STPD manual, status verifikasi, serta riwayat perubahan status untuk dokumen yang terkait dengan billing Anda.</p>
                </div>
                <span class="status-pill {{ $stpd->status }}">{{ str($stpd->status)->headline()->toString() }}</span>
            </div>

            <div class="detail-meta">
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Billing Sumber</div>
                    <div class="detail-meta-value">{{ $stpd->tax?->billing_code ?? '-' }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Jenis Pajak</div>
                    <div class="detail-meta-value">{{ $stpd->tax?->jenisPajak?->nama ?? '-' }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Objek Pajak</div>
                    <div class="detail-meta-value">{{ $stpd->tax?->taxObject?->nama_objek_pajak ?? '-' }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Tanggal Buat</div>
                    <div class="detail-meta-value">{{ $stpd->tanggal_buat?->translatedFormat('d M Y, H:i') ?? '-' }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Tipe STPD</div>
                    <div class="detail-meta-value">{{ $stpd->isTipePokok() ? 'Pokok & Sanksi' : 'Sanksi Saja' }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Bulan Terlambat</div>
                    <div class="detail-meta-value">{{ $stpd->bulan_terlambat }} bulan</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Sanksi</div>
                    <div class="detail-meta-value">Rp {{ number_format((float) $stpd->sanksi_dihitung, 0, ',', '.') }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Pokok Belum Dibayar</div>
                    <div class="detail-meta-value">Rp {{ number_format((float) $stpd->pokok_belum_dibayar, 0, ',', '.') }}</div>
                </div>
            </div>
        </section>

        @if($stpd->catatan_petugas || $stpd->catatan_verifikasi)
            <section class="detail-card">
                <h3>Catatan</h3>
                @if($stpd->catatan_petugas)
                    <div class="detail-note">Catatan petugas: {{ $stpd->catatan_petugas }}</div>
                @endif
                @if($stpd->catatan_verifikasi)
                    <div class="detail-note" style="margin-top:12px;">Catatan verifikasi: {{ $stpd->catatan_verifikasi }}</div>
                @endif
            </section>
        @endif

        @if($stpd->status === 'disetujui')
            <section class="detail-card">
                <h3>Dokumen STPD</h3>
                <div class="detail-actions">
                    <a href="{{ route('stpd-manual.show', $stpd->id) }}" target="_blank" class="detail-action-link">
                        <i class="bi bi-eye"></i>
                        Lihat Dokumen
                    </a>
                    <a href="{{ route('stpd-manual.download', $stpd->id) }}" class="detail-action-link secondary">
                        <i class="bi bi-download"></i>
                        Unduh Dokumen
                    </a>
                </div>
            </section>
        @endif

        <section class="detail-card">
            <div style="--verification-history-border: var(--border); --verification-history-bg: transparent; --verification-history-heading: var(--text-primary); --verification-history-text: var(--text-secondary); --verification-history-muted: var(--text-tertiary); --verification-history-accent: var(--primary); --verification-history-line: var(--border);">
                <x-verification-status-timeline
                    :histories="$stpd->verificationStatusHistories"
                    heading="Riwayat Verifikasi"
                    empty-message="Belum ada riwayat verifikasi untuk STPD manual ini."
                />
            </div>
        </section>
    </div>
@endsection
