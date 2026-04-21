<x-filament-panels::page>

<style>
    :root {
        --abt-primary: #0891b2; --abt-primary-h: #0e7490;
        --abt-primary-5: rgba(8,145,178,.05);  --abt-primary-10: rgba(8,145,178,.10);
        --abt-primary-20: rgba(8,145,178,.20); --abt-primary-30: rgba(8,145,178,.30);
        --abt-shadow: rgba(8,145,178,.20);
    }
    .dark {
        --abt-primary: #22d3ee; --abt-primary-h: #06b6d4;
        --abt-primary-5: rgba(34,211,238,.10);  --abt-primary-10: rgba(34,211,238,.18);
        --abt-primary-20: rgba(34,211,238,.28); --abt-primary-30: rgba(34,211,238,.38);
        --abt-shadow: rgba(34,211,238,.30);
    }
    .abt-text-primary  { color: var(--abt-primary); }
    .abt-bg-primary    { background-color: var(--abt-primary); }
    .abt-bg-ph:hover   { background-color: var(--abt-primary-h); }
    .abt-bg-p5         { background-color: var(--abt-primary-5); }
    .abt-bg-p10        { background-color: var(--abt-primary-10); }
    .abt-bg-p20        { background-color: var(--abt-primary-20); }
    .abt-border-p10    { border-color: var(--abt-primary-10); }
    .abt-border-p20    { border-color: var(--abt-primary-20); }
    .abt-ring:focus    { --tw-ring-color: var(--abt-primary); border-color: var(--abt-primary); }
    .abt-shadow        { box-shadow: 0 4px 12px -1px var(--abt-shadow); }
    .abt-av { width:2.1rem; height:2.1rem; border-radius:.45rem; display:flex; align-items:center;
              justify-content:center; font-weight:700; font-size:.8rem; color:#fff; flex-shrink:0;
              background:linear-gradient(135deg,#06b6d4,#0891b2); }
    .abt-badge { padding:.1rem .4rem; border-radius:.25rem; font-size:.58rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
    .abt-badge-meter { background:#dbeafe; color:#1e40af; }
    .dark .abt-badge-meter { background:rgba(59,130,246,.15); color:#93c5fd; }
    .abt-badge-nopd { background:#d1fae5; color:#065f46; }
    .dark .abt-badge-nopd { background:rgba(16,185,129,.15); color:#6ee7b7; }
    .abt-card { transition:all .18s; cursor:pointer; position:relative; overflow:hidden; }
    .abt-card::before { content:""; position:absolute; left:0; top:0; bottom:0; width:3px;
                        background:transparent; transition:background .18s; border-radius:0 3px 3px 0; }
    .abt-card:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(8,145,178,.09); border-color:#67e8f9 !important; }
    .abt-card:hover::before { background:var(--abt-primary); }
    .dark .abt-card:hover { border-color:#06b6d4 !important; box-shadow:0 4px 14px rgba(34,211,238,.18); }
    .abt-card-selected { border-color:var(--abt-primary) !important; background:var(--abt-primary-5) !important; }
    .abt-card-selected::before { background:var(--abt-primary); }
    @keyframes abt-pop { 0%{transform:scale(.85);opacity:0} 100%{transform:scale(1);opacity:1} }
    .abt-pop { animation:abt-pop .35s cubic-bezier(.175,.885,.32,1.275); }
    @keyframes abt-shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
    .abt-shimmer { background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,.15) 50%,transparent 100%);
                   background-size:200% 100%; animation:abt-shimmer 2.2s infinite; }
    .dark .abt-page .bg-white { background-color: #0f172a; }
    .dark .abt-page select,
    .dark .abt-page input[type="text"],
    .dark .abt-page input[type="number"],
    .dark .abt-page input[type="month"] { background-color: #1e293b; }
    .dark .abt-page .bg-slate-50 { background-color: #1e293b; }
    .dark .abt-page .border-slate-200 { border-color: #334155; }
    .dark .abt-page .border-slate-100 { border-color: #1e293b; }

    /* ── Scenario indicators ──────────────────────────────────── */
    .abt-scenario { position:relative; padding:.75rem 1rem; border-radius:.75rem; display:flex;
        align-items:center; gap:.75rem; overflow:hidden; transition:all .22s ease; }
    .abt-scenario::before { content:""; position:absolute; left:0; top:0; bottom:0; width:4px;
        border-radius:0 4px 4px 0; }
    .abt-scenario-new { background:linear-gradient(135deg, rgba(59,130,246,.08), rgba(99,102,241,.06));
        border:1px solid rgba(59,130,246,.25); }
    .abt-scenario-new::before { background:linear-gradient(180deg, #3b82f6, #6366f1); }
    .dark .abt-scenario-new { background:linear-gradient(135deg, rgba(59,130,246,.12), rgba(99,102,241,.08));
        border-color:rgba(59,130,246,.3); }
    .abt-scenario-history { background:linear-gradient(135deg, rgba(16,185,129,.08), rgba(6,182,212,.06));
        border:1px solid rgba(16,185,129,.25); }
    .abt-scenario-history::before { background:linear-gradient(180deg, #10b981, #06b6d4); }
    .dark .abt-scenario-history { background:linear-gradient(135deg, rgba(16,185,129,.12), rgba(6,182,212,.08));
        border-color:rgba(16,185,129,.3); }
    .abt-scenario-nometer { background:linear-gradient(135deg, rgba(245,158,11,.08), rgba(239,68,68,.04));
        border:1px solid rgba(245,158,11,.25); }
    .abt-scenario-nometer::before { background:linear-gradient(180deg, #f59e0b, #ef4444); }
    .dark .abt-scenario-nometer { background:linear-gradient(135deg, rgba(245,158,11,.12), rgba(239,68,68,.06));
        border-color:rgba(245,158,11,.3); }
    .abt-scenario-icon { width:2rem; height:2rem; border-radius:.5rem; display:flex; align-items:center;
        justify-content:center; flex-shrink:0; }

    /* ── Meter card ──────────────────────────────────────────── */
    .abt-meter-card { background:linear-gradient(135deg, rgba(255,255,255,.8), rgba(248,250,252,.9));
        border:1px solid rgba(203,213,225,.5); border-radius:.875rem; padding:1rem;
        backdrop-filter:blur(8px); transition:all .2s ease; }
    .abt-meter-card:hover { border-color:rgba(6,182,212,.4); box-shadow:0 2px 12px rgba(6,182,212,.08); }
    .dark .abt-meter-card { background:linear-gradient(135deg, rgba(30,41,59,.9), rgba(15,23,42,.95));
        border-color:rgba(51,65,85,.6); }
    .dark .abt-meter-card:hover { border-color:rgba(34,211,238,.3); box-shadow:0 2px 12px rgba(34,211,238,.1); }
    .abt-meter-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em;
        color:#64748b; margin-bottom:.375rem; display:flex; align-items:center; gap:.375rem; }
    .dark .abt-meter-label { color:#94a3b8; }
    .abt-meter-input { width:100%; padding:.625rem .875rem; border:1.5px solid #e2e8f0; border-radius:.625rem;
        background:#fff; font-size:.9rem; font-weight:600; color:#0f172a;
        font-variant-numeric:tabular-nums; transition:all .18s ease; }
    .abt-meter-input:focus { outline:none; border-color:var(--abt-primary);
        box-shadow:0 0 0 3px var(--abt-primary-20); }
    .abt-meter-input::placeholder { color:#cbd5e1; font-weight:400; }
    .dark .abt-meter-input { background:#1e293b; border-color:#334155; color:#f1f5f9; }
    .dark .abt-meter-input:focus { border-color:var(--abt-primary);
        box-shadow:0 0 0 3px var(--abt-primary-20); }
    .dark .abt-meter-input::placeholder { color:#475569; }
    .abt-meter-readonly { width:100%; padding:.625rem .875rem; border:1.5px dashed #cbd5e1; border-radius:.625rem;
        background:linear-gradient(135deg, #f8fafc, #f1f5f9); font-size:.9rem; font-weight:600; color:#64748b;
        font-variant-numeric:tabular-nums; cursor:not-allowed; user-select:none; }
    .dark .abt-meter-readonly { background:linear-gradient(135deg, #1e293b, #0f172a);
        border-color:#334155; color:#94a3b8; }

    /* ── Toggle switch ──────────────────────────────────────── */
    .abt-toggle-wrap { display:flex; align-items:center; justify-content:space-between; padding:.625rem .875rem;
        border-radius:.75rem; border:1px solid #e2e8f0; background:#f8fafc; transition:all .2s; }
    .abt-toggle-wrap.active { background:linear-gradient(135deg, rgba(249,115,22,.06), rgba(245,158,11,.04));
        border-color:rgba(249,115,22,.3); }
    .dark .abt-toggle-wrap { background:#1e293b; border-color:#334155; }
    .dark .abt-toggle-wrap.active { background:linear-gradient(135deg, rgba(249,115,22,.12), rgba(245,158,11,.06));
        border-color:rgba(249,115,22,.35); }
    .abt-toggle-btn { position:relative; width:2.75rem; height:1.5rem; border-radius:999px;
        cursor:pointer; transition:background .25s cubic-bezier(.4,0,.2,1); flex-shrink:0; overflow:hidden; }
    .abt-toggle-btn .abt-toggle-dot { position:absolute; top:50%; left:2px; width:1.125rem; height:1.125rem;
        margin-top:-0.5625rem; border-radius:999px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.2);
        transition:transform .25s cubic-bezier(.4,0,.2,1); }

    /* ── Meter change panel ──────────────────────────────────── */
    .abt-change-panel { border-radius:.875rem; overflow:hidden; border:1px solid rgba(249,115,22,.25); }
    .abt-change-header { padding:.5rem 1rem; font-size:.65rem; font-weight:800; text-transform:uppercase;
        letter-spacing:.08em; display:flex; align-items:center; gap:.5rem; }
    .abt-change-header-old { background:linear-gradient(135deg, rgba(249,115,22,.1), rgba(245,158,11,.05));
        color:#c2410c; border-bottom:1px solid rgba(249,115,22,.15); }
    .abt-change-header-new { background:linear-gradient(135deg, rgba(16,185,129,.1), rgba(6,182,212,.05));
        color:#047857; border-bottom:1px solid rgba(16,185,129,.15); }
    .dark .abt-change-header-old { background:linear-gradient(135deg, rgba(249,115,22,.15), rgba(245,158,11,.08));
        color:#fb923c; }
    .dark .abt-change-header-new { background:linear-gradient(135deg, rgba(16,185,129,.15), rgba(6,182,212,.08));
        color:#34d399; }
    .dark .abt-change-panel { border-color:rgba(249,115,22,.3); }

    /* ── Usage display ──────────────────────────────────────── */
    .abt-usage-display { position:relative; padding:1rem 1.25rem; border-radius:.875rem; overflow:hidden;
        background:linear-gradient(135deg, var(--abt-primary-10), var(--abt-primary-5));
        border:1px solid var(--abt-primary-20); }
    .abt-usage-display::after { content:""; position:absolute; right:-1rem; top:-1rem;
        width:5rem; height:5rem; border-radius:50%;
        background:radial-gradient(circle, var(--abt-primary-20), transparent 70%); pointer-events:none; }
</style>

<div class="abt-page py-2">

{{-- Page header --}}
<div class="mb-5">
    <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Buat SKPD Air Tanah</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Cari objek air tanah, isi data meteran, buat draft SKPD.</p>
</div>

{{-- ================================================================== --}}
{{-- SECTION 1: SEARCH                                                   --}}
{{-- ================================================================== --}}
<x-filament::section>
    <x-slot name="heading">1. Cari Objek Air Tanah</x-slot>
    <x-slot name="description">Cari berdasarkan NIK, NPWPD, atau nama objek air tanah.</x-slot>

    <div class="relative mb-4">
        <input type="text"
               wire:model.live.debounce.400ms="searchKeyword"
               autofocus
               placeholder="NIK, NPWPD, atau nama objek air tanah..."
               class="abt-ring block w-full pl-11 pr-9 py-2.5 border border-slate-200 dark:border-slate-700
                      rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100
                      placeholder-slate-400 dark:placeholder-slate-500 text-sm">
        
        <span class="absolute inset-y-0 right-0 pr-3 flex items-center" wire:loading wire:target="updatedSearchKeyword">
            <svg class="animate-spin h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        </span>
    </div>

    @if(strlen(trim($searchKeyword ?? '')) >= 3)
        @if(count($searchResults) > 0)
            <div class="flex items-center gap-2 mb-2 px-1">
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                    {{ count($searchResults) }} Hasil
                </span>
            </div>
            <div class="space-y-2 max-h-[28rem] overflow-y-auto pr-0.5">
                @foreach($searchResults as $obj)
                    @php
                        $initial    = strtoupper(substr($obj['nama'] ?? '?', 0, 1));
                        $isSelected = $selectedWaterObjectId === $obj['id'];
                        $isExpanded = $expandedDetailId === $obj['id'];
                    @endphp
                    <div wire:click="selectObject('{{ $obj['id'] }}')"
                         class="abt-card bg-white dark:bg-slate-900 border rounded-xl p-3
                                {{ $isSelected ? 'abt-card-selected border-cyan-400 dark:border-cyan-500' : 'border-slate-200 dark:border-slate-800' }}">
                        <div class="flex items-center gap-3">
                            <div class="abt-av">{{ $initial }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-semibold text-slate-900 dark:text-white text-sm truncate max-w-[14rem]">{{ $obj['nama'] }}</span>
                                    @if($obj['last_meter_reading'])
                                        <span class="abt-badge abt-badge-meter">{{ number_format($obj['last_meter_reading']) }} m³</span>
                                    @endif
                                    @if(!($obj['uses_meter'] ?? true))
                                        <span class="abt-badge" style="background:#fef3c7;color:#92400e;">Tanpa Meter</span>
                                    @endif
                                    @if($obj['nopd'])
                                        <span class="abt-badge abt-badge-nopd">NOPD {{ $obj['nopd'] }}</span>
                                    @endif
                                    @if($isSelected)
                                        <span class="abt-badge ml-auto" style="background:#d1fae5;color:#065f46;">&#10003; Dipilih</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                    {{ $obj['npwpd'] ?? '-' }} &middot; {{ $obj['alamat'] ?? '-' }}
                                </p>
                                @if($isExpanded)
                                <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700 grid grid-cols-2 gap-x-3 gap-y-0.5 text-[11px]">
                                    <div><span class="text-slate-500 dark:text-slate-400">NOPD:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ $obj['nopd'] ?? '-' }}</span></div>
                                    <div><span class="text-slate-500 dark:text-slate-400">Kecamatan:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ $obj['kecamatan'] ?? '-' }}</span></div>
                                    <div><span class="text-slate-500 dark:text-slate-400">Kelurahan:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ $obj['kelurahan'] ?? '-' }}</span></div>
                                    <div><span class="text-slate-500 dark:text-slate-400">Meter Terakhir:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ $obj['last_meter_reading'] ? number_format($obj['last_meter_reading']) . ' m³' : '-' }}</span></div>
                                </div>
                                @endif
                            </div>
                            <button wire:click.stop="toggleDetail('{{ $obj['id'] }}')"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center border flex-shrink-0 transition-colors
                                           {{ $isExpanded ? 'border-cyan-200 bg-cyan-50 text-cyan-500 dark:border-cyan-800 dark:bg-cyan-900/20 dark:text-cyan-400'
                                                          : 'border-slate-200 text-slate-400 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-500 dark:hover:bg-slate-700' }}">
                                <svg class="w-3 h-3 transition-transform {{ $isExpanded ? 'rotate-180' : '' }}"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 text-center">
                <svg class="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Tidak ditemukan</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Coba kata kunci lain</p>
            </div>
        @endif
    @endif
</x-filament::section>

{{-- ================================================================== --}}
{{-- SECTION 2: DATA WP & OBJEK TERPILIH                                --}}
{{-- ================================================================== --}}
@if($selectedWaterObjectData && !$skpdResult)
<x-filament::section>
    <x-slot name="heading">2. Data Wajib Pajak &amp; Objek Air Tanah</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-green-100 dark:bg-green-800">
                <x-heroicon-s-check-circle class="w-5 h-5 text-green-600 dark:text-green-400" />
            </div>
            <h4 class="font-semibold text-green-700 dark:text-green-300 text-base">Objek Air Tanah Dipilih</h4>
        </div>
        <button wire:click="buatBaru"
                class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200
                       flex items-center gap-1.5 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Reset
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- WP Info --}}
        @if($wajibPajakData)
        <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Wajib Pajak</p>
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user class="w-4 h-4 text-slate-400 shrink-0" />
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $wajibPajakData['nama_lengkap'] }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <x-heroicon-o-identification class="w-4 h-4 text-slate-400 shrink-0" />
                    <span class="text-sm font-mono text-slate-700 dark:text-slate-300">{{ $wajibPajakData['nik'] }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <x-heroicon-o-map-pin class="w-4 h-4 text-slate-400 shrink-0" />
                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ str()->limit($wajibPajakData['alamat'] ?? '-', 50) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <x-heroicon-o-document-text class="w-4 h-4 text-slate-400 shrink-0" />
                    <span class="text-sm font-mono text-slate-700 dark:text-slate-300">{{ $wajibPajakData['npwpd'] ?? '-' }}</span>
                </div>
            </div>
        </div>
        @else
        <div class="bg-amber-50 dark:bg-amber-900/15 rounded-xl p-4 border border-amber-200 dark:border-amber-800/50">
            <p class="text-sm text-amber-700 dark:text-amber-300 font-medium">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline -mt-0.5" />
                Wajib pajak terdaftar tidak ditemukan untuk objek ini.
            </p>
        </div>
        @endif

        {{-- Object Info --}}
        <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Objek Air Tanah</p>
            <div class="space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $selectedWaterObjectData['nama'] }}</span>
                    @if($selectedWaterObjectData['nopd'])
                        <span class="abt-badge abt-badge-nopd">NOPD {{ $selectedWaterObjectData['nopd'] }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <x-heroicon-o-map-pin class="w-4 h-4 text-slate-400 shrink-0" />
                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ str()->limit($selectedWaterObjectData['alamat'] ?? '-', 50) }}</span>
                </div>
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Kec. <span class="font-medium text-slate-800 dark:text-slate-200">{{ $selectedWaterObjectData['kecamatan'] ?? '-' }}</span>
                    &middot; Kel. <span class="font-medium text-slate-800 dark:text-slate-200">{{ $selectedWaterObjectData['kelurahan'] ?? '-' }}</span>
                </div>
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Meter Terakhir: <span class="font-bold abt-text-primary">{{ $selectedWaterObjectData['last_meter_reading'] ? number_format($selectedWaterObjectData['last_meter_reading']) . ' m³' : '-' }}</span>
                </div>
            </div>
        </div>
    </div>
</x-filament::section>

{{-- ================================================================== --}}
{{-- SECTION 3: PERHITUNGAN SKPD AIR TANAH                              --}}
{{-- ================================================================== --}}
<x-filament::section>
    <x-slot name="heading">3. Perhitungan SKPD Air Tanah</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- LEFT: Form Input --}}
        <div class="space-y-4">
            {{-- Periode Bulan --}}
            <div>
                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                    Periode Bulan
                </label>
                <input type="month" wire:model.live="periodeBulan"
                       class="abt-ring block w-full py-2 px-3 border border-slate-200 dark:border-slate-700
                              rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
            </div>

            {{-- ── Scenario Indicator ── --}}
            @if(!$usesMeter)
                <div class="abt-scenario abt-scenario-nometer">
                    <div class="abt-scenario-icon" style="background:linear-gradient(135deg,#f59e0b,#ef4444);">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-amber-700 dark:text-amber-300">Tanpa Meteran Air</p>
                        <p class="text-[10px] text-amber-600/70 dark:text-amber-400/60 mt-0.5">Input penggunaan air secara langsung dalam m³</p>
                    </div>
                </div>
            @elseif($isNewObject)
                <div class="abt-scenario abt-scenario-new">
                    <div class="abt-scenario-icon" style="background:linear-gradient(135deg,#3b82f6,#6366f1);">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-blue-700 dark:text-blue-300">Objek Pajak Baru</p>
                        <p class="text-[10px] text-blue-600/70 dark:text-blue-400/60 mt-0.5">Meter awal dapat diisi secara manual sesuai kondisi lapangan</p>
                    </div>
                </div>
            @elseif($hasHistory)
                <div class="abt-scenario abt-scenario-history">
                    <div class="abt-scenario-icon" style="background:linear-gradient(135deg,#10b981,#06b6d4);">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-emerald-700 dark:text-emerald-300">Histori Meter Tersedia</p>
                        <p class="text-[10px] text-emerald-600/70 dark:text-emerald-400/60 mt-0.5">Meter awal diambil otomatis dari pembacaan terakhir</p>
                    </div>
                </div>
            @endif

            {{-- ── Skenario 2: Tanpa Meteran ── --}}
            @if(!$usesMeter)
                <div class="abt-meter-card">
                    <div class="abt-meter-label">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Penggunaan Air (m³)
                    </div>
                    <div x-data="abtMeter(@entangle('directUsage').live)">
                        <input type="text" x-ref="input" :value="display"
                               x-on:input="onInput($event)" x-on:blur="onBlur()" x-on:focus="onFocus()"
                               inputmode="decimal" placeholder="Masukkan pemakaian air..."
                               class="abt-meter-input">
                    </div>
                </div>

            {{-- ── Skenario 1, 3, 4: Pakai Meteran ── --}}
            @else
                {{-- Toggle Pergantian Meteran --}}
                @if($hasHistory)
                <div class="abt-toggle-wrap {{ $isMeterChange ? 'active' : '' }}">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ $isMeterChange ? 'bg-orange-100 dark:bg-orange-500/15' : 'bg-slate-200 dark:bg-slate-700' }}" style="transition:all .2s">
                            <svg class="w-3.5 h-3.5 {{ $isMeterChange ? 'text-orange-600 dark:text-orange-400' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transition:color .2s">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold {{ $isMeterChange ? 'text-orange-700 dark:text-orange-300' : 'text-slate-600 dark:text-slate-400' }}" style="transition:color .2s">Pergantian meteran bulan ini?</p>
                            <p class="text-[10px] {{ $isMeterChange ? 'text-orange-500/70 dark:text-orange-400/50' : 'text-slate-400 dark:text-slate-500' }}" style="transition:color .2s">Aktifkan jika meteran diganti di pertengahan bulan</p>
                        </div>
                    </div>
                    <button wire:click="toggleMeterChange" type="button"
                            class="abt-toggle-btn {{ $isMeterChange ? 'bg-orange-500' : 'bg-slate-300 dark:bg-slate-600' }}">
                        <span class="abt-toggle-dot {{ $isMeterChange ? 'translate-x-[1.375rem]' : 'translate-x-0' }}" style="transition:transform .25s cubic-bezier(.4,0,.2,1)"></span>
                    </button>
                </div>
                @endif

                @if($isMeterChange)
                {{-- ── Skenario 3: Ganti Meteran (Opsi B) ── --}}
                <div class="abt-change-panel">
                    {{-- Meteran Lama --}}
                    <div class="abt-change-header abt-change-header-old">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Meteran Lama
                    </div>
                    <div class="p-3 grid grid-cols-2 gap-3 bg-white dark:bg-slate-900">
                        <div>
                            <div class="abt-meter-label">Meter Awal (Lama)</div>
                            <div class="abt-meter-readonly">{{ $meterReadingBefore !== null ? number_format($meterReadingBefore, 2, ',', '.') : '0' }}</div>
                        </div>
                        <div x-data="abtMeter(@entangle('meterOldEnd').live)">
                            <div class="abt-meter-label">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                Meter Akhir (Lama)
                            </div>
                            <input type="text" x-ref="input" :value="display"
                                   x-on:input="onInput($event)" x-on:blur="onBlur()" x-on:focus="onFocus()"
                                   inputmode="decimal" placeholder="0" class="abt-meter-input">
                        </div>
                    </div>

                    {{-- Meteran Baru --}}
                    <div class="abt-change-header abt-change-header-new">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Meteran Baru
                    </div>
                    <div class="p-3 grid grid-cols-2 gap-3 bg-white dark:bg-slate-900">
                        <div x-data="abtMeter(@entangle('meterNewStart').live)">
                            <div class="abt-meter-label">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Meter Awal (Baru)
                            </div>
                            <input type="text" x-ref="input" :value="display"
                                   x-on:input="onInput($event)" x-on:blur="onBlur()" x-on:focus="onFocus()"
                                   inputmode="decimal" placeholder="0" class="abt-meter-input">
                        </div>
                        <div x-data="abtMeter(@entangle('meterNewEnd').live)">
                            <div class="abt-meter-label">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Meter Akhir (Baru)
                            </div>
                            <input type="text" x-ref="input" :value="display"
                                   x-on:input="onInput($event)" x-on:blur="onBlur()" x-on:focus="onFocus()"
                                   inputmode="decimal" placeholder="0" class="abt-meter-input">
                        </div>
                    </div>

                    {{-- Catatan --}}
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700/50">
                        <div class="abt-meter-label">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                            Catatan Pergantian
                        </div>
                        <input type="text" wire:model.live="catatanMeter"
                               placeholder="Contoh: Meteran lama rusak, diganti 15 Maret 2026"
                               class="abt-meter-input" style="font-weight:400; font-size:.8rem;">
                    </div>
                </div>
                @else
                {{-- ── Skenario 1 & 4: Normal Meter Awal → Akhir ── --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="abt-meter-card">
                        <div class="abt-meter-label">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                            Meter Awal
                        </div>
                        @if($hasHistory)
                            <div class="abt-meter-readonly">{{ $meterReadingBefore !== null ? number_format($meterReadingBefore, 2, ',', '.') : '0' }}</div>
                        @else
                            <div x-data="abtMeter(@entangle('meterReadingBefore').live)">
                                <input type="text" x-ref="input" :value="display"
                                       x-on:input="onInput($event)" x-on:blur="onBlur()" x-on:focus="onFocus()"
                                       inputmode="decimal" placeholder="0" class="abt-meter-input">
                            </div>
                        @endif
                    </div>
                    <div class="abt-meter-card">
                        <div class="abt-meter-label">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                            Meter Akhir
                        </div>
                        <div x-data="abtMeter(@entangle('meterReadingAfter').live)">
                            <input type="text" x-ref="input" :value="display"
                                   x-on:input="onInput($event)" x-on:blur="onBlur()" x-on:focus="onFocus()"
                                   inputmode="decimal" placeholder="0" class="abt-meter-input">
                        </div>
                    </div>
                </div>
                @endif
            @endif

            {{-- ── Pemakaian Display ── --}}
            @php $preview = $this->getPreviewPajak(); @endphp
            <div class="abt-usage-display">
                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:var(--abt-primary-20);">
                            <svg class="w-4 h-4 abt-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Pemakaian</span>
                    </div>
                    <span class="text-2xl font-black abt-text-primary tabular-nums">{{ $preview ? number_format($preview['usage'], 2, ',', '.') : '0' }} <span class="text-sm font-bold opacity-70">m³</span></span>
                </div>
            </div>

            {{-- Tarif --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        <svg class="w-3 h-3 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Harga NPA
                    </label>
                    <div class="py-2.5 px-3 border border-dashed border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-slate-800/60 text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed select-none">
                        Progresif Otomatis
                    </div>
                </div>
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        <svg class="w-3 h-3 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Tarif Pajak (%)
                    </label>
                    <div class="py-2.5 px-3 border border-dashed border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-slate-800/60 text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed select-none">
                        {{ $tarifPersen ?: 0 }}%
                    </div>
                </div>
            </div>

            @include('filament.pages.partials.skpd-air-tanah-lampiran-upload')
        </div>

        {{-- RIGHT: Rincian Perhitungan --}}
        <div class="lg:self-start">
            <div class="lg:sticky lg:top-4 space-y-10">
            @if($preview && $preview['usage'] > 0)
            <div class="rounded-xl overflow-hidden
                        bg-white border border-slate-200 shadow-sm
                        dark:bg-transparent dark:border-slate-600 dark:shadow-none"
                 style="--rp-bg: linear-gradient(135deg, #1E293B 0%, #334155 100%);">
                {{-- Apply dark gradient only in dark mode via nested div --}}
                <div class="dark:hidden">
                    {{-- ═══ LIGHT MODE ═══ --}}
                    <div class="px-4 sm:px-5 pt-4 pb-2 border-b border-slate-100"
                         style="background:linear-gradient(135deg, #f8fafc, #f1f5f9);">
                        <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md flex items-center justify-center" style="background:var(--abt-primary-20);">
                                <svg class="w-3.5 h-3.5 abt-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            Rincian Perhitungan
                        </h4>
                    </div>
                    @if(!empty($preview['tiers']))
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr style="background:#f8fafc;">
                                    <th class="px-4 py-2.5 text-[10px] uppercase tracking-wider font-bold text-slate-400 text-left border-b border-slate-100">Tier</th>
                                    <th class="px-4 py-2.5 text-[10px] uppercase tracking-wider font-bold text-slate-400 text-left border-b border-slate-100">Rentang Volume</th>
                                    <th class="px-4 py-2.5 text-[10px] uppercase tracking-wider font-bold text-slate-400 text-right border-b border-slate-100">Vol (m³)</th>
                                    <th class="px-4 py-2.5 text-[10px] uppercase tracking-wider font-bold text-slate-400 text-right border-b border-slate-100">HDA / NPA (Rp/m³)</th>
                                    <th class="px-4 py-2.5 text-[10px] uppercase tracking-wider font-bold text-slate-400 text-right border-b border-slate-100">NPA (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preview['tiers'] as $t)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-2.5 text-xs text-slate-600 border-b border-slate-50">{{ $t['tier'] }}</td>
                                    <td class="px-4 py-2.5 text-xs text-slate-600 border-b border-slate-50">
                                        {{ number_format($t['min_vol']) }}–{{ (!$t['max_vol'] || $t['max_vol'] >= 99999999) ? '∞' : number_format($t['max_vol']) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-slate-700 text-right tabular-nums border-b border-slate-50">{{ number_format($t['volume'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-xs text-slate-700 text-right tabular-nums border-b border-slate-50">{{ number_format($t['npa_rate'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-xs text-slate-900 font-bold text-right tabular-nums border-b border-slate-50">{{ number_format($t['npa'], 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                    <div class="px-4 sm:px-5 py-3 space-y-1.5" style="background:linear-gradient(135deg, #f8fafc, #f1f5f9); border-top:1px solid #e2e8f0;">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500">Total NPA (Nilai Perolehan Air)</span>
                            <span class="text-sm font-bold text-slate-800 tabular-nums">Rp {{ number_format($preview['dasar'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500">Tarif Pajak</span>
                            <span class="text-sm font-bold text-slate-800">{{ $tarifPersen ?: 0 }}%</span>
                        </div>
                        <div class="flex justify-between items-center pt-2.5 mt-2.5" style="border-top:2px solid var(--abt-primary-20);">
                            <span class="text-sm font-bold text-slate-800">Estimasi Pajak Air Tanah</span>
                            <span class="text-lg font-extrabold abt-text-primary tabular-nums">Rp {{ number_format($preview['pajak'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <div class="hidden dark:block" style="background: linear-gradient(135deg, #1E293B 0%, #334155 100%);">
                    {{-- ═══ DARK MODE ═══ --}}
                    <div class="px-4 sm:px-5 pt-4 pb-2">
                        <h4 class="text-sm font-bold text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Rincian Perhitungan
                        </h4>
                    </div>
                    @if(!empty($preview['tiers']))
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-[10px] uppercase tracking-wider font-semibold text-slate-400 text-left border-b border-white/10">Tier</th>
                                    <th class="px-4 py-2 text-[10px] uppercase tracking-wider font-semibold text-slate-400 text-left border-b border-white/10">Rentang Volume</th>
                                    <th class="px-4 py-2 text-[10px] uppercase tracking-wider font-semibold text-slate-400 text-right border-b border-white/10">Vol (m³)</th>
                                    <th class="px-4 py-2 text-[10px] uppercase tracking-wider font-semibold text-slate-400 text-right border-b border-white/10">HDA / NPA (Rp/m³)</th>
                                    <th class="px-4 py-2 text-[10px] uppercase tracking-wider font-semibold text-slate-400 text-right border-b border-white/10">NPA (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preview['tiers'] as $t)
                                <tr>
                                    <td class="px-4 py-2 text-xs text-white/80 border-b border-white/5">{{ $t['tier'] }}</td>
                                    <td class="px-4 py-2 text-xs text-white/80 border-b border-white/5">
                                        {{ number_format($t['min_vol']) }}–{{ (!$t['max_vol'] || $t['max_vol'] >= 99999999) ? '∞' : number_format($t['max_vol']) }}
                                    </td>
                                    <td class="px-4 py-2 text-xs text-white/90 text-right tabular-nums border-b border-white/5">{{ number_format($t['volume'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-xs text-white/90 text-right tabular-nums border-b border-white/5">{{ number_format($t['npa_rate'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-xs text-white font-semibold text-right tabular-nums border-b border-white/5">{{ number_format($t['npa'], 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                    <div class="px-4 sm:px-5 py-3 space-y-1.5">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-white/70">Total NPA (Nilai Perolehan Air)</span>
                            <span class="text-sm font-bold text-white tabular-nums">Rp {{ number_format($preview['dasar'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-white/70">Tarif Pajak</span>
                            <span class="text-sm font-bold text-white">{{ $tarifPersen ?: 0 }}%</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 mt-2 border-t-2 border-white/20">
                            <span class="text-sm font-bold text-white">Estimasi Pajak Air Tanah</span>
                            <span class="text-lg font-extrabold tabular-nums" style="color: #FCD34D;">Rp {{ number_format($preview['pajak'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="flex flex-col items-center justify-center h-full min-h-[16rem]
                        bg-slate-50 dark:bg-slate-800 rounded-xl border border-dashed
                        border-slate-200 dark:border-slate-700 p-4 sm:p-8 text-center">
                <div class="w-12 h-12 rounded-2xl abt-bg-p10 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 abt-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01
                                 M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">Rincian Perhitungan</p>
                <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">Lengkapi data meteran untuk melihat rincian pajak.</p>
            </div>
            @endif

            {{-- Submit Button --}}
            <div style="margin-top: 1.75rem;">
                <button wire:click="buatSkpd"
                        wire:loading.attr="disabled"
                        @if(!$preview || $preview['usage'] <= 0) disabled @endif
                        class="w-full abt-bg-primary abt-bg-ph abt-shadow disabled:opacity-40 disabled:cursor-not-allowed
                               text-white font-bold py-3.5 rounded-xl transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                    <svg wire:loading.remove wire:target="buatSkpd" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg wire:loading wire:target="buatSkpd" class="animate-spin h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span wire:loading.remove wire:target="buatSkpd">Buat Draft SKPD</span>
                    <span wire:loading wire:target="buatSkpd">Memproses...</span>
                </button>
                <p class="text-[10px] text-center text-slate-500 dark:text-slate-400 mt-2 italic">
                    Draft SKPD akan dikirim ke Verifikator untuk persetujuan.
                </p>
            </div>
            </div>
        </div>

    </div>
</x-filament::section>
@endif

{{-- ================================================================== --}}
{{-- SECTION 4: HASIL SKPD                                              --}}
{{-- ================================================================== --}}
@if($skpdResult)
<div class="abt-pop space-y-4">
    <div class="text-center py-4">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600
                    flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/25">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="text-lg font-extrabold text-slate-900 dark:text-white mt-3">Draft SKPD Berhasil Dibuat!</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">Menunggu verifikasi oleh Verifikator.</p>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="relative overflow-hidden dark:bg-slate-800 px-6 py-5 text-center"
             style="background:linear-gradient(135deg, #f0fdfa, #e0f2fe);">
            <div class="dark:hidden relative z-10">
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-[.2em] mb-1.5">Nomor SKPD</p>
                <p class="text-slate-900 text-xl font-mono font-extrabold tracking-wide">{{ $skpdResult['nomor_skpd'] }}</p>
            </div>
            <div class="hidden dark:block relative z-10" style="background:none;">
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[.2em] mb-1.5">Nomor SKPD</p>
                <p class="text-white text-xl font-mono font-extrabold tracking-wide">{{ $skpdResult['nomor_skpd'] }}</p>
            </div>
            <div class="abt-shimmer absolute inset-0 pointer-events-none hidden dark:block"></div>
        </div>
        <div class="p-4 space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                    <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Wajib Pajak</p>
                    <p class="font-semibold text-slate-900 dark:text-white mt-0.5 truncate">{{ $skpdResult['nama_wp'] }}</p>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                    <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Objek</p>
                    <p class="font-semibold text-slate-900 dark:text-white mt-0.5 truncate">{{ $skpdResult['nama_objek'] }}</p>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                    <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Periode</p>
                    <p class="font-semibold text-slate-900 dark:text-white mt-0.5">{{ $skpdResult['periode'] }}</p>
                </div>
            </div>

            @if($skpdResult['uses_meter'] ?? true)
            <div class="grid grid-cols-3 gap-3 text-sm">
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-center">
                    <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Meter Awal</p>
                    <p class="font-mono font-semibold text-slate-900 dark:text-white mt-0.5">{{ $skpdResult['meter_before'] !== null ? number_format($skpdResult['meter_before'], 2, ',', '.') : '-' }}</p>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-center">
                    <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Meter Akhir</p>
                    <p class="font-mono font-semibold text-slate-900 dark:text-white mt-0.5">{{ $skpdResult['meter_after'] !== null ? number_format($skpdResult['meter_after'], 2, ',', '.') : '-' }}</p>
                </div>
                <div class="p-3 rounded-lg abt-bg-p10 border abt-border-p20 text-center">
                    <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pemakaian</p>
                    <p class="font-mono font-bold abt-text-primary mt-0.5">{{ number_format($skpdResult['usage'], 2, ',', '.') }} m³</p>
                </div>
            </div>
            @if($skpdResult['is_meter_change'] ?? false)
                <div class="p-2.5 rounded-lg bg-orange-50 dark:bg-orange-900/10 border border-orange-200 dark:border-orange-800/40">
                    <p class="text-[10px] font-bold text-orange-600 dark:text-orange-400">⚠ Pergantian Meteran</p>
                </div>
            @endif
            @else
            <div class="grid grid-cols-1 gap-3 text-sm">
                <div class="p-3 rounded-lg abt-bg-p10 border abt-border-p20 text-center">
                    <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Penggunaan Air (Tanpa Meter)</p>
                    <p class="font-mono font-bold abt-text-primary mt-0.5">{{ number_format($skpdResult['usage'], 2, ',', '.') }} m³</p>
                </div>
            </div>
            @endif

            @if($skpdResult['lampiran_path'] ?? false)
            <div class="grid grid-cols-1 gap-3 text-sm">
                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($skpdResult['lampiran_path']) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="p-3 rounded-lg border border-cyan-200 dark:border-cyan-800/40 bg-cyan-50 dark:bg-cyan-900/10 flex items-center justify-between gap-3 hover:bg-cyan-100 dark:hover:bg-cyan-900/20 transition-colors">
                    <div>
                        <p class="text-[10px] font-bold text-cyan-700 dark:text-cyan-300 uppercase tracking-wider">Lampiran Pendukung</p>
                        <p class="font-semibold text-cyan-900 dark:text-cyan-100 mt-0.5">Lihat file lampiran</p>
                    </div>
                    <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h6m0 0v6m0-6L10 16m-4-9H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-1"/>
                    </svg>
                </a>
            </div>
            @endif

            <div class="space-y-1.5">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Dasar Pengenaan</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200 tabular-nums">Rp {{ number_format($skpdResult['dasar_pengenaan'],0,',','.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Total Pajak</span>
                    <span class="text-xl font-black abt-text-primary tabular-nums">Rp {{ number_format($skpdResult['jumlah_pajak'],0,',','.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        @if(auth()->user()->role === 'petugas')
        <a href="{{ $skpdResult['daftar_url'] }}"
           class="flex-1 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-bold
                  py-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50
                  dark:hover:bg-slate-800 transition-all flex items-center justify-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Lihat Daftar SKPD
        </a>
        @else
        <a href="{{ $skpdResult['verifikasi_url'] }}"
           class="flex-1 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-bold
                  py-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50
                  dark:hover:bg-slate-800 transition-all flex items-center justify-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Lihat Verifikasi
        </a>
        @endif
        <button wire:click="buatBaru"
                class="flex-1 abt-bg-primary abt-bg-ph abt-shadow text-white font-bold
                       py-3 rounded-xl transition-all flex items-center justify-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat SKPD Baru
        </button>
    </div>
</div>
@endif

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('abtMeter', (model) => ({
        raw: model,
        display: '',
        init() {
            this.display = this.fmt(this.raw);
            this.$watch('raw', v => {
                if (document.activeElement !== this.$refs.input) this.display = this.fmt(v);
            });
        },
        fmt(v) {
            if (v === null || v === undefined || v === '') return '';
            let s = String(v);
            // convert JS decimal point to comma
            let parts = s.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return parts.length > 1 ? parts[0] + ',' + parts[1] : parts[0];
        },
        parse(s) {
            if (!s) return null;
            let n = parseFloat(s.replace(/\./g, '').replace(',', '.'));
            return isNaN(n) ? null : n;
        },
        onInput(e) {
            let v = e.target.value.replace(/[^0-9,]/g, '');
            let parts = v.split(',');
            if (parts.length > 2) v = parts[0] + ',' + parts.slice(1).join('');
            if (parts.length > 1 && parts[1].length > 2) v = parts[0] + ',' + parts[1].substring(0, 2);
            // live format thousands on integer part
            parts = v.split(',');
            let intPart = parts[0].replace(/\./g, '');
            intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            v = parts.length > 1 ? intPart + ',' + parts[1] : intPart;
            this.display = v;
            // preserve cursor position
            let pos = e.target.selectionStart;
            let oldLen = e.target.value.length;
            this.$nextTick(() => {
                let newLen = this.display.length;
                let newPos = pos + (newLen - oldLen);
                this.$refs.input.setSelectionRange(newPos, newPos);
            });
            this.raw = this.parse(v);
        },
        onFocus() {},
        onBlur() { this.display = this.fmt(this.raw); }
    }));

    Alpine.data('abtLampiranUpload', () => ({
        previewUrl: null,
        fileSize: null,
        fileExt: null,
        originalSize: null,
        isCompressing: false,
        isUploading: false,
        uploadProgress: 0,
        isDragging: false,
        errorMessage: null,
        showLightbox: false,

        formatSize(bytes) {
            if (!bytes) return '-';
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(2) + ' MB';
        },

        isImage(ext) {
            return ['jpg', 'jpeg', 'png', 'webp', 'gif'].includes((ext || '').toLowerCase());
        },

        async compressImage(file, maxBytes) {
            return new Promise((resolve, reject) => {
                const url = URL.createObjectURL(file);
                const img = new Image();

                img.onerror = () => {
                    URL.revokeObjectURL(url);
                    reject('Gagal memuat gambar.');
                };

                img.onload = () => {
                    URL.revokeObjectURL(url);

                    let width = img.width;
                    let height = img.height;
                    const maxDimension = 2048;

                    if (width > maxDimension || height > maxDimension) {
                        const ratio = Math.min(maxDimension / width, maxDimension / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                    }

                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');

                    const attempt = (quality, scale) => {
                        const scaledWidth = Math.round(width * scale);
                        const scaledHeight = Math.round(height * scale);

                        canvas.width = scaledWidth;
                        canvas.height = scaledHeight;
                        context.clearRect(0, 0, scaledWidth, scaledHeight);
                        context.drawImage(img, 0, 0, scaledWidth, scaledHeight);

                        canvas.toBlob((blob) => {
                            if (!blob) {
                                reject('Gagal mengompres gambar.');
                                return;
                            }

                            if (blob.size <= maxBytes || (quality <= 0.15 && scale <= 0.3)) {
                                resolve(new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {
                                    type: 'image/jpeg',
                                    lastModified: Date.now(),
                                }));
                                return;
                            }

                            if (quality > 0.2) {
                                attempt(quality - 0.1, scale);
                                return;
                            }

                            attempt(0.7, Math.max(scale - 0.2, 0.3));
                        }, 'image/jpeg', quality);
                    };

                    attempt(0.85, 1);
                };

                img.src = url;
            });
        },

        async processFile(file) {
            this.errorMessage = null;
            this.originalSize = file.size;

            const ext = file.name.split('.').pop().toLowerCase();
            let uploadFile = file;

            if (this.isImage(ext)) {
                if (file.size > 1048576) {
                    this.isCompressing = true;

                    try {
                        uploadFile = await this.compressImage(file, 1048576);
                    } catch (error) {
                        this.errorMessage = typeof error === 'string' ? error : 'Gagal mengompres gambar.';
                        this.isCompressing = false;
                        return;
                    }

                    this.isCompressing = false;
                }
            } else if (ext === 'pdf') {
                if (file.size > 1048576) {
                    this.errorMessage = 'Ukuran file PDF maksimal 1 MB.';
                    return;
                }
            } else {
                this.errorMessage = 'Format file tidak didukung. Gunakan JPG, JPEG, PNG, WEBP, atau PDF.';
                return;
            }

            if (this.previewUrl && this.previewUrl.startsWith('blob:')) {
                URL.revokeObjectURL(this.previewUrl);
            }

            const localPreviewUrl = URL.createObjectURL(uploadFile);

            this.isUploading = true;
            this.uploadProgress = 0;

            $wire.upload('lampiranUploadTemp', uploadFile,
                () => {
                    this.previewUrl = localPreviewUrl;
                    this.fileSize = uploadFile.size;
                    this.fileExt = uploadFile.name.split('.').pop().toLowerCase();
                    this.isUploading = false;
                },
                () => {
                    this.errorMessage = 'Gagal mengunggah lampiran.';
                    this.isUploading = false;
                },
                (event) => {
                    this.uploadProgress = event.detail?.progress || 0;
                }
            );
        },

        handleFileInput(event) {
            const file = event.target.files?.[0];

            if (file) {
                this.processFile(file);
            }
        },

        handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer?.files?.[0];

            if (file) {
                this.processFile(file);
            }
        },

        async removeFile() {
            if (this.previewUrl && this.previewUrl.startsWith('blob:')) {
                URL.revokeObjectURL(this.previewUrl);
            }

            this.previewUrl = null;
            this.fileSize = null;
            this.fileExt = null;
            this.originalSize = null;
            this.errorMessage = null;
            this.showLightbox = false;
            this.uploadProgress = 0;

            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }

            await $wire.removeLampiranUpload();
        },
    }));
});
</script>
</x-filament-panels::page>
