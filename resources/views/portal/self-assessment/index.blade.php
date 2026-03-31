@extends('layouts.portal-dashboard')

@section('title', 'Self Assessment - Borotax Portal')
@section('page-title', 'Self Assessment')

@section('styles')
<style>
    .sa-header {
        background: linear-gradient(140deg, var(--secondary) 0%, var(--secondary-dark) 100%);
        border-radius: var(--radius-xl);
        padding: 28px 32px;
        color: var(--text-white);
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .sa-header::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -3%;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(108,172,207,0.18), transparent 70%);
    }

    .sa-header-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-lg);
        background: rgba(255,255,255,0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
        position: relative;
    }

    .sa-header-content { position: relative; }

    .sa-header h2 {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .sa-header p {
        color: rgba(255,255,255,0.55);
        font-size: 0.85rem;
    }

    .section-label {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .section-sublabel {
        font-size: 0.82rem;
        color: var(--text-tertiary);
        margin-bottom: 18px;
    }

    .tax-type-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .tax-type-card {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        padding: 24px;
        transition: all var(--transition);
        text-decoration: none;
        color: inherit;
        display: block;
        position: relative;
    }

    .tax-type-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .tax-type-card .tc-top {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 14px;
    }

    .tax-type-card .tc-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .tax-type-card .tc-icon.hotel { background: #E3F2FD; }
    .tax-type-card .tc-icon.restoran { background: #FFF3E0; }
    .tax-type-card .tc-icon.hiburan { background: #F3E5F5; }
    .tax-type-card .tc-icon.parkir { background: #E8F5E9; }

    .tax-type-card .tc-info { flex: 1; min-width: 0; }

    .tax-type-card .tc-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .tax-type-card .tc-desc {
        font-size: 0.78rem;
        color: var(--text-tertiary);
        line-height: 1.5;
    }

    .tax-type-card .tc-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .tax-type-card .tc-objects {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: var(--text-tertiary);
    }

    .tax-type-card .tc-objects i { font-size: 0.9rem; }

    .tax-type-card .tc-objects .count {
        font-weight: 700;
        color: var(--primary-dark);
    }

    .tax-type-card .tc-tarif {
        background: var(--primary-50);
        color: var(--primary-dark);
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: var(--radius-full);
    }

    .tax-type-card .tc-arrow {
        position: absolute;
        top: 24px;
        right: 24px;
        color: var(--border);
        font-size: 1rem;
        transition: all var(--transition);
    }

    .tax-type-card:hover .tc-arrow { color: var(--primary); transform: translateX(3px); }

    @media (max-width: 768px) {
        .tax-type-grid { grid-template-columns: 1fr; }
        .sa-header { padding: 22px 20px; }
    }
</style>
@endsection

@section('content')
    {{-- Header --}}
    <div class="sa-header">
        <div class="sa-header-icon"><i class="bi bi-file-earmark-text"></i></div>
        <div class="sa-header-content">
            <h2>Self Assessment</h2>
            <p>Laporkan omzet bulanan & bayar pajak secara mandiri</p>
        </div>
    </div>

    {{-- Tax Type Cards --}}
    <div class="section-label">Jenis Pajak Tersedia</div>
    <div class="section-sublabel">Pilih jenis pajak untuk membuat billing pembayaran</div>

    <div class="tax-type-grid">
        @foreach($jenisPajak as $jp)
            @php
                $count = $taxObjectCounts[$jp->id] ?? 0;
                $iconClass = strtolower($jp->nama_singkat ?? '');
            @endphp
            <a href="{{ route('portal.self-assessment.create', $jp->id) }}" class="tax-type-card">
                <i class="bi bi-arrow-right tc-arrow"></i>
                <div class="tc-top">
                    <div class="tc-icon {{ $iconClass }}">{{ $jp->icon }}</div>
                    <div class="tc-info">
                        <div class="tc-name">{{ $jp->nama }}</div>
                        <div class="tc-desc">{{ $jp->deskripsi }}</div>
                    </div>
                </div>
                <div class="tc-bottom">
                    <div class="tc-objects">
                        <i class="bi bi-building"></i>
                        <span class="count">{{ $count }}</span> objek pajak terdaftar
                    </div>
                    <span class="tc-tarif">Tarif {{ number_format($jp->tarif_default, 0) }}%</span>
                </div>
            </a>
        @endforeach
    </div>
@endsection