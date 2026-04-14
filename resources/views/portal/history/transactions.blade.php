@extends('layouts.portal-dashboard')

@section('title', 'Riwayat Transaksi - Borotax Portal')
@section('page-title', 'Riwayat Transaksi')

@section('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 14px;
    }

    .page-header h1 {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-pills {
        display: flex;
        gap: 8px;
    }

    .filter-pill {
        padding: 7px 18px;
        border-radius: var(--radius-full);
        font-size: 0.82rem;
        font-weight: 600;
        border: 1.5px solid var(--border);
        background: var(--bg-card);
        color: var(--text-secondary);
        cursor: pointer;
        transition: all var(--transition);
        text-decoration: none;
    }

    .filter-pill:hover,
    .filter-pill.active {
        border-color: var(--primary);
        background: var(--primary-50);
        color: var(--primary-dark);
    }

    .table-card {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        overflow: hidden;
    }

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
        background: var(--bg-surface);
        border-bottom: 1px solid var(--border);
    }

    .data-table tbody td {
        padding: 14px 24px;
        font-size: 0.85rem;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-secondary);
    }

    .data-table tbody tr:hover { background: var(--bg-surface); }
    .data-table tbody tr:last-child td { border-bottom: none; }

    .pagination-wrapper {
        padding: 18px;
        display: flex;
        justify-content: center;
    }

    .pagination-wrapper nav span,
    .pagination-wrapper nav a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: var(--radius-sm);
        font-size: 0.82rem;
        font-weight: 600;
        margin: 0 2px;
        border: 1px solid var(--border);
        color: var(--text-secondary);
        text-decoration: none;
        transition: all var(--transition);
    }

    .pagination-wrapper nav span.current,
    .pagination-wrapper nav a:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: var(--text-tertiary);
    }

    .empty-state i { font-size: 2.5rem; margin-bottom: 10px; display: block; color: var(--border); }
    .empty-state p { font-size: 0.88rem; }

    .search-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
        background: var(--bg-surface);
        flex-wrap: wrap;
    }
    .search-bar form {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
        min-width: 200px;
    }
    .search-input {
        flex: 1;
        padding: 8px 14px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 0.85rem;
        background: var(--bg-card);
        color: var(--text-primary);
        outline: none;
        transition: border-color var(--transition);
        min-width: 180px;
    }
    .search-input:focus {
        border-color: var(--primary);
    }
    .search-input::placeholder {
        color: var(--text-tertiary);
    }
    .search-btn {
        padding: 8px 16px;
        border-radius: var(--radius-sm);
        font-size: 0.82rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all var(--transition);
    }
    .search-btn-primary {
        background: var(--primary);
        color: white;
    }
    .search-btn-primary:hover {
        background: var(--primary-dark);
    }
    .search-btn-reset {
        background: var(--bg-card);
        color: var(--text-secondary);
        border: 1.5px solid var(--border);
    }
    .search-btn-reset:hover {
        border-color: var(--primary);
        color: var(--primary-dark);
    }
    .per-page-select {
        padding: 8px 10px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 0.82rem;
        background: var(--bg-card);
        color: var(--text-secondary);
        outline: none;
        cursor: pointer;
    }
    .per-page-select:focus {
        border-color: var(--primary);
    }
    .pagination-info {
        padding: 12px 24px;
        font-size: 0.78rem;
        color: var(--text-tertiary);
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .pagination-nav {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .pagination-nav a,
    .pagination-nav span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 8px;
        border-radius: var(--radius-sm);
        font-size: 0.82rem;
        font-weight: 600;
        border: 1px solid var(--border);
        color: var(--text-secondary);
        text-decoration: none;
        transition: all var(--transition);
    }
    .pagination-nav a:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }
    .pagination-nav .active-page {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }
    .pagination-nav .disabled-page {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
    }

    .badge-pembetulan {
        background: #dbeafe;
        color: #1d4ed8;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .badge-dipembetulan {
        background: #fef3c7;
        color: #b45309;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .badge-original {
        background: var(--bg-surface);
        color: var(--text-tertiary);
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .pembetulan-ref {
        font-size: 0.7rem;
        color: var(--text-tertiary);
        margin-top: 3px;
    }
    .pembetulan-ref code {
        background: var(--bg-surface-variant);
        padding: 1px 5px;
        border-radius: 3px;
        font-size: 0.68rem;
    }

    @media (max-width: 768px) {
        .page-header { flex-direction: column; align-items: flex-start; }
        .filter-pills { flex-wrap: wrap; }
        .data-table thead th, .data-table tbody td { padding: 10px 16px; }
    }
</style>
@endsection

@section('content')
    <div class="page-header">
        <h1><i class="bi bi-clock-history" style="color: var(--primary);"></i> Riwayat Transaksi</h1>
        <div class="filter-pills">
            <a href="{{ route('portal.history') }}"
                class="filter-pill {{ !request('status') ? 'active' : '' }}">Semua</a>
            <a href="{{ route('portal.history', ['status' => 'pending']) }}"
                class="filter-pill {{ request('status') === 'pending' ? 'active' : '' }}">Menunggu</a>
            <a href="{{ route('portal.history', ['status' => 'lunas']) }}"
                class="filter-pill {{ request('status') === 'lunas' ? 'active' : '' }}">Lunas</a>
        </div>
    </div>

    <div class="table-card">
        {{-- Search Bar --}}
        <div class="search-bar">
            <form method="GET" action="{{ route('portal.history') }}">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <input type="text" name="search" class="search-input" placeholder="Cari kode billing atau jenis pajak..." value="{{ request('search') }}">
                <button type="submit" class="search-btn search-btn-primary"><i class="bi bi-search"></i> Cari</button>
                @if(request('search'))
                    <a href="{{ route('portal.history', request()->only('status')) }}" class="search-btn search-btn-reset"><i class="bi bi-x-lg"></i> Reset</a>
                @endif
            </form>
            <div style="display:flex; align-items:center; gap:6px;">
                <label for="per_page" style="font-size:0.78rem; color:var(--text-tertiary); white-space:nowrap;">Tampilkan:</label>
                <select id="per_page" class="per-page-select" onchange="window.location.href=this.value">
                    @foreach([10, 15, 25, 50] as $pp)
                        <option value="{{ route('portal.history', array_merge(request()->except('per_page', 'page'), ['per_page' => $pp])) }}" @selected(request('per_page', 15) == $pp)>{{ $pp }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis Pajak</th>
                            <th>Kode Billing</th>
                            <th>Pembetulan</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th style="width:100px; text-align:center;">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $tx)
                            <tr>
                                <td>{{ $tx->created_at->format('d/m/Y') }}</td>
                                <td>{{ $tx->jenisPajak->nama ?? '-' }}
                                    @if($tx->notes)
                                        <div style="font-size: 0.72rem; color: var(--text-tertiary); margin-top: 2px;">{{ str()->limit($tx->notes, 40) }}</div>
                                    @endif
                                </td>
                                <td><code style="background:var(--bg-surface-variant); padding:2px 8px; border-radius:4px; font-size:0.82rem;">{{ $tx->billing_code ?? '-' }}</code></td>
                                <td>
                                    @if($tx->pembetulan_ke > 0)
                                        <span class="badge-pembetulan">Pembetulan Ke-{{ $tx->pembetulan_ke }}</span>
                                        @if($tx->parent)
                                            <div class="pembetulan-ref">Atas Billing: <code>{{ $tx->parent->billing_code }}</code></div>
                                        @endif
                                    @elseif($tx->children->count() > 0)
                                        <span class="badge-dipembetulan">Sudah Dipembetulan</span>
                                        <div class="pembetulan-ref">Pembetulan: <code>{{ $tx->children->sortByDesc('pembetulan_ke')->first()->billing_code }}</code></div>
                                    @else
                                        <span class="badge-original">Original</span>
                                    @endif
                                </td>
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
                                            App\Enums\TaxStatus::Expired => 'Kedaluwarsa',
                                            default => ucfirst($displayStatus->value),
                                        };
                                    @endphp
                                    <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td style="text-align:center;">
                                    <div style="display:flex; align-items:center; justify-content:center; gap:12px;">
                                        @if($tx->status === App\Enums\TaxStatus::Paid)
                                            {{-- SPTPD (hanya tampil jika triwulan lengkap / sptpd_number terisi) --}}
                                            @if($tx->sptpd_number)
                                            <div style="display:flex; flex-direction:column; align-items:center;">
                                                <span style="font-size:0.65rem; font-weight:700; color:var(--primary); margin-bottom:2px;">SPTPD</span>
                                                <div style="display:flex; gap:6px;">
                                                    <a href="{{ route('portal.sptpd.show', $tx->id) }}" target="_blank" title="Print SPTPD" style="color:var(--primary); font-size:1.1rem;"><i class="bi bi-printer"></i></a>
                                                    <a href="{{ route('portal.sptpd.download', $tx->id) }}" title="Download SPTPD" style="color:#10b981; font-size:1.1rem;"><i class="bi bi-download"></i></a>
                                                </div>
                                            </div>
                                            @endif
                                            
                                            {{-- STPD (Only if exists and not OPD) --}}
                                            @if($tx->stpd_number && !($tx->taxObject && $tx->taxObject->is_opd))
                                                 <div style="display:flex; flex-direction:column; align-items:center;">
                                                    <span style="font-size:0.65rem; font-weight:700; color:#f59e0b; margin-bottom:2px;">STPD</span>
                                                    <div style="display:flex; gap:6px;">
                                                        <a href="{{ route('portal.stpd.show', $tx->id) }}" target="_blank" title="Print STPD (Sanksi)" style="color:#f59e0b; font-size:1.1rem;"><i class="bi bi-printer"></i></a>
                                                        <a href="{{ route('portal.stpd.download', $tx->id) }}" title="Download STPD (Sanksi)" style="color:#f59e0b; font-size:1.1rem;"><i class="bi bi-download"></i></a>
                                                    </div>
                                                 </div>
                                            @endif
                                        @else
                                            {{-- Billing Code --}}
                                            <div style="display:flex; flex-direction:column; align-items:center;">
                                                <span style="font-size:0.65rem; font-weight:700; color:var(--text-secondary); margin-bottom:2px;">Billing</span>
                                                <div style="display:flex; gap:6px;">
                                                    <a href="{{ route('portal.billing.document.show', $tx->id) }}" target="_blank" title="{{ $tx->getBillingDocumentActionTitle() }}" style="color:var(--text-secondary); font-size:1.1rem;"><i class="bi bi-printer"></i></a>
                                                    <a href="{{ route('portal.billing.document.download', $tx->id) }}" title="{{ $tx->getBillingDownloadActionTitle() }}" style="color:var(--text-secondary); font-size:1.1rem;"><i class="bi bi-download"></i></a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="pagination-info">
                <span>Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} dari {{ $transactions->total() }} transaksi</span>
                @if($transactions->hasPages())
                    <div class="pagination-nav">
                        {{-- Previous --}}
                        @if($transactions->onFirstPage())
                            <span class="disabled-page"><i class="bi bi-chevron-left"></i></span>
                        @else
                            <a href="{{ $transactions->appends(request()->query())->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                        @endif

                        {{-- Page Numbers --}}
                        @php
                            $current = $transactions->currentPage();
                            $last = $transactions->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $transactions->appends(request()->query())->url(1) }}">1</a>
                            @if($start > 2)
                                <span style="border:none; color:var(--text-tertiary);">…</span>
                            @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            @if($i == $current)
                                <span class="active-page">{{ $i }}</span>
                            @else
                                <a href="{{ $transactions->appends(request()->query())->url($i) }}">{{ $i }}</a>
                            @endif
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1)
                                <span style="border:none; color:var(--text-tertiary);">…</span>
                            @endif
                            <a href="{{ $transactions->appends(request()->query())->url($last) }}">{{ $last }}</a>
                        @endif

                        {{-- Next --}}
                        @if($transactions->hasMorePages())
                            <a href="{{ $transactions->appends(request()->query())->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                        @else
                            <span class="disabled-page"><i class="bi bi-chevron-right"></i></span>
                        @endif
                    </div>
                @endif
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                @if(request('search'))
                    <p>Tidak ada transaksi yang cocok dengan pencarian "<strong>{{ request('search') }}</strong>".</p>
                    <a href="{{ route('portal.history', request()->only('status')) }}" style="color:var(--primary); font-size:0.85rem; margin-top:8px; display:inline-block;">Reset Pencarian</a>
                @else
                    <p>Belum ada riwayat transaksi.</p>
                @endif
            </div>
        @endif
    </div>
@endsection