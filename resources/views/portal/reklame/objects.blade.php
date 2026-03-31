@extends('layouts.portal-dashboard')

@section('title', 'Objek Reklame - Borotax Portal')
@section('page-title', 'Objek Reklame Saya')

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

    /* Top bar */
    .obj-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .obj-topbar h2 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    /* Mobile-only notice */
    .mobile-notice {
        background: #FFF8E1;
        border: 1px solid #FFE082;
        border-radius: var(--radius-lg);
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 0.84rem;
        color: #5D4037;
    }

    .mobile-notice i {
        font-size: 1.3rem;
        color: #F57F17;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .mobile-notice strong {
        display: block;
        margin-bottom: 2px;
        color: #E65100;
    }

    .mobile-notice span {
        font-size: 0.78rem;
        color: #795548;
    }

    /* Object cards */
    .obj-list { display: grid; gap: 14px; }

    .obj-card {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        overflow: hidden;
        transition: all var(--transition);
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .obj-card:hover {
        border-color: var(--primary-light);
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .obj-card-header {
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .obj-card-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        background: #FBE9E7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: #BF360C;
        flex-shrink: 0;
    }

    .obj-card-info { flex: 1; min-width: 0; }

    .obj-card-name {
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .obj-card-addr {
        font-size: 0.78rem;
        color: var(--text-tertiary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .obj-badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: var(--radius-full);
        white-space: nowrap;
    }

    .obj-badge.aktif       { background: #E8F5E9; color: #2E7D32; }
    .obj-badge.kadaluarsa  { background: #FFEBEE; color: #C62828; }
    .obj-badge.pending     { background: #FFF8E1; color: #F57F17; }

    .obj-card-body {
        padding: 0 22px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .obj-meta {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .obj-meta-item {
        font-size: 0.78rem;
        color: var(--text-tertiary);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .obj-meta-item i { font-size: 0.85rem; }
    .obj-meta-item strong { color: var(--text-primary); }

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

    .empty-state p { font-size: 0.85rem; margin-bottom: 20px; }

    /* Alert */
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

    @media (max-width: 768px) {
        .obj-topbar { flex-direction: column; align-items: stretch; }
        .obj-card-body { flex-direction: column; align-items: stretch; }
    }
</style>
@endsection

@section('content')
    <a href="{{ route('portal.reklame.index') }}" class="obj-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Reklame
    </a>

    @session('success')
        <div class="alert-success">
            <i class="bi bi-check-circle-fill"></i>
            {{ $value }}
        </div>
    @endsession

    <div class="obj-topbar">
        <h2><i class="bi bi-signpost-2"></i> Objek Reklame Saya</h2>
    </div>

    {{-- Mobile-only notice --}}
    <div class="mobile-notice">
        <i class="bi bi-phone"></i>
        <div>
            <strong>Pendaftaran objek reklame baru hanya tersedia di aplikasi mobile Borotax.</strong>
            <span>Gunakan aplikasi mobile atau kunjungi kantor Bapenda untuk mendaftarkan objek reklame baru. Perpanjangan dapat dilakukan melalui halaman detail objek.</span>
        </div>
    </div>

    @if($objects->isEmpty())
        <div class="empty-state">
            <i class="bi bi-signpost-2"></i>
            <h3>Belum Ada Objek</h3>
            <p>Anda belum memiliki objek reklame terdaftar. Gunakan aplikasi mobile Borotax untuk mendaftarkan objek baru.</p>
        </div>
    @else
        <div class="obj-list">
            @foreach($objects as $obj)
                @php
                    $isExpired = $obj->isKadaluarsa();
                    $statusClass = match($obj->status) {
                        'aktif' => $isExpired ? 'kadaluarsa' : 'aktif',
                        'kadaluarsa' => 'kadaluarsa',
                        default => 'pending',
                    };
                    $statusLabel = match($obj->status) {
                        'aktif' => $isExpired ? 'Kadaluarsa' : 'Aktif',
                        'kadaluarsa' => 'Kadaluarsa',
                        default => 'Pending',
                    };
                @endphp
                <a href="{{ route('portal.reklame.object-detail', $obj->id) }}" class="obj-card">
                    <div class="obj-card-header">
                        <div class="obj-card-icon"><i class="bi bi-signpost-2-fill"></i></div>
                        <div class="obj-card-info">
                            <div class="obj-card-name">{{ $obj->nama_reklame }}</div>
                            <div class="obj-card-addr">{{ $obj->alamat_reklame }}, {{ $obj->kelurahan }}, {{ $obj->kecamatan }}</div>
                        </div>
                        <span class="obj-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="obj-card-body">
                        <div class="obj-meta">
                            <div class="obj-meta-item">
                                <i class="bi bi-aspect-ratio"></i>
                                {{ $obj->ukuran_formatted }}
                            </div>
                            <div class="obj-meta-item">
                                <i class="bi bi-layers"></i>
                                Muka: <strong>{{ $obj->jumlah_muka }}</strong>
                            </div>
                            <div class="obj-meta-item">
                                <i class="bi bi-geo-alt"></i>
                                Kel. Lokasi: <strong>{{ $obj->kelompok_lokasi }}</strong>
                            </div>
                            @if($obj->masa_berlaku_sampai)
                                <div class="obj-meta-item">
                                    <i class="bi bi-calendar3"></i>
                                    s/d {{ $obj->masa_berlaku_sampai->translatedFormat('d M Y') }}
                                    @if(!$isExpired && $obj->sisa_hari <= 30)
                                        <strong style="color: #F57F17;">({{ $obj->sisa_hari }} hari)</strong>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
