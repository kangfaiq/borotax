@extends('layouts.portal-dashboard')

@section('title', 'Ajukan Pembetulan - Borotax Portal')
@section('page-title', 'Ajukan Pembetulan')

@section('styles')
<style>
    .pemb-index-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 24px;
    }

    .pemb-index-copy h2 {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .pemb-index-copy p {
        max-width: 720px;
        font-size: 0.84rem;
        color: var(--text-secondary);
    }

    .pemb-search-form {
        width: min(360px, 100%);
    }

    .pemb-search-wrap {
        position: relative;
    }

    .pemb-search-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-tertiary);
        font-size: 0.92rem;
    }

    .pemb-search-wrap input {
        width: 100%;
        padding: 12px 14px 12px 38px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-md);
        font: inherit;
        background: var(--bg-card);
        color: var(--text-primary);
    }

    .pemb-search-wrap input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.12);
    }

    .pemb-grid {
        display: grid;
        gap: 16px;
    }

    .pemb-item {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 22px;
        box-shadow: var(--shadow-xs);
    }

    .pemb-item-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .pemb-billing-code {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: var(--radius-full);
        background: var(--bg-surface);
        color: var(--text-primary);
        font-size: 0.78rem;
        font-weight: 800;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        letter-spacing: 0.02em;
    }

    .pemb-item-title {
        margin-top: 10px;
        font-size: 1rem;
        font-weight: 800;
        color: var(--text-primary);
    }

    .pemb-item-subtitle {
        margin-top: 3px;
        font-size: 0.82rem;
        color: var(--text-secondary);
    }

    .pemb-status,
    .pemb-pending-flag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: var(--radius-full);
        font-size: 0.74rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .pemb-status.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .pemb-status.paid {
        background: #dcfce7;
        color: #166534;
    }

    .pemb-status.verified {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .pemb-pending-flag {
        margin-top: 10px;
        background: #fff7ed;
        color: #c2410c;
    }

    .pemb-meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .pemb-meta-item {
        padding: 14px;
        border-radius: var(--radius-md);
        background: var(--bg-surface);
    }

    .pemb-meta-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }

    .pemb-meta-value {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-primary);
        word-break: break-word;
    }

    .pemb-item-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid var(--border-light);
    }

    .pemb-item-actions p {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .pemb-action-link,
    .pemb-action-disabled {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 16px;
        border-radius: var(--radius-md);
        font-size: 0.84rem;
        font-weight: 700;
    }

    .pemb-action-link {
        background: var(--primary);
        color: #fff;
        transition: all var(--transition);
    }

    .pemb-action-link:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(var(--primary-rgb), 0.2);
    }

    .pemb-action-disabled {
        background: #fff7ed;
        color: #9a3412;
    }

    .pemb-empty {
        background: var(--bg-card);
        border: 1px dashed var(--border);
        border-radius: var(--radius-lg);
        padding: 42px 24px;
        text-align: center;
    }

    .pemb-empty i {
        font-size: 2rem;
        color: var(--text-tertiary);
        display: block;
        margin-bottom: 12px;
    }

    .pemb-empty h3 {
        font-size: 1rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 6px;
    }

    .pemb-empty p {
        font-size: 0.84rem;
        color: var(--text-secondary);
        max-width: 520px;
        margin: 0 auto;
    }

    .pagination-info {
        margin-top: 16px;
        padding: 16px 4px 0;
        font-size: 0.78rem;
        color: var(--text-tertiary);
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
        background: var(--bg-card);
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

    @media (max-width: 1024px) {
        .pemb-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .pemb-index-head,
        .pemb-item-head,
        .pemb-item-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .pemb-search-form {
            width: 100%;
        }

        .pemb-meta {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
    <div class="pemb-index-head">
        <div class="pemb-index-copy">
            <h2>Pilih Billing untuk Pembetulan</h2>
            <p>Gunakan menu ini untuk mengajukan pembetulan kapan saja dari portal. Daftar di bawah hanya menampilkan billing aktif terbaru milik Anda yang masih dapat diajukan koreksi.</p>
        </div>

        <form method="GET" action="{{ route('portal.pembetulan.index') }}" class="pemb-search-form">
            <div class="pemb-search-wrap">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari kode billing, jenis pajak, atau objek pajak">
            </div>
        </form>
    </div>

    @php
        $pendingRequestTaxLookup = array_flip($pendingRequestTaxIds);
    @endphp

    @if($recentRequests->isNotEmpty())
        <div class="pemb-index-copy" style="margin-bottom:14px;">
            <h2>Riwayat Permohonan Pembetulan</h2>
            <p>Permohonan terakhir Anda tetap bisa dipantau dari sini meskipun billing asalnya sudah tidak tampil di daftar pengajuan baru.</p>
        </div>

        <div class="pemb-grid" style="margin-bottom:24px;">
            @foreach($recentRequests as $requestRecord)
                @php
                    $requestStatusClass = match ($requestRecord->status) {
                        'selesai' => 'verified',
                        'ditolak' => 'rejected',
                        default => 'pending',
                    };

                    $requestStatusLabel = match ($requestRecord->status) {
                        'pending' => 'Menunggu Review',
                        'diproses' => 'Sedang Diproses',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak',
                        default => str($requestRecord->status)->headline()->toString(),
                    };
                @endphp

                <div class="pemb-item">
                    <div class="pemb-item-head">
                        <div>
                            <span class="pemb-billing-code">
                                <i class="bi bi-hourglass-split"></i>
                                {{ $requestRecord->tax?->billing_code ?? 'Pembetulan' }}
                            </span>
                            <div class="pemb-item-title">{{ $requestRecord->tax?->jenisPajak?->nama ?? 'Permohonan Pembetulan' }}</div>
                            <div class="pemb-item-subtitle">{{ $requestRecord->tax?->taxObject?->nama_objek_pajak ?? '-' }}</div>
                        </div>

                        <span class="pemb-status {{ $requestStatusClass }}">{{ $requestStatusLabel }}</span>
                    </div>

                    <div class="pemb-meta">
                        <div class="pemb-meta-item">
                            <div class="pemb-meta-label">Diajukan</div>
                            <div class="pemb-meta-value">{{ $requestRecord->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
                        </div>
                        <div class="pemb-meta-item">
                            <div class="pemb-meta-label">Omzet Baru</div>
                            <div class="pemb-meta-value">{{ $requestRecord->omzet_baru ? 'Rp ' . number_format((float) $requestRecord->omzet_baru, 0, ',', '.') : '-' }}</div>
                        </div>
                        <div class="pemb-meta-item">
                            <div class="pemb-meta-label">Diproses Oleh</div>
                            <div class="pemb-meta-value">{{ $requestRecord->processor?->nama_lengkap ?? $requestRecord->processor?->name ?? 'Belum diproses' }}</div>
                        </div>
                        <div class="pemb-meta-item">
                            <div class="pemb-meta-label">Update Terakhir</div>
                            <div class="pemb-meta-value">{{ $requestRecord->updated_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="pemb-item-actions">
                        <p>{{ \Illuminate\Support\Str::limit($requestRecord->alasan, 120) }}</p>
                        <a href="{{ route('portal.pembetulan.show', $requestRecord->id) }}" class="pemb-action-link">
                            <i class="bi bi-arrow-right-circle"></i>
                            Lihat Detail Permohonan
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @forelse($taxes as $tax)
        @php
            $displayStatus = $tax->display_status;

            $statusClass = match ($displayStatus) {
                App\Enums\TaxStatus::Paid => 'paid',
                App\Enums\TaxStatus::Verified => 'verified',
                App\Enums\TaxStatus::Expired => 'rejected',
                default => 'pending',
            };

            $statusLabel = match ($displayStatus) {
                App\Enums\TaxStatus::Paid => 'Sudah Dibayar',
                App\Enums\TaxStatus::Verified => 'Terverifikasi',
                App\Enums\TaxStatus::Expired => 'Lewat Jatuh Tempo',
                default => 'Belum Dibayar',
            };

            $masaPajakLabel = $tax->masa_pajak_bulan
                ? \Carbon\Carbon::create($tax->masa_pajak_tahun, $tax->masa_pajak_bulan, 1)->translatedFormat('F Y')
                : 'Tahun ' . $tax->masa_pajak_tahun;

            $hasPendingRequest = isset($pendingRequestTaxLookup[$tax->id]);
        @endphp

        <div class="pemb-item">
            <div class="pemb-item-head">
                <div>
                    <span class="pemb-billing-code">
                        <i class="bi bi-upc-scan"></i>
                        {{ $tax->billing_code }}
                    </span>
                    <div class="pemb-item-title">{{ $tax->jenisPajak->nama ?? 'Billing Pajak' }}</div>
                    <div class="pemb-item-subtitle">{{ $tax->taxObject->nama_objek_pajak ?? '-' }}</div>

                    @if($hasPendingRequest)
                        <div class="pemb-pending-flag">
                            <i class="bi bi-hourglass-split"></i>
                            Permohonan pembetulan untuk billing ini sedang menunggu review
                        </div>
                    @endif
                </div>

                <span class="pemb-status {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>

            <div class="pemb-meta">
                <div class="pemb-meta-item">
                    <div class="pemb-meta-label">Masa Pajak</div>
                    <div class="pemb-meta-value">{{ $masaPajakLabel }}</div>
                </div>
                <div class="pemb-meta-item">
                    <div class="pemb-meta-label">Omzet</div>
                    <div class="pemb-meta-value">Rp {{ number_format($tax->omzet, 0, ',', '.') }}</div>
                </div>
                <div class="pemb-meta-item">
                    <div class="pemb-meta-label">Jumlah Pajak</div>
                    <div class="pemb-meta-value">Rp {{ number_format($tax->amount, 0, ',', '.') }}</div>
                </div>
                <div class="pemb-meta-item">
                    <div class="pemb-meta-label">Dibuat</div>
                    <div class="pemb-meta-value">{{ $tax->created_at->translatedFormat('d M Y H:i') }}</div>
                </div>
            </div>

            <div class="pemb-item-actions">
                <p>Ajukan koreksi jika ada kesalahan omzet, data objek, atau informasi billing lain yang perlu diperbarui.</p>

                @if($hasPendingRequest)
                    <span class="pemb-action-disabled">
                        <i class="bi bi-clock-history"></i>
                        Menunggu Review
                    </span>
                @else
                    <a href="{{ route('portal.pembetulan.create', $tax->id) }}" class="pemb-action-link">
                        <i class="bi bi-pencil-square"></i>
                        Ajukan Pembetulan
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="pemb-empty">
            <i class="bi bi-inbox"></i>
            <h3>Tidak ada billing yang siap diajukan pembetulan</h3>
            @if($search !== '')
                <p>Tidak ada billing yang cocok dengan pencarian Anda. Coba kata kunci lain atau reset pencarian untuk melihat seluruh billing yang bisa diajukan pembetulan.</p>
                <a href="{{ route('portal.pembetulan.index') }}" style="color:var(--primary); font-size:0.85rem; margin-top:10px; display:inline-block; font-weight:700;">Reset Pencarian</a>
            @else
                <p>Belum ada billing aktif terbaru yang memenuhi syarat untuk pembetulan.</p>
            @endif
        </div>
    @endforelse

    @if($taxes->count() > 0)
        <div class="pagination-info">
            <span>Menampilkan {{ $taxes->firstItem() }}–{{ $taxes->lastItem() }} dari {{ $taxes->total() }} billing</span>

            @if($taxes->hasPages())
                <div class="pagination-nav">
                    @if($taxes->onFirstPage())
                        <span class="disabled-page"><i class="bi bi-chevron-left"></i></span>
                    @else
                        <a href="{{ $taxes->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                    @endif

                    @php
                        $current = $taxes->currentPage();
                        $last = $taxes->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $taxes->url(1) }}">1</a>
                        @if($start > 2)
                            <span style="border:none; color:var(--text-tertiary); background:transparent;">…</span>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        @if($i === $current)
                            <span class="active-page">{{ $i }}</span>
                        @else
                            <a href="{{ $taxes->url($i) }}">{{ $i }}</a>
                        @endif
                    @endfor

                    @if($end < $last)
                        @if($end < $last - 1)
                            <span style="border:none; color:var(--text-tertiary); background:transparent;">…</span>
                        @endif
                        <a href="{{ $taxes->url($last) }}">{{ $last }}</a>
                    @endif

                    @if($taxes->hasMorePages())
                        <a href="{{ $taxes->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                    @else
                        <span class="disabled-page"><i class="bi bi-chevron-right"></i></span>
                    @endif
                </div>
            @endif
        </div>
    @endif
@endsection