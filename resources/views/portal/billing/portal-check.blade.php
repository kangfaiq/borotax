@extends('layouts.portal-dashboard')

@section('title', 'Cek Billing - Borotax Portal')
@section('page-title', 'Cek Billing')

@section('styles')
<style>
    .billing-box {
        background: var(--bg-card);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border);
        padding: 36px 32px;
        max-width: 620px;
        box-shadow: var(--shadow-sm);
    }

    .billing-box h2 {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .billing-box .subtitle {
        color: var(--text-secondary);
        font-size: 0.85rem;
        margin-bottom: 24px;
        line-height: 1.6;
    }

    .billing-input-group {
        display: flex;
        gap: 10px;
    }

    .billing-input {
        flex: 1;
        padding: 12px 16px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-md);
        font-size: 0.9rem;
        font-family: inherit;
        transition: all var(--transition);
        outline: none;
        background: var(--bg-surface-variant);
    }

    .billing-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(108,172,207,0.15);
        background: var(--bg-card);
    }

    .btn-check {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 12px 22px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-family: inherit;
        font-size: 0.88rem;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition);
        white-space: nowrap;
    }

    .btn-check:hover {
        background: var(--primary-dark);
        box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
    }

    /* Result card */
    .result-card {
        margin-top: 24px;
        border-radius: var(--radius-lg);
        overflow: hidden;
        border: 1px solid var(--border);
    }

    .result-header {
        padding: 14px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 700;
        font-size: 0.88rem;
    }

    .result-header.pending {
        background: var(--warning-light);
        color: #E65100;
        border-bottom: 1px solid #FFE0B2;
    }

    .result-header.paid,
    .result-header.lunas {
        background: var(--success-light);
        color: #2E7D32;
        border-bottom: 1px solid #C8E6C9;
    }

    .result-header.verified {
        background: var(--info-light);
        color: #1565C0;
        border-bottom: 1px solid #BBDEFB;
    }

    .result-header.expired {
        background: var(--bg-surface-variant);
        color: var(--text-tertiary);
        border-bottom: 1px solid var(--border);
    }

    .result-body { padding: 18px 20px; }

    .result-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid var(--border-light);
    }

    .result-row:last-child { border-bottom: none; }

    .result-row .label {
        color: var(--text-secondary);
        font-size: 0.84rem;
    }

    .result-row .value {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.84rem;
    }

    .result-total {
        display: flex;
        justify-content: space-between;
        padding: 14px 20px;
        background: var(--primary-50);
        border-top: 1.5px solid var(--border);
    }

    .result-total .label { font-weight: 700; color: var(--text-primary); }
    .result-total .value { font-weight: 800; font-size: 1.05rem; color: var(--primary-dark); }

    .not-found {
        text-align: center;
        padding: 32px;
        color: var(--text-secondary);
    }

    .not-found i {
        font-size: 2rem;
        margin-bottom: 8px;
        display: block;
        color: var(--error);
    }

    .result-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 10px;
        padding: 16px 20px;
        border-top: 1px solid var(--border);
    }

    .result-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 12px;
        border-radius: var(--radius-md);
        font-size: 0.84rem;
        font-weight: 600;
        text-decoration: none;
        color: #fff;
        transition: transform var(--transition), box-shadow var(--transition), filter var(--transition);
    }

    .result-action:hover {
        transform: translateY(-1px);
        filter: brightness(0.98);
    }

    .result-action.billing-print,
    .result-action.sptpd-print {
        background: #3b82f6;
    }

    .result-action.billing-download,
    .result-action.sptpd-download {
        background: #10b981;
    }

    .result-action.stpd-print,
    .result-action.stpd-download {
        background: #f59e0b;
    }

    .result-note {
        margin: 0 20px 20px;
        padding: 12px 14px;
        border-radius: var(--radius-md);
        background: var(--info-light);
        border: 1px solid rgba(59, 130, 246, 0.18);
        color: #1d4ed8;
        font-size: 0.8rem;
        line-height: 1.6;
    }

    @media (max-width: 640px) {
        .billing-box { padding: 24px 18px; }
        .billing-input-group { flex-direction: column; }
    }
</style>
@endsection

