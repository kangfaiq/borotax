@extends('layouts.portal-dashboard')

@section('title', 'Permohonan Sewa Reklame - Borotax Portal')
@section('page-title', 'Permohonan Sewa Reklame')

@section('styles')
<style>
    /* Alert */
    .sewa-alert {
        border-radius: var(--radius-lg);
        padding: 14px 20px;
        margin-bottom: 20px;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sewa-alert.success { background: #E8F5E9; color: #2E7D32; border: 1px solid #A5D6A7; }
    .sewa-alert.error   { background: #FFEBEE; color: #C62828; border: 1px solid #EF9A9A; }

    /* Action bar */
    .sewa-action-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        gap: 12px;
        flex-wrap: wrap;
    }

    .sewa-action-bar h3 {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .btn-ajukan {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: linear-gradient(140deg, #42A5F5 0%, #1565C0 100%);
        color: #fff;
        border-radius: var(--radius-full);
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
        transition: all var(--transition);
    }

    .btn-ajukan:hover { box-shadow: var(--shadow-lg); transform: translateY(-1px); color: #fff; }

    /* Cards */
    .sewa-list { display: grid; gap: 14px; }

    .sewa-card {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        overflow: hidden;
        transition: all var(--transition);
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .sewa-card:hover {
        border-color: var(--primary-light);
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .sewa-card-top {
        padding: 18px 22px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .sewa-card-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .sewa-card-icon.diajukan      { background: #E3F2FD; color: #1565C0; }
    .sewa-card-icon.perlu_revisi   { background: #E3F2FD; color: #0277BD; }
    .sewa-card-icon.diproses       { background: #FFF8E1; color: #F57F17; }
    .sewa-card-icon.disetujui      { background: #E8F5E9; color: #2E7D32; }
    .sewa-card-icon.ditolak        { background: #FFEBEE; color: #C62828; }

    .sewa-card-info { flex: 1; min-width: 0; }

    .sewa-card-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .sewa-card-sub {
        font-size: 0.78rem;
        color: var(--text-tertiary);
        margin-bottom: 6px;
    }

    .sewa-card-meta {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .sewa-card-meta span {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .sewa-card-meta span strong { color: var(--text-primary); }

    .sewa-status-badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: var(--radius-full);
        white-space: nowrap;
        margin-left: auto;
        align-self: flex-start;
    }

    .sewa-status-badge.diajukan      { background: #E3F2FD; color: #1565C0; }
    .sewa-status-badge.perlu_revisi   { background: #E3F2FD; color: #0277BD; }
    .sewa-status-badge.diproses       { background: #FFF8E1; color: #F57F17; }
    .sewa-status-badge.disetujui      { background: #E8F5E9; color: #2E7D32; }
    .sewa-status-badge.ditolak        { background: #FFEBEE; color: #C62828; }

    /* Empty state */
    .sewa-empty {
        text-align: center;
        padding: 60px 20px;
        background: var(--bg-card);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border);
    }

    .sewa-empty i {
        font-size: 2.5rem;
        color: var(--text-tertiary);
        margin-bottom: 16px;
    }

    .sewa-empty h4 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 6px;
    }

    .sewa-empty p {
        font-size: 0.85rem;
        color: var(--text-tertiary);
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .sewa-card-top { flex-wrap: wrap; }
        .sewa-status-badge { margin-left: 0; }
    }
</style>
@endsection

@section('content')
    @session('success')
        <div class="sewa-alert success"><i class="bi bi-check-circle-fill"></i> {{ $value }}</div>
    @endsession
    @session('error')
        <div class="sewa-alert error"><i class="bi bi-exclamation-triangle-fill"></i> {{ $value }}</div>
    @endsession

    <div class="sewa-action-bar">
        <h3>Riwayat Permohonan Sewa</h3>
        <a href="{{ route('publik.sewa-reklame') }}" class="btn-ajukan">
            <i class="bi bi-plus-lg"></i> Ajukan Sewa Baru
        </a>
    </div>

    @if($permohonan->isEmpty())
        <div class="sewa-empty">
            <i class="bi bi-clipboard-x"></i>
            <h4>Belum Ada Permohonan</h4>
            <p>Anda belum pernah mengajukan sewa reklame. Kunjungi halaman daftar aset untuk memilih titik reklame.</p>
            <a href="{{ route('publik.sewa-reklame') }}" class="btn-ajukan">
                <i class="bi bi-signpost-2"></i> Lihat Daftar Aset
            </a>
        </div>
    @else
        <div class="sewa-list">
            @foreach($permohonan as $item)
                <a href="{{ route('portal.reklame.sewa-detail', $item->id) }}" class="sewa-card">
                    <div class="sewa-card-top">
                        <div class="sewa-card-icon {{ $item->status }}">
                            <i class="bi bi-{{ $item->status === 'disetujui' ? 'check-circle' : ($item->status === 'ditolak' ? 'x-circle' : ($item->status === 'diproses' ? 'hourglass-split' : ($item->status === 'perlu_revisi' ? 'arrow-repeat' : 'clock'))) }}"></i>
                        </div>
                        <div class="sewa-card-info">
                            <div class="sewa-card-title">
                                {{ $item->asetReklame->nama ?? 'Aset Tidak Ditemukan' }}
                            </div>
                            <div class="sewa-card-sub">
                                <strong>{{ $item->nomor_tiket }}</strong> &bull; {{ $item->asetReklame->kode_aset ?? '-' }} &bull; {{ $item->asetReklame->lokasi ?? '-' }}
                            </div>
                            <div class="sewa-card-meta">
                                <span><i class="bi bi-calendar3"></i> Diajukan: <strong>{{ $item->created_at->translatedFormat('d M Y') }}</strong></span>
                                <span><i class="bi bi-clock-history"></i> Durasi: <strong>{{ $item->durasi_sewa_hari }} hari</strong></span>
                                <span><i class="bi bi-calendar-event"></i> Mulai: <strong>{{ \Carbon\Carbon::parse($item->tanggal_mulai_diinginkan)->translatedFormat('d M Y') }}</strong></span>
                            </div>
                        </div>
                        @php
                            $statusLabels = [
                                'diajukan' => 'Diajukan',
                                'perlu_revisi' => 'Perlu Revisi',
                                'diproses' => 'Diproses',
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                            ];
                        @endphp
                        <span class="sewa-status-badge {{ $item->status }}">
                            {{ $statusLabels[$item->status] ?? ucfirst($item->status) }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
