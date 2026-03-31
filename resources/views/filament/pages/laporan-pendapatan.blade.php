<x-filament-panels::page>

<style>
    :root {
        --lp-primary: #3b82f6;
    }
    .lp-page .lp-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 24px;
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    .lp-page .lp-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(59,130,246,0.12);
        border-color: #93c5fd;
    }
    .lp-page .lp-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: transparent;
        transition: background 0.2s;
    }
    .lp-page .lp-card:hover::before {
        background: var(--lp-primary);
    }

    .dark .lp-page .lp-card {
        background: #1e293b;
        border-color: #334155;
    }
    .dark .lp-page .lp-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 12px 28px rgba(59,130,246,0.2);
    }

    .lp-icon {
        width: 56px; height: 56px;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 28px;
        margin-bottom: 16px;
    }

    .lp-stat { font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; }
    .dark .lp-stat { color: #94a3b8; }

    .lp-val { font-size: 18px; font-weight: 800; color: #0f172a; }
    .dark .lp-val { color: #f8fafc; }

    .lp-arrow {
        position: absolute; bottom: 20px; right: 20px;
        width: 32px; height: 32px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: #f1f5f9; color: #94a3b8; transition: all 0.2s;
    }
    .lp-card:hover .lp-arrow { background: var(--lp-primary); color: white; }
    .dark .lp-arrow { background: #334155; color: #64748b; }
    .dark .lp-card:hover .lp-arrow { background: var(--lp-primary); color: white; }

    .lp-badge {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px;
    }
    .lp-badge-blue { background: #dbeafe; color: #1d4ed8; }
    .dark .lp-badge-blue { background: rgba(59,130,246,0.15); color: #93c5fd; }
    .lp-badge-amber { background: #fef3c7; color: #92400e; }
    .dark .lp-badge-amber { background: rgba(245,158,11,0.15); color: #fcd34d; }
    .lp-badge-green { background: #dcfce7; color: #166534; }
    .dark .lp-badge-green { background: rgba(34,197,94,0.15); color: #86efac; }

    .lp-summary {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
        border-radius: 16px; padding: 24px 28px; color: white;
        position: relative; overflow: hidden; margin-bottom: 28px;
    }
    .lp-summary::before {
        content: ''; position: absolute; top: -30%; right: -8%;
        width: 250px; height: 250px; background: rgba(255,255,255,0.07);
        border-radius: 50%;
    }
    .lp-summary::after {
        content: ''; position: absolute; bottom: -40%; right: 20%;
        width: 180px; height: 180px; background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }
    .lp-summary * { position: relative; z-index: 1; }

    .lp-year-num {
        font-size: 2.5rem; font-weight: 900; line-height: 1;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .dark .lp-year-num {
        background: linear-gradient(135deg, #60a5fa, #93c5fd);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .lp-year-current {
        border-color: #93c5fd !important;
        box-shadow: 0 0 0 1px rgba(59,130,246,0.15);
    }
    .dark .lp-year-current {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 1px rgba(59,130,246,0.2);
    }
</style>

<div class="lp-page">

@if(!$tahun)
    {{-- ==================== YEAR SELECTION ==================== --}}

    <div class="lp-summary">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold mb-1">Laporan Pendapatan</h2>
                <p class="text-sm opacity-80">Pilih tahun laporan untuk melihat detail pendapatan per jenis pajak.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-8">
        @foreach($years as $y)
            @php
                $ys = $yearStats[$y] ?? ['total_transaksi' => 0, 'total_pendapatan' => 0, 'pending' => 0];
                $isCurrent = $y === (int) date('Y');
                $url = App\Filament\Pages\LaporanPendapatan::getUrl(['tahun' => $y]);
            @endphp
            <a href="{{ $url }}" class="lp-card block {{ $isCurrent ? 'lp-year-current' : '' }}" style="text-decoration:none;">
                <div class="flex items-center justify-between mb-4">
                    <span class="lp-year-num">{{ $y }}</span>
                    @if($isCurrent)
                        <span class="lp-badge lp-badge-green text-[10px]">Berjalan</span>
                    @endif
                </div>

                <div class="space-y-2.5">
                    <div>
                        <div class="lp-stat mb-0.5">Pendapatan</div>
                        <div class="text-base font-extrabold text-slate-900 dark:text-white">
                            Rp {{ number_format($ys['total_pendapatan'], 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-700">
                        <span class="lp-stat">Transaksi</span>
                        <div class="flex items-center gap-2">
                            <span class="lp-badge lp-badge-blue">{{ $ys['total_transaksi'] }}</span>
                            @if($ys['pending'] > 0)
                                <span class="lp-badge lp-badge-amber">{{ $ys['pending'] }} pending</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

@else
    {{-- ==================== JENIS PAJAK SELECTION ==================== --}}

    @php
        $totalPendapatanAll = collect($stats)->sum('total_pendapatan');
        $pendapatanBulanIniAll = collect($stats)->sum('pendapatan_bulan_ini');
        $totalTransaksiAll = collect($stats)->sum('total_transaksi');
        $totalPendingAll = collect($stats)->sum('pending');
    @endphp

    <div class="lp-summary">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold mb-1">Laporan Pendapatan — {{ $tahun }}</h2>
                <p class="text-sm opacity-80">Pilih jenis pajak untuk melihat detail laporan transaksi.</p>
            </div>
            <div class="flex flex-wrap gap-6 text-sm">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider opacity-60">Total Pendapatan</div>
                    <div class="text-lg font-extrabold mt-0.5">Rp {{ number_format($totalPendapatanAll, 0, ',', '.') }}</div>
                </div>
                @if($tahun === (int) date('Y'))
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider opacity-60">Bulan Ini</div>
                    <div class="text-lg font-extrabold mt-0.5">Rp {{ number_format($pendapatanBulanIniAll, 0, ',', '.') }}</div>
                </div>
                @endif
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider opacity-60">Total Transaksi</div>
                    <div class="text-lg font-extrabold mt-0.5">{{ number_format($totalTransaksiAll, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        @foreach($jenisPajaks as $jp)
            @php
                $s = $stats[$jp->id] ?? ['total_transaksi' => 0, 'total_pendapatan' => 0, 'pendapatan_bulan_ini' => 0, 'pending' => 0];
                $url = \App\Filament\Resources\TaxResource::getUrl('index', ['jenisPajakId' => $jp->id, 'tahun' => $tahun]);
                $colors = [
                    'Hotel' => '#dbeafe',
                    'Restoran' => '#fef3c7',
                    'Hiburan' => '#ede9fe',
                    'Reklame' => '#ffe4e6',
                    'PPJ' => '#fef9c3',
                    'Parkir' => '#e0f2fe',
                    'Air Tanah' => '#cffafe',
                    'MBLB' => '#f1f5f9',
                ];
                $bgColor = $colors[$jp->nama_singkat ?? 'Hotel'] ?? '#dbeafe';
            @endphp
            <a href="{{ $url }}" class="lp-card block" style="text-decoration:none;">
                <div class="lp-icon" style="background: {{ $bgColor }};">
                    {{ $jp->icon }}
                </div>

                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-0.5">{{ $jp->nama }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                    {{ $jp->tipe_assessment === 'self_assessment' ? 'Self Assessment' : 'Official Assessment' }}
                    &middot; Tarif {{ number_format($jp->tarif_default, 0) }}%
                </p>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="lp-stat">Pendapatan</span>
                        <span class="lp-val text-sm">Rp {{ number_format($s['total_pendapatan'], 0, ',', '.') }}</span>
                    </div>
                    @if($tahun === (int) date('Y'))
                    <div class="flex items-center justify-between">
                        <span class="lp-stat">Bulan Ini</span>
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($s['pendapatan_bulan_ini'], 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-700">
                        <span class="lp-stat">Transaksi</span>
                        <div class="flex items-center gap-2">
                            <span class="lp-badge lp-badge-blue">{{ $s['total_transaksi'] }}</span>
                            @if($s['pending'] > 0)
                                <span class="lp-badge lp-badge-amber">{{ $s['pending'] }} pending</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

@endif

</div>

</x-filament-panels::page>