@section('content')
    <div class="billing-box">
        <h2><i class="bi bi-receipt" style="color: var(--primary);"></i> Cek Status Billing</h2>
        <p class="subtitle">Masukkan kode billing untuk melihat detail dan status pembayaran pajak. Halaman ini juga menerima alias pembayaran STPD manual `sanksi_saja`.</p>

        <form method="GET" action="{{ route('portal.billing') }}">
            <div class="billing-input-group">
                <input type="text" name="code" class="billing-input" placeholder="Masukkan Kode Billing"
                    value="{{ $code ?? '' }}" maxlength="20" required>
                <button type="submit" class="btn-check">
                    <i class="bi bi-search"></i> Cek
                </button>
            </div>
        </form>

        @if($code)
            @if($billing)
                @php
                    $displayStatus = $billing->display_status;
                    $hasNewerPembetulan = $billing->hasNewerPembetulan();
                    $showSptpdActions = ! $hasNewerPembetulan
                        && $billing->canExposeSptpdDocument();
                    $showStpdActions = ! $hasNewerPembetulan
                        && $billing->canExposeStpdDocument();
                    $showBillingActions = $hasNewerPembetulan || ! $showSptpdActions;
                    $showSptpdFallbackNotice = ! $hasNewerPembetulan
                        && $billing->hasPrincipalPaidInFull()
                        && blank($billing->sptpd_number);
                @endphp

                <div class="result-card">
                    <div class="result-header {{ $displayStatus->value }}">
                        <span>Detail Billing</span>
                        @switch($displayStatus->value)
                            @case('pending')
                                <span class="badge badge-pending">Belum Dibayar</span>
                                @break
                            @case('partially_paid')
                                <span class="badge badge-verified">Dibayar Sebagian</span>
                                @break
                            @case('paid')
                                <span class="badge badge-paid">Lunas</span>
                                @break
                            @case('verified')
                                <span class="badge badge-verified">Terverifikasi</span>
                                @break
                            @case('expired')
                                <span class="badge badge-expired">Kedaluwarsa</span>
                                @break
                            @default
                                <span class="badge badge-rejected">{{ ucfirst($displayStatus->value) }}</span>
                        @endswitch
                    </div>
                    <div class="result-body">
                        <div class="result-row">
                            <span class="label">Kode yang Dicek</span>
                            <span class="value"><code style="background:var(--bg-surface-variant); padding:2px 8px; border-radius:4px;">{{ $code }}</code></span>
                        </div>
                        <div class="result-row">
                            <span class="label">Billing Sumber</span>
                            <span class="value"><code style="background:var(--bg-surface-variant); padding:2px 8px; border-radius:4px;">{{ $billing->billing_code }}</code></span>
                        </div>
                        @if($billing->stpd_payment_code)
                            <div class="result-row">
                                <span class="label">Alias Pembayaran STPD Manual</span>
                                <span class="value"><code style="background:var(--bg-surface-variant); padding:2px 8px; border-radius:4px;">{{ $billing->stpd_payment_code }}</code></span>
                            </div>
                            <div class="result-row">
                                <span class="label">Keterangan</span>
                                <span class="value">Alias pembayaran untuk STPD manual tipe sanksi saja</span>
                            </div>
                        @endif
                        <div class="result-row">
                            <span class="label">Kode Pembayaran Aktif</span>
                            <span class="value"><code style="background:var(--bg-surface-variant); padding:2px 8px; border-radius:4px;">{{ $billing->getPreferredPaymentCode() }}</code></span>
                        </div>
                        <div class="result-row">
                            <span class="label">Jenis Pajak</span>
                            <span class="value">{{ $billing->jenisPajak->nama ?? '-' }}</span>
                        </div>
                        <div class="result-row">
                            <span class="label">Tanggal Dibuat</span>
                            <span class="value">{{ $billing->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($billing->paid_at)
                            <div class="result-row">
                                <span class="label">Tanggal Bayar</span>
                                <span class="value">{{ \Carbon\Carbon::parse($billing->paid_at)->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="result-total">
                        <span class="label">Total Tagihan</span>
                        <span class="value">Rp {{ number_format($billing->amount ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="result-actions">
                        @if($showBillingActions)
                            <a href="{{ route('portal.billing.document.show', $billing->id) }}" target="_blank"
                                title="{{ $billing->getBillingDocumentActionTitle() }}"
                                class="result-action billing-print">
                                <i class="bi bi-printer"></i> {{ $billing->getBillingDocumentActionLabel() }}
                            </a>
                            <a href="{{ route('portal.billing.document.download', $billing->id) }}"
                                title="{{ $billing->getBillingDownloadActionTitle() }}"
                                class="result-action billing-download">
                                <i class="bi bi-download"></i> {{ $billing->getBillingDownloadActionLabel() }}
                            </a>
                        @endif

                        @if($showSptpdActions)
                            <a href="{{ route('portal.sptpd.show', $billing->id) }}" target="_blank"
                                title="Lihat SPTPD"
                                class="result-action sptpd-print">
                                <i class="bi bi-printer"></i> Cetak SPTPD
                            </a>
                            <a href="{{ route('portal.sptpd.download', $billing->id) }}"
                                title="Unduh SPTPD"
                                class="result-action sptpd-download">
                                <i class="bi bi-download"></i> Unduh SPTPD
                            </a>
                        @endif

                        @if($showStpdActions)
                            <a href="{{ route('portal.stpd.show', $billing->id) }}" target="_blank"
                                title="Lihat STPD"
                                class="result-action stpd-print">
                                <i class="bi bi-printer"></i> Cetak STPD
                            </a>
                            <a href="{{ route('portal.stpd.download', $billing->id) }}"
                                title="Unduh STPD"
                                class="result-action stpd-download">
                                <i class="bi bi-download"></i> Unduh STPD
                            </a>
                        @endif
                    </div>

                    @if($showSptpdFallbackNotice)
                        <div class="result-note">
                            Billing ini sudah lunas, tetapi SPTPD belum terbit karena dokumen triwulan terkait belum lengkap. Dokumen billing tetap ditampilkan sebagai arsip pembayaran yang tersedia saat ini.
                        </div>
                    @endif
                </div>
            @else
                <div class="not-found">
                    <i class="bi bi-x-circle"></i>
                    <p><strong>Billing tidak ditemukan</strong></p>
                    <p>Kode billing <strong>"{{ $code }}"</strong> tidak terdaftar.</p>
                </div>
            @endif
        @endif
    </div>
@endsection
