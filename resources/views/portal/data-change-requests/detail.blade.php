@extends('layouts.portal-dashboard')

@section('title', 'Detail Perubahan Data - Borotax Portal')
@section('page-title', 'Detail Perubahan Data')

@section('styles')
<style>
    .detail-back { display:inline-flex; align-items:center; gap:8px; margin-bottom:18px; color:var(--text-secondary); font-size:0.84rem; font-weight:600; }
    .detail-grid { display:grid; gap:16px; }
    .detail-card { background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-lg); padding:22px; }
    .detail-card h2, .detail-card h3 { color:var(--text-primary); font-weight:800; margin-bottom:12px; }
    .detail-card h2 { font-size:1.12rem; }
    .detail-card h3 { font-size:0.94rem; }
    .detail-copy { color:var(--text-secondary); font-size:0.84rem; line-height:1.7; }
    .detail-table { width:100%; border-collapse:collapse; }
    .detail-table th, .detail-table td { padding:12px 14px; border-bottom:1px solid var(--border-light); font-size:0.84rem; text-align:left; vertical-align:top; }
    .detail-table th { background:var(--bg-surface); color:var(--text-tertiary); font-size:0.74rem; text-transform:uppercase; letter-spacing:0.04em; }
    .detail-table td { color:var(--text-secondary); }
    .detail-table strong { color:var(--text-primary); }
    .detail-meta { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:12px; }
    .detail-meta-item { background:var(--bg-surface); border-radius:var(--radius-md); padding:14px; }
    .detail-meta-label { font-size:0.72rem; text-transform:uppercase; letter-spacing:0.04em; color:var(--text-tertiary); font-weight:700; margin-bottom:6px; }
    .detail-meta-value { font-size:0.86rem; color:var(--text-primary); font-weight:700; }
    .status-pill { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:999px; font-size:0.76rem; font-weight:800; }
    .status-pill.pending { background:#fef3c7; color:#92400e; }
    .status-pill.approved { background:#dcfce7; color:#166534; }
    .status-pill.rejected { background:#fee2e2; color:#b91c1c; }
    .detail-note { white-space:pre-wrap; color:var(--text-secondary); font-size:0.86rem; line-height:1.7; }
    @media (max-width: 768px) { .detail-meta { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
    @php($statusClass = str($requestRecord->status)->lower()->value())

    <a href="{{ route('portal.data-change-requests.index') }}" class="detail-back">
        <i class="bi bi-arrow-left"></i>
        Kembali ke Riwayat Perubahan Data
    </a>

    <div class="detail-grid">
        <section class="detail-card">
            <div style="display:flex; justify-content:space-between; gap:16px; align-items:flex-start; flex-wrap:wrap; margin-bottom:14px;">
                <div>
                    <h2>Permintaan Perubahan {{ $requestRecord->getEntityTypeLabel() }}</h2>
                    <p class="detail-copy">Detail ini menampilkan field yang diajukan untuk diubah, alasan perubahan, dan histori verifikasi permintaan perubahan data.</p>
                </div>
                <span class="status-pill {{ $statusClass }}">{{ $requestRecord->getStatusLabel() }}</span>
            </div>

            <div class="detail-meta">
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Tipe Data</div>
                    <div class="detail-meta-value">{{ $requestRecord->getEntityTypeLabel() }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Diajukan</div>
                    <div class="detail-meta-value">{{ $requestRecord->created_at?->translatedFormat('d M Y, H:i') ?? '-' }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-meta-label">Direview</div>
                    <div class="detail-meta-value">{{ $requestRecord->reviewed_at?->translatedFormat('d M Y, H:i') ?? 'Belum direview' }}</div>
                </div>
            </div>
        </section>

        <section class="detail-card">
            <h3>Field yang Diajukan</h3>
            @if(is_array($requestRecord->field_changes) && $requestRecord->field_changes !== [])
                <div style="overflow-x:auto;">
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Nilai Lama</th>
                                <th>Nilai Baru</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requestRecord->field_changes as $field => $change)
                                <tr>
                                    <td><strong>{{ str($field)->replace('_', ' ')->headline()->toString() }}</strong></td>
                                    <td>{{ $change['old'] ?? '-' }}</td>
                                    <td>{{ $change['new'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="detail-copy">Tidak ada field perubahan yang bisa ditampilkan.</div>
            @endif
        </section>

        <section class="detail-card">
            <h3>Alasan dan Catatan</h3>
            <div class="detail-note">{{ $requestRecord->alasan_perubahan }}</div>
            @if($requestRecord->catatan_review)
                <div style="margin-top:14px; padding-top:14px; border-top:1px solid var(--border-light);">
                    <div style="font-size:0.78rem; font-weight:800; color:var(--text-tertiary); margin-bottom:6px;">Catatan Review</div>
                    <div class="detail-note">{{ $requestRecord->catatan_review }}</div>
                </div>
            @endif
        </section>

        <section class="detail-card">
            <div style="--verification-history-border: var(--border); --verification-history-bg: transparent; --verification-history-heading: var(--text-primary); --verification-history-text: var(--text-secondary); --verification-history-muted: var(--text-tertiary); --verification-history-accent: var(--primary); --verification-history-line: var(--border);">
                <x-verification-status-timeline
                    :histories="$requestRecord->verificationStatusHistories"
                    heading="Riwayat Verifikasi"
                    empty-message="Belum ada riwayat verifikasi untuk permintaan perubahan data ini."
                />
            </div>
        </section>
    </div>
@endsection
