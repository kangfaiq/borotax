@extends('layouts.portal-dashboard')

@section('title', 'STPD Manual - Borotax Portal')
@section('page-title', 'STPD Manual')

@section('styles')
<style>
    .owner-page-header { margin-bottom: 22px; }
    .owner-page-header h2 { font-size: 1.16rem; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; }
    .owner-page-header p { font-size: 0.84rem; color: var(--text-secondary); max-width: 760px; }
    .owner-grid { display: grid; gap: 16px; }
    .owner-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 20px; }
    .owner-card-head { display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start; margin-bottom:14px; }
    .owner-card-title { font-size: 1rem; font-weight: 800; color: var(--text-primary); margin-bottom: 4px; }
    .owner-card-copy { font-size: 0.82rem; color: var(--text-secondary); }
    .owner-chip { display:inline-flex; align-items:center; gap:6px; padding:7px 12px; border-radius:999px; font-size:0.74rem; font-weight:800; }
    .owner-chip.draft { background:#fef3c7; color:#92400e; }
    .owner-chip.disetujui { background:#dcfce7; color:#166534; }
    .owner-chip.ditolak { background:#fee2e2; color:#b91c1c; }
    .owner-meta { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:12px; }
    .owner-meta-item { background: var(--bg-surface); border-radius: var(--radius-md); padding: 14px; }
    .owner-meta-label { font-size:0.7rem; text-transform:uppercase; letter-spacing:0.04em; color:var(--text-tertiary); font-weight:700; margin-bottom:6px; }
    .owner-meta-value { font-size:0.86rem; color:var(--text-primary); font-weight:700; }
    .owner-link { display:inline-flex; align-items:center; gap:8px; margin-top:16px; color:var(--primary); font-size:0.84rem; font-weight:700; }
    .owner-empty { background: var(--bg-card); border:1px dashed var(--border); border-radius:var(--radius-lg); padding:40px 24px; text-align:center; color:var(--text-secondary); }
    .owner-empty i { font-size:2rem; display:block; margin-bottom:12px; color:var(--text-tertiary); }
    @media (max-width: 900px) { .owner-meta { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 768px) { .owner-meta { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
    <div class="owner-page-header">
        <h2>Riwayat STPD Manual</h2>
        <p>Daftar ini menampilkan STPD manual yang terkait dengan billing Anda, termasuk draft yang masih diverifikasi, dokumen yang sudah disetujui, dan STPD yang ditolak.</p>
    </div>

    <div class="owner-grid">
        @forelse($stpds as $stpd)
            <article class="owner-card">
                <div class="owner-card-head">
                    <div>
                        <div class="owner-card-title">{{ $stpd->nomor_stpd ?? 'Draft STPD Manual' }}</div>
                        <div class="owner-card-copy">Billing sumber: {{ $stpd->tax?->billing_code ?? '-' }}</div>
                    </div>
                    <span class="owner-chip {{ $stpd->status }}">{{ str($stpd->status)->headline()->toString() }}</span>
                </div>

                <div class="owner-meta">
                    <div class="owner-meta-item">
                        <div class="owner-meta-label">Jenis Pajak</div>
                        <div class="owner-meta-value">{{ $stpd->tax?->jenisPajak?->nama ?? '-' }}</div>
                    </div>
                    <div class="owner-meta-item">
                        <div class="owner-meta-label">Objek Pajak</div>
                        <div class="owner-meta-value">{{ $stpd->tax?->taxObject?->nama_objek_pajak ?? '-' }}</div>
                    </div>
                    <div class="owner-meta-item">
                        <div class="owner-meta-label">Tipe STPD</div>
                        <div class="owner-meta-value">{{ $stpd->isTipePokok() ? 'Pokok & Sanksi' : 'Sanksi Saja' }}</div>
                    </div>
                    <div class="owner-meta-item">
                        <div class="owner-meta-label">Dibuat</div>
                        <div class="owner-meta-value">{{ $stpd->tanggal_buat?->translatedFormat('d M Y, H:i') ?? '-' }}</div>
                    </div>
                </div>

                <a href="{{ route('portal.stpd-manual.show', $stpd->id) }}" class="owner-link">
                    <i class="bi bi-arrow-right-circle"></i>
                    Lihat Detail STPD Manual
                </a>
            </article>
        @empty
            <div class="owner-empty">
                <i class="bi bi-inbox"></i>
                <div>Belum ada STPD manual yang terkait dengan billing Anda.</div>
            </div>
        @endforelse
    </div>

    @if($stpds->hasPages())
        <div style="margin-top:18px;">{{ $stpds->links() }}</div>
    @endif
@endsection
