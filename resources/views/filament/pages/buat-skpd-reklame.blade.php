<x-filament-panels::page>

<style>
    :root {
        --rkl-primary: #e11d48; --rkl-primary-h: #be123c;
        --rkl-primary-5: rgba(225,29,72,.05);  --rkl-primary-10: rgba(225,29,72,.10);
        --rkl-primary-20: rgba(225,29,72,.20); --rkl-primary-30: rgba(225,29,72,.30);
        --rkl-shadow: rgba(225,29,72,.20);
    }
    .dark {
        --rkl-primary: #fb7185; --rkl-primary-h: #f43f5e;
        --rkl-primary-5: rgba(251,113,133,.10);  --rkl-primary-10: rgba(251,113,133,.18);
        --rkl-primary-20: rgba(251,113,133,.28); --rkl-primary-30: rgba(251,113,133,.38);
        --rkl-shadow: rgba(251,113,133,.30);
    }
    .rkl-text-primary  { color: var(--rkl-primary); }
    .rkl-bg-primary    { background-color: var(--rkl-primary); }
    .rkl-bg-ph:hover   { background-color: var(--rkl-primary-h); }
    .rkl-bg-p5         { background-color: var(--rkl-primary-5); }
    .rkl-bg-p10        { background-color: var(--rkl-primary-10); }
    .rkl-bg-p20        { background-color: var(--rkl-primary-20); }
    .rkl-border-p10    { border-color: var(--rkl-primary-10); }
    .rkl-border-p20    { border-color: var(--rkl-primary-20); }
    .rkl-ring:focus    { --tw-ring-color: var(--rkl-primary); border-color: var(--rkl-primary); }
    .rkl-shadow        { box-shadow: 0 4px 12px -1px var(--rkl-shadow); }
    .rkl-av { width:2.1rem; height:2.1rem; border-radius:.45rem; display:flex; align-items:center;
              justify-content:center; font-weight:700; font-size:.8rem; color:#fff; flex-shrink:0;
              background:linear-gradient(135deg,#f43f5e,#e11d48); }
    .rkl-badge { padding:.1rem .4rem; border-radius:.25rem; font-size:.58rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
    .rkl-badge-tetap { background:#dbeafe; color:#1e40af; }
    .dark .rkl-badge-tetap { background:rgba(59,130,246,.15); color:#93c5fd; }
    .rkl-badge-insidentil { background:#fef3c7; color:#92400e; }
    .dark .rkl-badge-insidentil { background:rgba(245,158,11,.15); color:#fcd34d; }
    .rkl-badge-lokasi { background:#d1fae5; color:#065f46; }
    .dark .rkl-badge-lokasi { background:rgba(16,185,129,.15); color:#6ee7b7; }
    .rkl-card { transition:all .18s; cursor:pointer; position:relative; overflow:hidden; }
    .rkl-card::before { content:""; position:absolute; left:0; top:0; bottom:0; width:3px;
                        background:transparent; transition:background .18s; border-radius:0 3px 3px 0; }
    .rkl-card:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(225,29,72,.09); border-color:#fda4af !important; }
    .rkl-card:hover::before { background:var(--rkl-primary); }
    .dark .rkl-card:hover { border-color:#f43f5e !important; box-shadow:0 4px 14px rgba(251,113,133,.18); }
    .rkl-card-selected { border-color:var(--rkl-primary) !important; background:var(--rkl-primary-5) !important; }
    .rkl-card-selected::before { background:var(--rkl-primary); }
    @keyframes rkl-pop { 0%{transform:scale(.85);opacity:0} 100%{transform:scale(1);opacity:1} }
    .rkl-pop { animation:rkl-pop .35s cubic-bezier(.175,.885,.32,1.275); }
    @keyframes rkl-shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
    .rkl-shimmer { background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,.15) 50%,transparent 100%);
                   background-size:200% 100%; animation:rkl-shimmer 2.2s infinite; }
    .dark .rkl-page .bg-white { background-color: #0f172a; }
    .dark .rkl-page select,
    .dark .rkl-page input[type="text"],
    .dark .rkl-page input[type="number"],
    .dark .rkl-page input[type="date"] { background-color: #1e293b; }
    .dark .rkl-page .bg-slate-50 { background-color: #1e293b; }
    .dark .rkl-page .border-slate-200 { border-color: #334155; }
    .dark .rkl-page .border-slate-100 { border-color: #1e293b; }
</style>

<div class="rkl-page py-2">

{{-- Page header --}}
<div class="mb-5">
    <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Buat SKPD Reklame</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Cari objek reklame, isi parameter perhitungan, buat draft SKPD.</p>
</div>

{{-- ================================================================== --}}
{{-- MODE TABS                                                           --}}
{{-- ================================================================== --}}
<div class="flex gap-2 mb-5">
    <button wire:click="switchMode('objek_wp')"
            class="flex-1 py-3 px-4 rounded-xl text-sm font-bold transition-all border
                   {{ $mode === 'objek_wp' ? 'rkl-bg-primary text-white border-transparent rkl-shadow' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
        <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        Objek WP (Swasta)
    </button>
    <button wire:click="switchMode('aset_pemkab')"
            class="flex-1 py-3 px-4 rounded-xl text-sm font-bold transition-all border
                   {{ $mode === 'aset_pemkab' ? 'rkl-bg-primary text-white border-transparent rkl-shadow' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
        <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        Aset Pemkab (Sewa)
    </button>
</div>

@if($permohonanData)
<div class="mb-5 bg-blue-50 dark:bg-blue-900/15 rounded-xl p-4 border border-blue-200 dark:border-blue-800/50">
    <div class="flex items-center gap-2 mb-1">
        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-sm font-bold text-blue-700 dark:text-blue-300">Dari Permohonan Sewa Online</span>
    </div>
    <p class="text-xs text-blue-600 dark:text-blue-400">
        Data penyewa dan aset telah diisi otomatis dari permohonan. Petugas: {{ $permohonanData['nama'] }} &middot; Durasi diminta: {{ $permohonanData['durasi_sewa_hari'] }} hari
    </p>
</div>
@endif

{{-- ================================================================== --}}
{{-- SECTION 1: SEARCH (MODE: OBJEK WP)                                 --}}
{{-- ================================================================== --}}
@if($mode === 'objek_wp')
<x-filament::section>
    <x-slot name="heading">1. Cari Objek Reklame</x-slot>
    <x-slot name="description">Cari berdasarkan NIK, NPWPD, atau nama objek reklame.</x-slot>

    <div class="relative mb-4">
        <input type="text"
               wire:model.live.debounce.400ms="searchKeyword"
               autofocus
               placeholder="NIK, NPWPD, atau nama objek reklame..."
               class="rkl-ring block w-full pl-11 pr-9 py-2.5 border border-slate-200 dark:border-slate-700
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
                        $isSelected = $selectedReklameObjectId === $obj['id'];
                        $isExpanded = $expandedDetailId === $obj['id'];
                    @endphp
                    <div wire:click="selectObject('{{ $obj['id'] }}')"
                         class="rkl-card bg-white dark:bg-slate-900 border rounded-xl p-3
                                {{ $isSelected ? 'rkl-card-selected border-rose-400 dark:border-rose-500' : 'border-slate-200 dark:border-slate-800' }}">
                        <div class="flex items-center gap-3">
                            <div class="rkl-av">{{ $initial }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-semibold text-slate-900 dark:text-white text-sm truncate max-w-[14rem]">{{ $obj['nama'] }}</span>
                                    <span class="rkl-badge {{ $obj['is_insidentil'] ? 'rkl-badge-insidentil' : 'rkl-badge-tetap' }}">
                                        {{ $obj['is_insidentil'] ? 'Insidentil' : 'Tetap' }}
                                    </span>
                                    @if($obj['kelompok_lokasi'])
                                        <span class="rkl-badge rkl-badge-lokasi">{{ $obj['kelompok_lokasi'] }}</span>
                                    @endif
                                    @if($isSelected)
                                        <span class="rkl-badge ml-auto" style="background:#d1fae5;color:#065f46;">&#10003; Dipilih</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                    {{ $obj['npwpd'] ?? '-' }} &middot; {{ $obj['alamat'] ?? '-' }}
                                </p>
                                @if($isExpanded)
                                <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700 grid grid-cols-2 gap-x-3 gap-y-0.5 text-[11px]">
                                    <div><span class="text-slate-500 dark:text-slate-400">Sub Jenis:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ $obj['sub_jenis'] }}</span></div>
                                    <div><span class="text-slate-500 dark:text-slate-400">Kelompok:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ $obj['kelompok_lokasi'] ?? '-' }}</span></div>
                                    <div><span class="text-slate-500 dark:text-slate-400">Ukuran:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ $obj['ukuran_formatted'] }}</span></div>
                                    <div><span class="text-slate-500 dark:text-slate-400">Muka:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ $obj['jumlah_muka'] }}</span></div>
                                    <div><span class="text-slate-500 dark:text-slate-400">Berlaku s/d:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ $obj['masa_berlaku_sampai'] ?? '-' }}</span></div>
                                    <div><span class="text-slate-500 dark:text-slate-400">Status:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ ucfirst($obj['status'] ?? '-') }}</span></div>
                                </div>
                                @endif
                            </div>
                            <button wire:click.stop="toggleDetail('{{ $obj['id'] }}')"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center border flex-shrink-0 transition-colors
                                           {{ $isExpanded ? 'border-rose-200 bg-rose-50 text-rose-500 dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-400'
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
@endif

{{-- ================================================================== --}}
{{-- SECTION 1B: SEARCH (MODE: ASET PEMKAB)                             --}}
{{-- ================================================================== --}}
@if($mode === 'aset_pemkab' && !$selectedAsetPemkabData && !$skpdResult)
<x-filament::section>
    <x-slot name="heading">1. Cari Aset Reklame Pemkab</x-slot>
    <x-slot name="description">Cari berdasarkan kode aset atau nama titik reklame milik Pemkab.</x-slot>

    <div class="relative mb-4">
        <input type="text"
               wire:model.live.debounce.400ms="searchAsetKeyword"
               autofocus
               placeholder="Kode aset atau nama titik reklame..."
               class="rkl-ring block w-full pl-11 pr-9 py-2.5 border border-slate-200 dark:border-slate-700
                      rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100
                      placeholder-slate-400 dark:placeholder-slate-500 text-sm">
        <span class="absolute inset-y-0 right-0 pr-3 flex items-center" wire:loading wire:target="updatedSearchAsetKeyword">
            <svg class="animate-spin h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        </span>
    </div>

    @if(strlen(trim($searchAsetKeyword ?? '')) >= 2)
        @if(count($searchAsetResults) > 0)
            <div class="flex items-center gap-2 mb-2 px-1">
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                    {{ count($searchAsetResults) }} Hasil
                </span>
            </div>
            <div class="space-y-2 max-h-[28rem] overflow-y-auto pr-0.5">
                @foreach($searchAsetResults as $aset)
                    @php
                        $statusColors = ['tersedia'=>'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                         'disewa'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                         'maintenance'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                         'tidak_aktif'=>'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'];
                    @endphp
                    <div wire:click="selectAsetPemkab('{{ $aset['id'] }}')"
                         class="rkl-card bg-white dark:bg-slate-900 border rounded-xl p-3 border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="rkl-av" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-semibold text-slate-900 dark:text-white text-sm truncate max-w-[14rem]">{{ $aset['nama'] }}</span>
                                    <span class="rkl-badge {{ $statusColors[$aset['status_ketersediaan']] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $aset['status_label'] }}
                                    </span>
                                    <span class="rkl-badge rkl-badge-tetap">{{ strtoupper($aset['jenis']) }}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                    {{ $aset['kode_aset'] }} &middot; {{ $aset['lokasi'] ?? '-' }}
                                    &middot; {{ $aset['ukuran_formatted'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 text-center">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Tidak ditemukan</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Coba kata kunci lain</p>
            </div>
        @endif
    @endif
</x-filament::section>
@endif

{{-- ================================================================== --}}
{{-- SECTION 2B: DATA ASET & PENYEWA (MODE: ASET PEMKAB)                --}}
{{-- ================================================================== --}}
@if($mode === 'aset_pemkab' && $selectedAsetPemkabData && !$skpdResult)
<x-filament::section>
    <x-slot name="heading">2. Data Aset Pemkab &amp; Penyewa</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-800">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                </svg>
            </div>
            <h4 class="font-semibold text-blue-700 dark:text-blue-300 text-base">Aset Reklame Terpilih</h4>
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
        {{-- Aset Info --}}
        <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Aset Pemkab</p>
            <div class="space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $selectedAsetPemkabData['nama'] }}</span>
                    <span class="rkl-badge rkl-badge-tetap">{{ strtoupper($selectedAsetPemkabData['jenis']) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <x-heroicon-o-qr-code class="w-4 h-4 text-slate-400 shrink-0" />
                    <span class="text-sm font-mono text-slate-700 dark:text-slate-300">{{ $selectedAsetPemkabData['kode_aset'] }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <x-heroicon-o-map-pin class="w-4 h-4 text-slate-400 shrink-0" />
                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ str()->limit($selectedAsetPemkabData['lokasi'] ?? '-', 50) }}</span>
                </div>
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Ukuran: <span class="font-medium text-slate-800 dark:text-slate-200">{{ $selectedAsetPemkabData['ukuran_formatted'] }}</span>
                    &middot; Muka: <span class="font-medium text-slate-800 dark:text-slate-200">{{ $selectedAsetPemkabData['jumlah_muka'] }}</span>
                </div>
                @if($selectedAsetPemkabData['harga_sewa_per_minggu'])
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Sewa/minggu: <span class="font-medium text-slate-800 dark:text-slate-200">Rp {{ number_format($selectedAsetPemkabData['harga_sewa_per_minggu'],0,',','.') }}</span>
                </div>
                @endif
                @if($selectedAsetPemkabData['harga_sewa_per_bulan'])
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Sewa/bulan: <span class="font-medium text-slate-800 dark:text-slate-200">Rp {{ number_format($selectedAsetPemkabData['harga_sewa_per_bulan'],0,',','.') }}</span>
                </div>
                @endif
                @if($selectedAsetPemkabData['harga_sewa_per_tahun'])
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Sewa/tahun: <span class="font-medium text-slate-800 dark:text-slate-200">Rp {{ number_format($selectedAsetPemkabData['harga_sewa_per_tahun'],0,',','.') }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Penyewa / Wajib Pajak (NPWPD Search) --}}
        <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Wajib Pajak (Penyewa)</p>

            @if($selectedWpData)
                {{-- Selected WP Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-lg border border-green-200 dark:border-green-800 p-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="rkl-badge" style="background:#d1fae5;color:#065f46;">&#10003; WP Dipilih</span>
                        <button wire:click="deselectWp"
                                class="text-xs text-slate-400 hover:text-rose-500 transition-colors flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Ganti
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-user class="w-4 h-4 text-slate-400 shrink-0" />
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $selectedWpData['nama_lengkap'] }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-identification class="w-4 h-4 text-slate-400 shrink-0" />
                        <span class="text-sm font-mono text-slate-700 dark:text-slate-300">{{ $selectedWpData['nik'] }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-document-text class="w-4 h-4 text-slate-400 shrink-0" />
                        <span class="text-sm font-mono text-slate-700 dark:text-slate-300">{{ $selectedWpData['npwpd'] ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-map-pin class="w-4 h-4 text-slate-400 shrink-0" />
                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ str()->limit($selectedWpData['alamat'] ?? '-', 50) }}</span>
                    </div>
                </div>
            @else
                {{-- NPWPD Search Input --}}
                <div class="relative mb-3">
                    <input type="text"
                           wire:model.live.debounce.400ms="searchNpwpdKeyword"
                           placeholder="Cari NIK, NPWPD, atau nama WP..."
                           class="rkl-ring block w-full pl-9 pr-9 py-2.5 border border-slate-200 dark:border-slate-700
                                  rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100
                                  placeholder-slate-400 dark:placeholder-slate-500 text-sm">
                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center" wire:loading wire:target="updatedSearchNpwpdKeyword">
                        <svg class="animate-spin h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </span>
                </div>

                {{-- Search Results --}}
                @if(strlen(trim($searchNpwpdKeyword ?? '')) >= 3)
                    @if(count($searchNpwpdResults) > 0)
                        <div class="space-y-1.5 max-h-[16rem] overflow-y-auto mt-2">
                            @foreach($searchNpwpdResults as $wp)
                                <div wire:click="selectWp('{{ $wp['id'] }}')"
                                     class="rkl-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-3 cursor-pointer">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $wp['nama_lengkap'] }}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                        {{ $wp['npwpd'] ?? '-' }} &middot; {{ $wp['nik'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-500 dark:text-slate-400 text-center py-3">Wajib pajak tidak ditemukan.</p>
                    @endif
                @endif
            @endif
        </div>
    </div>
</x-filament::section>
@endif

{{-- ================================================================== --}}
{{-- SECTION 2: DATA WP & OBJEK TERPILIH (MODE: OBJEK WP)               --}}
{{-- ================================================================== --}}
@if($mode === 'objek_wp' && $selectedReklameObjectData && !$skpdResult)
<x-filament::section>
    <x-slot name="heading">2. Data Wajib Pajak &amp; Objek Reklame</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-green-100 dark:bg-green-800">
                <x-heroicon-s-check-circle class="w-5 h-5 text-green-600 dark:text-green-400" />
            </div>
            <h4 class="font-semibold text-green-700 dark:text-green-300 text-base">Objek Reklame Dipilih</h4>
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
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Objek Reklame</p>
            <div class="space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $selectedReklameObjectData['nama'] }}</span>
                    <span class="rkl-badge {{ $selectedReklameObjectData['is_insidentil'] ? 'rkl-badge-insidentil' : 'rkl-badge-tetap' }}">
                        {{ $selectedReklameObjectData['is_insidentil'] ? 'Insidentil' : 'Tetap' }}
                    </span>
                    @if($selectedReklameObjectData['kelompok_lokasi'])
                        <span class="rkl-badge rkl-badge-lokasi">Kel. {{ $selectedReklameObjectData['kelompok_lokasi'] }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <x-heroicon-o-map-pin class="w-4 h-4 text-slate-400 shrink-0" />
                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ str()->limit($selectedReklameObjectData['alamat'] ?? '-', 50) }}</span>
                </div>
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Ukuran: <span class="font-medium text-slate-800 dark:text-slate-200">{{ $selectedReklameObjectData['ukuran_formatted'] }}</span>
                    &middot; Muka: <span class="font-medium text-slate-800 dark:text-slate-200">{{ $selectedReklameObjectData['jumlah_muka'] }}</span>
                </div>
            </div>
        </div>
    </div>
</x-filament::section>
@endif

{{-- ================================================================== --}}
{{-- SECTION 3: PERHITUNGAN SKPD REKLAME                                --}}
{{-- ================================================================== --}}
@if(!$skpdResult && (($mode === 'objek_wp' && $selectedReklameObjectData) || ($mode === 'aset_pemkab' && $selectedAsetPemkabData)))
<x-filament::section>
    <x-slot name="heading">3. Perhitungan SKPD Reklame</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- LEFT: Form Input --}}
        <div class="space-y-4">
            @if($mode === 'objek_wp')
            {{-- Sub Jenis Reklame (Objek WP only) --}}
            <div>
                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-500 mb-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Sub Jenis Reklame
                </label>
                <input type="text" readonly
                       value="{{ $selectedReklameObjectData['sub_jenis'] ?? '-' }}"
                       class="block w-full py-2 px-3 border border-dashed border-slate-200 dark:border-slate-700
                              rounded-lg bg-slate-50 dark:bg-slate-800/50 text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
            </div>

            @php $hargaPatokanOptions = $this->getHargaPatokanReklameOptions(); @endphp
            <div>
                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Detail Jenis Reklame
                </label>
                <select wire:model.live="hargaPatokanReklameId"
                    class="rkl-ring block w-full py-2 px-3 border border-slate-200 dark:border-slate-700
                           rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                    <option value="">-- Pilih detail reklame --</option>
                    @foreach($hargaPatokanOptions as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                @if(empty($hargaPatokanOptions))
                    <p class="text-[10px] text-amber-600 dark:text-amber-400 mt-1">Belum ada harga patokan reklame aktif untuk sub jenis ini.</p>
                @endif
            </div>

            @php $lokasiJalanOptions = $this->getLokasiJalanOptions(); @endphp
            <div>
                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Lokasi / Jalan Penempatan Master
                </label>
                <select wire:model.live="lokasiJalanId"
                    class="rkl-ring block w-full py-2 px-3 border border-slate-200 dark:border-slate-700
                           rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                    <option value="">-- Pilih jalan sesuai masa berlaku --</option>
                    @foreach($lokasiJalanOptions as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">
                    Daftar jalan mengikuti master aktif pada tanggal mulai masa berlaku. Histori SKPD lama tidak terpengaruh.
                </p>
            </div>
            @endif

            {{-- Satuan Waktu --}}
            @php $satuanOptions = $mode === 'aset_pemkab' ? $this->getAvailableSatuanWaktuAset() : $this->getAvailableSatuanWaktu(); @endphp
            <div>
                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Satuan Waktu
                </label>
                <select wire:model.live="satuanWaktu"
                    class="rkl-ring block w-full py-2 px-3 border border-slate-200 dark:border-slate-700
                           rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                    <option value="">-- Pilih --</option>
                    @foreach($satuanOptions as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                @if($mode === 'objek_wp' && $hargaPatokanReklameId && empty($satuanOptions))
                    <p class="text-[10px] text-amber-600 dark:text-amber-400 mt-1">Tidak ada tarif yang tersedia untuk jenis ini.</p>
                @endif
            </div>

            {{-- Durasi --}}
            <div>
                @if($mode === 'aset_pemkab')
                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Durasi
                </label>
                <input type="number" wire:model.live="durasi" min="1"
                       class="rkl-ring block w-full py-2 px-3 border border-slate-200 dark:border-slate-700
                              rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                @else
                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-500 mb-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Durasi
                </label>
                <input type="number" wire:model.live="durasi" value="1" readonly
                       class="block w-full py-2 px-3 border border-dashed border-slate-200 dark:border-slate-700
                              rounded-lg bg-slate-50 dark:bg-slate-800/50 text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
                @endif
            </div>

            @if($mode === 'objek_wp')
            {{-- Jumlah Reklame (Objek WP only) --}}
            <div>
                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Jumlah Reklame
                </label>
                <input type="number" wire:model.live="jumlahReklame" min="1"
                       class="rkl-ring block w-full py-2 px-3 border border-slate-200 dark:border-slate-700
                              rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
            </div>

            {{-- Luas + Jumlah Muka (readonly dari objek) --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-500 mb-1.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Luas (m²)
                    </label>
                    <input type="text" readonly
                           value="{{ $luasM2 }}"
                           class="block w-full py-2 px-3 border border-dashed border-slate-200 dark:border-slate-700
                                  rounded-lg bg-slate-50 dark:bg-slate-800/50 text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">
                        @if($selectedReklameObjectData)
                            Dihitung otomatis dari dimensi objek ({{ ucfirst($selectedReklameObjectData['bentuk'] ?? 'persegi') }}: {{ $selectedReklameObjectData['ukuran_formatted'] }})
                        @endif
                    </p>
                </div>
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-500 mb-1.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Jumlah Muka
                    </label>
                    <input type="text" readonly
                           value="{{ $jumlahMuka }}"
                           class="block w-full py-2 px-3 border border-dashed border-slate-200 dark:border-slate-700
                                  rounded-lg bg-slate-50 dark:bg-slate-800/50 text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
                </div>
            </div>

            {{-- Kelompok Lokasi (derived from effective-dated road master) --}}
            <div>
                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-500 mb-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Kelompok Lokasi
                </label>
                @php
                    $kelompokLabels = ['A'=>'Jalan Utama/Protokol','A1'=>'Jalan Sekunder Utama','A2'=>'Jalan Sekunder','A3'=>'Jalan Lokal Utama','B'=>'Jalan Lokal','C'=>'Jalan Lingkungan'];
                    $kl = (string) $kelompokLokasi;
                    $kelompokDisplay = $kl !== '' ? ($kl . ' — ' . ($kelompokLabels[$kl] ?? '')) : 'Tidak ada';
                @endphp
                <input type="text" readonly
                       value="{{ $kelompokDisplay }}"
                       class="block w-full py-2 px-3 border border-dashed border-slate-200 dark:border-slate-700
                              rounded-lg bg-slate-50 dark:bg-slate-800/50 text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
                @if(!empty($selectedReklameObjectData['lokasi_jalan_label']))
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">
                        Objek terdaftar pada lokasi: {{ $selectedReklameObjectData['lokasi_jalan_label'] }}
                    </p>
                @endif
            </div>

            {{-- Lokasi Penempatan --}}
            <div>
                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Lokasi Penempatan
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model.live="lokasiPenempatan" value="luar_ruangan"
                               class="text-rose-600 focus:ring-rose-500">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Luar Ruangan</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model.live="lokasiPenempatan" value="dalam_ruangan"
                               class="text-rose-600 focus:ring-rose-500">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Dalam Ruangan <span class="text-[10px] text-slate-400">(×0.25)</span></span>
                    </label>
                </div>
            </div>

            {{-- Jenis Produk --}}
            <div>
                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Jenis Produk
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model.live="jenisProduk" value="non_rokok"
                               class="text-rose-600 focus:ring-rose-500">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Non-Rokok</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model.live="jenisProduk" value="rokok"
                               class="text-rose-600 focus:ring-rose-500">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Rokok <span class="text-[10px] text-slate-400">(×1.10)</span></span>
                    </label>
                </div>
            </div>

            <div>
                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-500"></span>
                    Isi Materi Reklame
                    <span class="text-[10px] font-medium text-slate-400">Opsional</span>
                </label>
                <input type="text" wire:model.live.debounce.400ms="isiMateriReklame"
                       placeholder="Contoh: Promo Grand Opening Toko ABC"
                       class="rkl-ring block w-full py-2 px-3 border border-slate-200 dark:border-slate-700
                              rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">
                    Jika dikosongkan, sistem memakai nama objek reklame yang dipilih.
                </p>
            </div>
            @endif

            {{-- Masa Berlaku --}}
            <div class="grid grid-cols-2 gap-3">
                <div x-data="{
                        value: @entangle('masaBerlakuMulai'),
                        get formatted() {
                            if (!this.value) return '';
                            const d = new Date(this.value + 'T00:00:00');
                            return String(d.getDate()).padStart(2,'0') + '/' + String(d.getMonth()+1).padStart(2,'0') + '/' + d.getFullYear();
                        },
                        parseInput(val) {
                            const m = val.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
                            if (m) {
                                const iso = m[3] + '-' + m[2].padStart(2,'0') + '-' + m[1].padStart(2,'0');
                                if (!isNaN(Date.parse(iso))) { this.value = iso; }
                            }
                        },
                        init() {
                            this.$watch('value', (val) => {
                                $wire.set('masaBerlakuMulai', val).then(() => {
                                    $wire.hitungMasaBerlakuSampai();
                                });
                            });
                        }
                     }">
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        Masa Berlaku Mulai
                    </label>
                    <input type="text" x-bind:value="formatted"
                           @change="parseInput($event.target.value)"
                           placeholder="dd/mm/yyyy"
                           class="rkl-ring block w-full py-2 px-3 border border-slate-200 dark:border-slate-700
                                  rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-500 mb-1.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Masa Berlaku Sampai
                    </label>
                    <input type="text" readonly
                           value="{{ $masaBerlakuSampai ? \Carbon\Carbon::parse($masaBerlakuSampai)->format('d/m/Y') : '-' }}"
                           class="block w-full py-2 px-3 border border-dashed border-slate-200 dark:border-slate-700
                                  rounded-lg bg-slate-50 dark:bg-slate-800/50 text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
                    @if($masaBerlakuSampai)
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Otomatis dari satuan waktu &times; durasi</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT: Preview Perhitungan --}}
        <div class="lg:self-start">
            <div class="lg:sticky lg:top-4 space-y-10">
            @if($mode === 'aset_pemkab')
                @php $previewAset = $this->getPreviewPajakAset(); @endphp
                @if($previewAset)
                <div class="rounded-xl rkl-bg-p5 border rkl-border-p20 p-3 sm:p-5">
                    <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Preview Perhitungan (Sewa Aset)</h4>

                    <table class="w-full text-xs sm:text-sm">
                        <tbody>
                            <tr class="border-b border-slate-200/50 dark:border-slate-700/50">
                                <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400">Harga Sewa</td>
                                <td class="py-2.5 px-3 text-right font-semibold text-slate-800 dark:text-slate-200 tabular-nums">Rp {{ number_format($previewAset['harga_sewa'],0,',','.') }} / {{ $previewAset['satuan_label'] }}</td>
                            </tr>
                            <tr class="border-b border-slate-200/50 dark:border-slate-700/50">
                                <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400">Durasi</td>
                                <td class="py-2.5 px-3 text-right font-semibold text-slate-800 dark:text-slate-200 tabular-nums">{{ $previewAset['durasi'] }} &times; {{ $previewAset['satuan_label'] }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 rkl-border-p20">
                                <td class="pt-3 pb-1 px-3 font-bold text-slate-900 dark:text-white">Total Pajak</td>
                                <td class="pt-3 pb-1 px-3 text-right text-lg sm:text-xl font-black rkl-text-primary tabular-nums">Rp {{ number_format($previewAset['jumlah_pajak'],0,',','.') }}</td>
                            </tr>
                            @if($previewAset['jatuh_tempo'])
                            <tr>
                                <td class="py-1 px-3 text-slate-500 dark:text-slate-400">Jatuh Tempo</td>
                                <td class="py-1 px-3 text-right font-medium text-slate-800 dark:text-slate-200">{{ $previewAset['jatuh_tempo'] }}</td>
                            </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
                @else
                    <p class="text-sm text-slate-400 italic">Pilih aset dan durasi untuk melihat preview.</p>
                @endif
            @else
            @php $preview = $this->getPreviewPajak(); @endphp

            @if($preview)
            <div class="rounded-xl rkl-bg-p5 border rkl-border-p20 p-3 sm:p-5">
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Preview Perhitungan</h4>

                <table class="w-full text-xs sm:text-sm">
                    <tbody>
                        <tr class="border-b border-slate-200/50 dark:border-slate-700/50">
                            <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400">Tarif Pokok</td>
                            <td class="py-2.5 px-3 text-right font-semibold text-slate-800 dark:text-slate-200 tabular-nums">Rp {{ number_format($preview['tarif_pokok'],0,',','.') }}/m²</td>
                        </tr>
                        <tr class="border-b border-slate-200/50 dark:border-slate-700/50">
                            <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400">Pokok Pajak Dasar</td>
                            <td class="py-2.5 px-3 text-right font-semibold text-slate-800 dark:text-slate-200 tabular-nums">Rp {{ number_format($preview['pokok_pajak_dasar'],0,',','.') }}</td>
                        </tr>
                        <tr class="border-b border-slate-200/50 dark:border-slate-700/50">
                            <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400">Penyesuaian Lokasi</td>
                            <td class="py-2.5 px-3 text-right">
                                <span class="rkl-bg-p20 rkl-text-primary text-xs font-bold px-2 py-0.5 rounded">&times;{{ number_format($preview['penyesuaian_lokasi'],2) }}</span>
                            </td>
                        </tr>
                        <tr class="border-b border-slate-200/50 dark:border-slate-700/50">
                            <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400">Penyesuaian Produk</td>
                            <td class="py-2.5 px-3 text-right">
                                <span class="rkl-bg-p20 rkl-text-primary text-xs font-bold px-2 py-0.5 rounded">&times;{{ number_format($preview['penyesuaian_produk'],2) }}</span>
                            </td>
                        </tr>
                        <tr class="border-b border-slate-200/50 dark:border-slate-700/50">
                            <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400">Dasar Pengenaan</td>
                            <td class="py-2.5 px-3 text-right font-semibold text-slate-800 dark:text-slate-200 tabular-nums">Rp {{ number_format($preview['dasar_pengenaan'],0,',','.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400">Nilai Strategis</td>
                            <td class="py-2.5 px-3 text-right">
                                @if($preview['is_insidentil'])
                                    <span class="text-xs text-slate-400 italic">Tidak berlaku</span>
                                @else
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 tabular-nums">Rp {{ number_format($preview['nilai_strategis'],0,',','.') }}</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 rkl-border-p20">
                            <td class="pt-3 pb-1 px-3 font-bold text-slate-900 dark:text-white">Total Pajak</td>
                            <td class="pt-3 pb-1 px-3 text-right text-lg sm:text-xl font-black rkl-text-primary tabular-nums">Rp {{ number_format($preview['jumlah_pajak'],0,',','.') }}</td>
                        </tr>
                        @if($preview['jatuh_tempo'])
                        <tr>
                            <td class="py-1 px-3 text-slate-500 dark:text-slate-400">Jatuh Tempo</td>
                            <td class="py-1 px-3 text-right font-medium text-slate-800 dark:text-slate-200">{{ $preview['jatuh_tempo'] }}</td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
            @else
            <div class="flex flex-col items-center justify-center h-full min-h-[16rem]
                        bg-slate-50 dark:bg-slate-800 rounded-xl border border-dashed
                        border-slate-200 dark:border-slate-700 p-4 sm:p-8 text-center">
                <div class="w-12 h-12 rounded-2xl rkl-bg-p10 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 rkl-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01
                                 M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">Preview Perhitungan</p>
                <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">Lengkapi form untuk melihat preview pajak.</p>
            </div>
            @endif
            @endif

            {{-- Submit Button --}}
            <div style="margin-top: 1.75rem;">
                @php
                    $submitMethod = $mode === 'aset_pemkab' ? 'buatSkpdAsetPemkab' : 'buatSkpd';
                    $canSubmit = $mode === 'aset_pemkab' ? ($previewAset ?? null) : ($preview ?? null);
                @endphp
                <button wire:click="{{ $submitMethod }}"
                        wire:loading.attr="disabled"
                        @if(!$canSubmit) disabled @endif
                        class="w-full rkl-bg-primary rkl-bg-ph rkl-shadow disabled:opacity-40 disabled:cursor-not-allowed
                               text-white font-bold py-3.5 rounded-xl transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                    <svg wire:loading.remove wire:target="{{ $submitMethod }}" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg wire:loading wire:target="{{ $submitMethod }}" class="animate-spin h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span wire:loading.remove wire:target="{{ $submitMethod }}">Buat Draft SKPD</span>
                    <span wire:loading wire:target="{{ $submitMethod }}">Memproses...</span>
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
<div class="rkl-pop space-y-4">
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
        <div class="relative overflow-hidden px-6 py-5 text-center" style="background-color: #0f172a">
            <div class="rkl-shimmer absolute inset-0 pointer-events-none"></div>
            <div class="relative z-10">
                <p class="text-[10px] font-bold uppercase tracking-[.2em] mb-1.5" style="color: #94a3b8">Nomor SKPD</p>
                <p class="text-xl font-mono font-extrabold tracking-wide" style="color: #ffffff">{{ $skpdResult['nomor_skpd'] }}</p>
            </div>
        </div>
        <div class="p-4 space-y-4">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                    <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Wajib Pajak</p>
                    <p class="font-semibold text-slate-900 dark:text-white mt-0.5 truncate">{{ $skpdResult['nama_wp'] }}</p>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                    <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Reklame</p>
                    <p class="font-semibold text-slate-900 dark:text-white mt-0.5 truncate">{{ $skpdResult['nama_reklame'] }}</p>
                </div>
            </div>
            @if(!empty($skpdResult['isi_materi_reklame']))
            <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-sm">
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Isi Materi Reklame</p>
                <p class="font-semibold text-slate-900 dark:text-white mt-0.5">{{ $skpdResult['isi_materi_reklame'] }}</p>
            </div>
            @endif
            <div class="space-y-1.5">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Total Pajak</span>
                    <span class="text-xl font-black rkl-text-primary tabular-nums">Rp {{ number_format($skpdResult['jumlah_pajak'],0,',','.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Jatuh Tempo</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $skpdResult['jatuh_tempo'] }}</span>
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
                class="flex-1 rkl-bg-primary rkl-bg-ph rkl-shadow text-white font-bold
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
</x-filament-panels::page>
