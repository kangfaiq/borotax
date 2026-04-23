@extends('layouts.portal-guest')

@section('title', 'Cek Billing - Borotax')

@section('navbar-class', 'navbar-light')

@section('nav-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}" style="color: var(--primary-dark); font-weight: 600;">Cek Billing</a>
    <a href="{{ url('/login') }}" class="btn btn-primary btn-sm navbar-cta">Login</a>
@endsection

@section('nav-mobile-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/login') }}">Login Wajib Pajak</a>
@endsection

@section('styles')
    <style>
        .billing-page {
            padding: 100px 0 60px;
            min-height: 100vh;
        }

        .billing-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .billing-header h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .billing-header p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            max-width: 640px;
            margin: 0 auto;
        }

        .publik-nav {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        .publik-nav a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: var(--radius-full);
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid var(--border);
            color: var(--text-secondary);
            background: var(--bg-card);
            transition: all var(--transition);
            text-decoration: none;
        }

        .publik-nav a:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .publik-nav a.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .billing-box {
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            padding: 44px 36px;
            max-width: 580px;
            margin: 0 auto;
            box-shadow: var(--shadow-md);
        }

        .billing-box-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .billing-box .subtitle {
            color: var(--text-secondary);
            font-size: 0.88rem;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .billing-input-group {
            display: flex;
            gap: 10px;
        }

        .billing-input {
            flex: 1;
            padding: 13px 18px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 0.92rem;
            font-family: inherit;
            transition: all var(--transition);
            outline: none;
            background: var(--bg-surface-variant);
        }

        .billing-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 172, 207, 0.15);
            background: var(--bg-card);
        }

        .result-card {
            margin-top: 28px;
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .result-header {
            padding: 16px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .result-header.pending {
            background: var(--warning-light);
            color: #E65100;
            border-bottom: 1px solid #FFE0B2;
        }

        .result-header.lunas {
            background: var(--success-light);
            color: #2E7D32;
            border-bottom: 1px solid #C8E6C9;
        }

        .result-body {
            padding: 20px 22px;
        }

        .result-row {
            display: flex;
            justify-content: space-between;
            padding: 9px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .result-row:last-child {
            border-bottom: none;
        }

        .result-row .label {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        .result-row .value {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.85rem;
        }

        .result-total {
            display: flex;
            justify-content: space-between;
            padding: 14px 22px;
            background: var(--primary-50);
            border-top: 1.5px solid var(--border);
        }

        .result-total .label {
            font-weight: 700;
            color: var(--text-primary);
        }

        .result-total .value {
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--primary-dark);
        }

        .not-found {
            text-align: center;
            padding: 36px;
            color: var(--text-secondary);
        }

        .not-found .icon {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 12px;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 700;
        }

        .badge-pending {
            background: #FFF3E0;
            color: #E65100;
        }

        .badge-lunas {
            background: #E8F5E9;
            color: #2E7D32;
        }

        @media (max-width: 640px) {
            .billing-box {
                padding: 28px 20px;
            }

            .billing-header h1 {
                font-size: 1.7rem;
            }

            .billing-input-group {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')
    <div class="billing-page">
        <div class="container">
            <div class="billing-header">
                <span class="section-badge"><i class="bi bi-bank"></i> LAYANAN PUBLIK</span>
                <h1>Cek Billing</h1>
                <p>Periksa status tagihan dan pembayaran pajak daerah tanpa login melalui halaman layanan publik.</p>
            </div>

            @include('portal.publik._nav', ['active' => 'cek-billing'])

            <div class="billing-box">
                <div class="billing-box-title">Masukkan kode billing</div>
                <p class="subtitle">Masukkan kode billing untuk melihat detail dan status pembayaran pajak. Halaman ini juga mendukung alias pembayaran STPD manual `sanksi_saja`.</p>

                <form method="GET" action="{{ url('/cek-billing') }}">
                    <div class="billing-input-group">
                        <input type="text" name="code" class="billing-input" placeholder="Masukkan Kode Billing"
                            value="{{ $code ?? '' }}" maxlength="20" inputmode="numeric" required>
                        <button type="submit" class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>
                            Cek
                        </button>
                    </div>
                </form>

                @if($code)
                    @if($billing)
                        @php($displayStatus = $billing->display_status)
                        <div class="result-card">
                            <div class="result-header {{ $displayStatus->value }}">
                                <span>Detail Billing</span>
                                @if($displayStatus === App\Enums\TaxStatus::Pending || $displayStatus === App\Enums\TaxStatus::Verified)
                                    <span class="badge badge-pending">Belum Dibayar</span>
                                @elseif($displayStatus === App\Enums\TaxStatus::PartiallyPaid)
                                    <span class="badge badge-verified">Dibayar Sebagian</span>
                                @elseif($displayStatus === App\Enums\TaxStatus::Paid)
                                    <span class="badge badge-lunas">Lunas</span>
                                @elseif($displayStatus === App\Enums\TaxStatus::Expired)
                                    <span class="badge badge-expired">Kedaluwarsa</span>
                                @endif
                            </div>
                            <div class="result-body">
                                <div class="result-row">
                                    <span class="label">Kode yang Dicek</span>
                                    <span class="value" style="font-family: monospace;">{{ $code }}</span>
                                </div>
                                <div class="result-row">
                                    <span class="label">Billing Sumber</span>
                                    <span class="value" style="font-family: monospace;">{{ $billing->billing_code }}</span>
                                </div>
                                @if($billing->stpd_payment_code)
                                    <div class="result-row">
                                        <span class="label">Alias Pembayaran STPD Manual</span>
                                        <span class="value" style="font-family: monospace;">{{ $billing->stpd_payment_code }}</span>
                                    </div>
                                    <div class="result-row">
                                        <span class="label">Keterangan</span>
                                        <span class="value">Alias pembayaran untuk STPD manual tipe sanksi saja</span>
                                    </div>
                                @endif
                                <div class="result-row">
                                    <span class="label">Kode Pembayaran Aktif</span>
                                    <span class="value" style="font-family: monospace;">{{ $billing->getPreferredPaymentCode() }}</span>
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
                        </div>
                    @else
                        <div class="not-found">
                            <div class="icon">❌</div>
                            <p><strong>Billing tidak ditemukan</strong></p>
                            <p>Kode billing <strong>"{{ $code }}"</strong> tidak terdaftar.</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection