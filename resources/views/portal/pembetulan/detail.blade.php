@extends('layouts.portal-dashboard')

@section('title', 'Detail Pembetulan - Borotax Portal')
@section('page-title', 'Detail Pembetulan')

@section('styles')
<style>
    .detail-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 18px;
        color: var(--text-secondary);
        font-size: 0.84rem;
        font-weight: 600;
    }

    .detail-grid {
        display: grid;
        gap: 16px;
    }

    .detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 22px;
    }

    .detail-card h2,
    .detail-card h3 {
        margin-bottom: 12px;
        color: var(--text-primary);
        font-weight: 800;
    }

    .detail-card h2 { font-size: 1.12rem; }
    .detail-card h3 { font-size: 0.94rem; }

    .detail-copy {
        font-size: 0.84rem;
        color: var(--text-secondary);
        line-height: 1.7;
    }

    .detail-meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .detail-meta-item {
        background: var(--bg-surface);
        border-radius: var(--radius-md);
        padding: 14px;
    }

    .detail-meta-label {
        font-size: 0.72rem;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
        font-weight: 700;
    }

    .detail-meta-value {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-primary);
        word-break: break-word;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 800;
    }

    .status-pill.pending { background: #fef3c7; color: #92400e; }
    .status-pill.diproses { background: #dbeafe; color: #1d4ed8; }
    .status-pill.selesai { background: #dcfce7; color: #166534; }
    .status-pill.ditolak { background: #fee2e2; color: #b91c1c; }

    .detail-note {
        white-space: pre-wrap;
        font-size: 0.86rem;
        line-height: 1.7;
        color: var(--text-secondary);
    }

    @media (max-width: 900px) {
        .detail-meta { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 640px) {
        .detail-meta { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
    @php
        $statusClass = str((string) $pembetulanRequest->status)->lower()->replace(' ', '')->value();
        $statusLabel = match ($pembetulanRequest->status) {
            'pending' => 'Menunggu Review',
            'diproses' => 'Sedang Diproses',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            default => str((string) $pembetulanRequest->status)->headline()->toString(),
        };
    @endphp

    <a href="{{ route('portal.pembetulan.index') }}" class="detail-back">
        <i class="bi bi-arrow-left"></i>
        Kembali ke Ajukan Pembetulan
    </a>

    <div class="detail-grid">
        <section class="detail-card">
            <div style="display:flex; justify-content:space-between; gap:16px; align-items:flex-start; flex-wrap:wrap; margin-bottom:14px;">
                <div>
                    <h2>Permohonan Pembetulan Billing</h2>
                    <p class="detail-copy">Detail ini menampilkan ringkasan billing yang diajukan, catatan permohonan, dan histori verifikasi terbaru untuk permintaan pembetulan Anda.</p>
                </div>
                <span class="status-pill {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>

            <div class="detail-meta">
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Billing</div>
                    <div class="detail-meta-value">{{ $pembetulanRequest->tax?->billing_code ?? '-' }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Jenis Pajak</div>
                    <div class="detail-meta-value">{{ $pembetulanRequest->tax?->jenisPajak?->nama ?? '-' }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Objek Pajak</div>
                    <div class="detail-meta-value">{{ $pembetulanRequest->tax?->taxObject?->nama_objek_pajak ?? '-' }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Diajukan</div>
                    <div class="detail-meta-value">{{ $pembetulanRequest->created_at?->translatedFormat('d M Y, H:i') ?? '-' }}</div>
                </div>
            </div>
        </section>

        <section class="detail-card">
            <h3>Alasan Pembetulan</h3>
            <div class="detail-note">{{ $pembetulanRequest->alasan }}</div>
        </section>

        @if($pembetulanRequest->omzet_baru)
            <section class="detail-card">
                <h3>Omzet Baru yang Diusulkan</h3>
                <div class="detail-copy" style="font-size:1rem; font-weight:800; color:var(--text-primary);">Rp {{ number_format((float) $pembetulanRequest->omzet_baru, 0, ',', '.') }}</div>
            </section>
        @endif

        @if($pembetulanRequest->catatan_petugas)
            <section class="detail-card">
                <h3>Catatan Petugas</h3>
                <div class="detail-note">{{ $pembetulanRequest->catatan_petugas }}</div>
            </section>
        @endif

        <section class="detail-card">
            <div style="--verification-history-border: var(--border); --verification-history-bg: transparent; --verification-history-heading: var(--text-primary); --verification-history-text: var(--text-secondary); --verification-history-muted: var(--text-tertiary); --verification-history-accent: var(--primary); --verification-history-line: var(--border);">
                <x-verification-status-timeline
                    :histories="$pembetulanRequest->verificationStatusHistories"
                    heading="Riwayat Verifikasi"
                    empty-message="Belum ada riwayat verifikasi untuk permohonan pembetulan ini."
                />
            </div>
        </section>
    </div>
@endsection
