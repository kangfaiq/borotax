@extends('layouts.portal-dashboard')

@section('title', 'Dashboard - Borotax Portal')
@section('page-title', 'Dashboard')

@section('styles')
<style>
    /* Welcome bar */
    .welcome-bar {
        background: linear-gradient(140deg, var(--secondary) 0%, var(--secondary-dark) 100%);
        border-radius: var(--radius-xl);
        padding: 28px 32px;
        color: var(--text-white);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .welcome-bar::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -3%;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(108,172,207,0.18), transparent 70%);
    }

    .welcome-bar h2 {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 4px;
        position: relative;
    }

    .welcome-bar p {
        color: rgba(255,255,255,0.55);
        font-size: 0.85rem;
        position: relative;
    }

    /* Stat cards */
    .stat-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        padding: 20px;
        transition: all var(--transition);
    }

    .stat-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .stat-card .s-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .stat-card .s-icon {
        width: 42px;
        height: 42px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }

    .stat-card .s-icon.teal { background: var(--primary-50); color: var(--primary-dark); }
    .stat-card .s-icon.green { background: var(--success-light); color: var(--success); }
    .stat-card .s-icon.orange { background: var(--warning-light); color: var(--warning); }
    .stat-card .s-icon.purple { background: #F3E8FF; color: #7C3AED; }

    .stat-card .s-value {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.2;
    }

    .stat-card .s-label {
        font-size: 0.78rem;
        color: var(--text-tertiary);
        margin-top: 2px;
    }

    /* Quick actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 24px;
    }

    .qa-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 20px 14px;
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        cursor: pointer;
        transition: all var(--transition);
        text-decoration: none;
        color: var(--text-secondary);
    }

    .qa-btn:hover {
        border-color: var(--primary);
        background: var(--primary-50);
        color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .qa-btn .qa-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        background: var(--bg-surface-variant);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: all var(--transition);
    }

    .qa-btn:hover .qa-icon {
        background: rgba(108,172,207,0.12);
    }

    .qa-btn .qa-label {
        font-size: 0.82rem;
        font-weight: 600;
        text-align: center;
    }

    /* Section box */
    .section-box {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .section-box-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px 0;
    }

    .section-box-header h3 {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-box-header a {
        color: var(--primary-dark);
        font-size: 0.82rem;
        font-weight: 600;
    }

    .section-box-header a:hover { text-decoration: underline; }

    /* Table */
    .table-responsive { overflow-x: auto; }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table thead th {
        text-align: left;
        padding: 12px 24px;
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid var(--border);
        background: var(--bg-surface);
    }

    .data-table tbody td {
        padding: 14px 24px;
        font-size: 0.85rem;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-secondary);
    }

    .data-table tbody tr:hover { background: var(--bg-surface); }
    .data-table tbody tr:last-child td { border-bottom: none; }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-tertiary);
    }

    .empty-state i { font-size: 2.2rem; margin-bottom: 10px; display: block; color: var(--border); }
    .empty-state p { font-size: 0.88rem; }

    /* Responsive */
    @media (max-width: 1280px) {
        .stat-cards { grid-template-columns: repeat(2, 1fr); }
        .quick-actions { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 640px) {
        .stat-cards { grid-template-columns: 1fr; }
        .quick-actions { grid-template-columns: 1fr 1fr; }
        .welcome-bar { padding: 22px 20px; }
        .data-table thead th, .data-table tbody td { padding: 10px 16px; }
    }
</style>
@endsection

@section('content')
    {{-- Welcome --}}
    <div class="welcome-bar">
        <h2>Selamat Datang, {{ $user->nama_lengkap }} 👋</h2>
        <p>Portal Wajib Pajak Kabupaten Bojonegoro</p>
    </div>

    {{-- Stat Cards --}}
    <div class="stat-cards">
        <div class="stat-card">
            <div class="s-top">
                <div class="s-icon orange"><i class="bi bi-hourglass-split"></i></div>
            </div>
            <div class="s-value">Rp {{ number_format($pendingAmount, 0, ',', '.') }}</div>
            <div class="s-label">Tagihan Belum Dibayar</div>
        </div>
        <div class="stat-card">
            <div class="s-top">
                <div class="s-icon green"><i class="bi bi-check-circle-fill"></i></div>
            </div>
            <div class="s-value">Rp {{ number_format($paidAmount, 0, ',', '.') }}</div>
            <div class="s-label">Total Sudah Dibayar</div>
        </div>
        <div class="stat-card">
            <div class="s-top">
                <div class="s-icon teal"><i class="bi bi-building"></i></div>
            </div>
            <div class="s-value">{{ $taxObjectsCount }}</div>
            <div class="s-label">Objek Pajak</div>
        </div>
        <div class="stat-card">
            <div class="s-top">
                <div class="s-icon purple"><i class="bi bi-ticket-perforated-fill"></i></div>
            </div>
            <div class="s-value">{{ $totalCoupons }}</div>
            <div class="s-label">Kupon Undian</div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="quick-actions">
        <a href="#" class="qa-btn">
            <span class="qa-icon"><i class="bi bi-receipt-cutoff"></i></span>
            <span class="qa-label">Self Assessment</span>
        </a>
        <a href="{{ route('portal.billing') }}" class="qa-btn">
            <span class="qa-icon"><i class="bi bi-search"></i></span>
            <span class="qa-label">Cek Billing</span>
        </a>
        <a href="{{ route('portal.history') }}" class="qa-btn">
            <span class="qa-icon"><i class="bi bi-clock-history"></i></span>
            <span class="qa-label">Riwayat Pajak</span>
        </a>
        <a href="{{ route('portal.gebyar.index') }}" class="qa-btn">
            <span class="qa-icon"><i class="bi bi-gift-fill"></i></span>
            <span class="qa-label">Gebyar Pajak</span>
        </a>
    </div>

    {{-- Recent Transactions --}}
    <div class="section-box">
        <div class="section-box-header">
            <h3><i class="bi bi-receipt" style="color: var(--primary);"></i> Transaksi Terbaru</h3>
            <a href="{{ route('portal.history') }}">Lihat Semua <i class="bi bi-arrow-right"></i></a>
        </div>

        @if($recentTransactions->count() > 0)
            <div class="table-responsive" style="margin-top: 12px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis Pajak</th>
                            <th>Kode Billing</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th style="width:80px; text-align:center;">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $tx)
                            <tr>
                                <td>{{ $tx->created_at->format('d/m/Y') }}</td>
                                <td>{{ $tx->jenisPajak->nama ?? '-' }}
                                    @if($tx->notes)
                                        <div style="font-size: 0.72rem; color: var(--text-tertiary); margin-top: 2px;">{{ str()->limit($tx->notes, 30) }}</div>
                                    @endif
                                </td>
                                <td><code style="background:var(--bg-surface-variant); padding:2px 8px; border-radius:4px; font-size:0.82rem;">{{ $tx->billing_code ?? '-' }}</code></td>
                                <td style="font-weight: 600;">Rp {{ number_format($tx->amount ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    @php
                                        $displayStatus = $tx->display_status;
                                        $statusBadgeClass = match ($displayStatus) {
                                            App\Enums\TaxStatus::Pending => 'badge-pending',
                                            App\Enums\TaxStatus::PartiallyPaid, App\Enums\TaxStatus::Verified => 'badge-verified',
                                            App\Enums\TaxStatus::Paid => 'badge-paid',
                                            App\Enums\TaxStatus::Expired => 'badge-expired',
                                            default => 'badge-rejected',
                                        };
                                        $statusLabel = match ($displayStatus) {
                                            App\Enums\TaxStatus::Pending => 'Menunggu',
                                            App\Enums\TaxStatus::PartiallyPaid => 'Dibayar Sebagian',
                                            App\Enums\TaxStatus::Paid => 'Lunas',
                                            App\Enums\TaxStatus::Verified => 'Terverifikasi',
                                            App\Enums\TaxStatus::Expired => 'Lewat Jatuh Tempo',
                                            default => ucfirst($displayStatus->value),
                                        };
                                    @endphp
                                    <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td style="text-align:center;">
                                    <div style="display:flex; align-items:center; justify-content:center; gap:6px;">
                                        <a href="{{ route('portal.billing.document.show', $tx->id) }}" target="_blank" title="{{ $tx->getBillingDocumentActionTitle() }}" style="color:var(--primary); font-size:1rem;"><i class="bi bi-printer"></i></a>
                                        <a href="{{ route('portal.billing.document.download', $tx->id) }}" title="{{ $tx->getBillingDownloadActionTitle() }}" style="color:#10b981; font-size:1rem;"><i class="bi bi-download"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Belum ada transaksi.</p>
            </div>
        @endif
    </div>
@endsection