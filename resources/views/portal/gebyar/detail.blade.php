@extends('layouts.portal-dashboard')

@section('title', 'Detail Gebyar Pajak - Borotax Portal')
@section('page-title', 'Detail Gebyar Pajak')

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
    .status-pill.pending { background:#fef3c7; color:#92400e; }
    .status-pill.approved { background:#dcfce7; color:#166534; }
    .status-pill.rejected { background:#fee2e2; color:#b91c1c; }
    .detail-note { white-space:pre-wrap; color:var(--text-secondary); font-size:0.86rem; line-height:1.7; }
    .detail-image { width:100%; max-width:360px; border-radius:16px; border:1px solid var(--border); display:block; }
    @media (max-width: 900px) { .detail-meta { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) { .detail-meta { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
    <a href="{{ route('portal.gebyar.index') }}" class="detail-back">
        <i class="bi bi-arrow-left"></i>
        Kembali ke Riwayat Gebyar Pajak
    </a>

    <div class="detail-grid">
        <section class="detail-card">
            <div style="display:flex; justify-content:space-between; gap:16px; align-items:flex-start; flex-wrap:wrap; margin-bottom:14px;">
                <div>
                    <h2>{{ $submission->place_name ?? 'Pengajuan Gebyar Pajak' }}</h2>
                    <p class="detail-copy">Detail ini menampilkan bukti transaksi, status verifikasi, dan histori proses verifikasi untuk pengajuan Gebyar Pajak Anda.</p>
                </div>
                <span class="status-pill {{ $submission->status }}">{{ $submission->status_label }}</span>
            </div>

            <div class="detail-meta">
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Jenis Pajak</div>
                    <div class="detail-meta-value">{{ $submission->jenisPajak?->nama ?? '-' }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Tanggal Transaksi</div>
                    <div class="detail-meta-value">{{ $submission->transaction_date?->translatedFormat('d M Y') ?? '-' }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Nominal</div>
                    <div class="detail-meta-value">Rp {{ number_format((float) $submission->transaction_amount, 0, ',', '.') }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Kupon</div>
                    <div class="detail-meta-value">{{ $submission->kupon_count }}</div>
                </div>
            </div>
        </section>

        @if($submission->image_url)
            <section class="detail-card">
                <h3>Bukti Transaksi</h3>
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($submission->image_url) }}" alt="Bukti transaksi Gebyar" class="detail-image">
            </section>
        @endif

        @if($submission->rejection_reason)
            <section class="detail-card">
                <h3>Catatan Verifikator</h3>
                <div class="detail-note">{{ $submission->rejection_reason }}</div>
            </section>
        @endif

        <section class="detail-card">
            <div style="--verification-history-border: var(--border); --verification-history-bg: transparent; --verification-history-heading: var(--text-primary); --verification-history-text: var(--text-secondary); --verification-history-muted: var(--text-tertiary); --verification-history-accent: var(--primary); --verification-history-line: var(--border);">
                <x-verification-status-timeline
                    :histories="$submission->verificationStatusHistories"
                    heading="Riwayat Verifikasi"
                    empty-message="Belum ada riwayat verifikasi untuk pengajuan Gebyar ini."
                />
            </div>
        </section>
    </div>
@endsection
