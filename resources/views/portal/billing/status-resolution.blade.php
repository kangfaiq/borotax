@extends('layouts.portal-dashboard')

@section('title', 'Status Billing - Borotax Portal')
@section('page-title', 'Status Billing')

@section('styles')
<style>
    .status-wrap {
        display: grid;
        gap: 20px;
    }

    .status-hero {
        background: linear-gradient(135deg, rgba(14, 116, 144, 0.08), rgba(15, 118, 110, 0.12));
        border: 1px solid rgba(14, 116, 144, 0.18);
        border-radius: 24px;
        padding: 28px 30px;
    }

    .status-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(14, 116, 144, 0.12);
        color: #0f766e;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    .status-hero h2 {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 10px;
    }

    .status-hero p {
        color: var(--text-secondary);
        line-height: 1.7;
        max-width: 900px;
        margin: 0;
    }

    .status-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .status-card {
        border-radius: 24px;
        border: 1px solid var(--border);
        background: var(--bg-card);
        box-shadow: var(--shadow-sm);
        padding: 24px;
        display: grid;
        gap: 16px;
    }

    .status-card.current {
        border-color: rgba(22, 163, 74, 0.24);
        box-shadow: 0 20px 40px rgba(22, 163, 74, 0.08);
    }

    .status-card.scanned {
        border-color: rgba(148, 163, 184, 0.28);
    }

    .status-card header {
        display: grid;
        gap: 8px;
    }

    .status-card h3 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--text-primary);
    }

    .status-card p {
        margin: 0;
        color: var(--text-secondary);
        line-height: 1.65;
        font-size: 0.9rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .status-badge.pending { background: #FEF3C7; color: #92400E; }
    .status-badge.verified { background: #DBEAFE; color: #1D4ED8; }
    .status-badge.paid { background: #DCFCE7; color: #166534; }
    .status-badge.partially_paid { background: #E0F2FE; color: #075985; }
    .status-badge.cancelled { background: #E5E7EB; color: #374151; }
    .status-badge.expired { background: #E5E7EB; color: #4B5563; }
    .status-badge.rejected { background: #FEE2E2; color: #B91C1C; }

    .status-meta {
        display: grid;
        gap: 12px;
    }

    .status-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border-light);
    }

    .status-row:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    .status-label {
        color: var(--text-tertiary);
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
    }

    .status-value {
        text-align: right;
        color: var(--text-primary);
        font-weight: 700;
    }

    .status-note {
        padding: 14px 16px;
        border-radius: 16px;
        background: var(--bg-surface-variant);
        color: var(--text-secondary);
        line-height: 1.6;
        font-size: 0.88rem;
    }

    .status-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .status-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 0 18px;
        border-radius: 14px;
        text-decoration: none;
        font-weight: 700;
        transition: all var(--transition);
    }

    .status-btn.primary {
        background: linear-gradient(135deg, #0f766e, #0f9f8d);
        color: white;
        box-shadow: 0 14px 28px rgba(15, 118, 110, 0.18);
    }

    .status-btn.secondary {
        background: var(--bg-surface-variant);
        color: var(--text-primary);
        border: 1px solid var(--border);
    }

    @media (max-width: 960px) {
        .status-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
@php
    $scannedStatus = $scannedTax->status->value;
    $latestStatus = $latestTax->status->value;
@endphp

<div class="status-wrap">
    <section class="status-hero">
        <div class="status-kicker">{{ $contextKicker ?? 'Resolusi Pembetulan' }}</div>
        <h2>{{ $contextTitle ?? 'Billing yang dipindai sudah memiliki pembetulan yang lebih baru' }}</h2>
        <p>{{ $resolutionMessage }}</p>
    </section>

    <div class="status-grid">
        <section class="status-card scanned">
            <header>
                <span class="status-badge {{ $scannedStatus }}">{{ $scannedTax->status->getLabel() }}</span>
                <h3>Dokumen yang Dipindai</h3>
                <p>QR pada cetakan lama tetap mengarah ke billing asal agar jejak audit tidak berubah.</p>
            </header>

            <div class="status-meta">
                <div class="status-row">
                    <span class="status-label">Kode Billing</span>
                    <span class="status-value">{{ $scannedTax->billing_code }}</span>
                </div>
                <div class="status-row">
                    <span class="status-label">Jenis Pajak</span>
                    <span class="status-value">{{ $scannedTax->jenisPajak->nama ?? '-' }}</span>
                </div>
                <div class="status-row">
                    <span class="status-label">Masa Pajak</span>
                    <span class="status-value">
                        {{ sprintf('%02d', (int) $scannedTax->masa_pajak_bulan) }}/{{ $scannedTax->masa_pajak_tahun }}
                    </span>
                </div>
                <div class="status-row">
                    <span class="status-label">Peran Dokumen</span>
                    <span class="status-value">
                        {{ $scannedTax->pembetulan_ke > 0 ? 'Pembetulan Ke-' . $scannedTax->pembetulan_ke : 'Original' }}
                    </span>
                </div>
            </div>

            <div class="status-note">{{ $scannedDocumentNote }}</div>

            <div class="status-actions">
                <a href="{{ $scannedDocumentUrl ?? route('portal.billing.document.show', ['taxId' => $scannedTax->id, 'historical' => 1]) }}" target="_blank" class="status-btn secondary">
                    Lihat Billing yang Dipindai
                </a>
                @if($scannedTax->status === \App\Enums\TaxStatus::Paid && $scannedTax->sptpd_number)
                    <a href="{{ route('portal.sptpd.show', $scannedTax->id) }}" target="_blank" class="status-btn secondary">
                        Lihat SPTPD Historis
                    </a>
                @endif
            </div>
        </section>

        <section class="status-card current">
            <header>
                <span class="status-badge {{ $latestStatus }}">{{ $latestTax->status->getLabel() }}</span>
                <h3>Dokumen Terbaru yang Berlaku</h3>
                <p>Gunakan dokumen ini untuk melihat status kewajiban paling mutakhir setelah pembetulan terakhir.</p>
            </header>

            <div class="status-meta">
                <div class="status-row">
                    <span class="status-label">Kode Billing</span>
                    <span class="status-value">{{ $latestTax->billing_code }}</span>
                </div>
                <div class="status-row">
                    <span class="status-label">Status</span>
                    <span class="status-value">{{ $latestTax->status->getLabel() }}</span>
                </div>
                <div class="status-row">
                    <span class="status-label">Pembetulan</span>
                    <span class="status-value">Pembetulan Ke-{{ max(1, (int) $latestTax->pembetulan_ke) }}</span>
                </div>
                <div class="status-row">
                    <span class="status-label">Referensi Asal</span>
                    <span class="status-value">{{ $latestTax->parent?->billing_code ?? $scannedTax->billing_code }}</span>
                </div>
            </div>

            <div class="status-note">
                @if($latestTax->status === \App\Enums\TaxStatus::Paid && $latestTax->sptpd_number)
                    Billing pembetulan terbaru sudah lunas dan SPTPD-nya telah tersedia.
                @else
                    Billing pembetulan terbaru inilah yang harus dipakai untuk memeriksa status aktif pembayaran.
                @endif
            </div>

            <div class="status-actions">
                <a href="{{ $latestDocument['url'] }}" target="_blank" class="status-btn primary">
                    {{ $latestDocument['label'] }}
                </a>
            </div>
        </section>
    </div>
</div>
@endsection