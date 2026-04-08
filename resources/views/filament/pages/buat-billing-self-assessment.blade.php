<x-filament-panels::page>

<style>
    :root {
        --sa-primary: #135bec; --sa-primary-h: #1050d0;
        --sa-primary-5: rgba(19,91,236,.05);  --sa-primary-10: rgba(19,91,236,.10);
        --sa-primary-20: rgba(19,91,236,.20); --sa-primary-30: rgba(19,91,236,.30);
        --sa-shadow: rgba(19,91,236,.20);
    }
    .dark {
        --sa-primary: #3b82f6; --sa-primary-h: #2563eb;
        --sa-primary-5: rgba(59,130,246,.10);  --sa-primary-10: rgba(59,130,246,.18);
        --sa-primary-20: rgba(59,130,246,.28); --sa-primary-30: rgba(59,130,246,.38);
        --sa-shadow: rgba(59,130,246,.30);
    }
    .sa-text-primary  { color: var(--sa-primary); }
    .sa-bg-primary    { background-color: var(--sa-primary); }
    .sa-bg-ph:hover   { background-color: var(--sa-primary-h); }
    .sa-bg-p5         { background-color: var(--sa-primary-5); }
    .sa-bg-p10        { background-color: var(--sa-primary-10); }
    .sa-bg-p20        { background-color: var(--sa-primary-20); }
    .sa-border-p10    { border-color: var(--sa-primary-10); }
    .sa-border-p20    { border-color: var(--sa-primary-20); }
    .sa-ring:focus    { --tw-ring-color: var(--sa-primary); border-color: var(--sa-primary); }
    .sa-shadow        { box-shadow: 0 4px 12px -1px var(--sa-shadow); }
    .sa-tag { padding:.2rem .6rem; border-radius:9999px; font-size:.68rem; font-weight:600; cursor:pointer;
              background:#dbeafe; color:#1d4ed8; border:1px solid #bfdbfe; transition:all .15s; }
    .sa-tag:hover { background:#bfdbfe; }
    .dark .sa-tag { background:rgba(59,130,246,.12); color:#93c5fd; border-color:rgba(59,130,246,.25); }
    .dark .sa-tag:hover { background:rgba(59,130,246,.22); }
    .sa-av { width:2.1rem; height:2.1rem; border-radius:.45rem; display:flex; align-items:center;
             justify-content:center; font-weight:700; font-size:.8rem; color:#fff; flex-shrink:0; }
    .sa-av-blue    { background:linear-gradient(135deg,#3b82f6,#2563eb); }
    .sa-av-emerald { background:linear-gradient(135deg,#10b981,#059669); }
    .sa-av-amber   { background:linear-gradient(135deg,#f59e0b,#d97706); }
    .sa-av-purple  { background:linear-gradient(135deg,#a855f7,#7c3aed); }
    .sa-av-sky     { background:linear-gradient(135deg,#0ea5e9,#0284c7); }
    .sa-av-cyan    { background:linear-gradient(135deg,#06b6d4,#0891b2); }
    .sa-av-rose    { background:linear-gradient(135deg,#f43f5e,#e11d48); }
    .sa-av-gray    { background:linear-gradient(135deg,#64748b,#475569); }
    .sa-badge { padding:.1rem .4rem; border-radius:.25rem; font-size:.58rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
    .sa-bg-blue    { background:#dbeafe; color:#1e40af; } .dark .sa-bg-blue    { background:rgba(59,130,246,.15); color:#93c5fd; }
    .sa-bg-emerald { background:#d1fae5; color:#065f46; } .dark .sa-bg-emerald { background:rgba(16,185,129,.15); color:#6ee7b7; }
    .sa-bg-amber   { background:#fef3c7; color:#92400e; } .dark .sa-bg-amber   { background:rgba(245,158,11,.15); color:#fcd34d; }
    .sa-bg-purple  { background:#ede9fe; color:#5b21b6; } .dark .sa-bg-purple  { background:rgba(168,85,247,.15); color:#c4b5fd; }
    .sa-bg-sky     { background:#e0f2fe; color:#075985; } .dark .sa-bg-sky     { background:rgba(14,165,233,.15); color:#7dd3fc; }
    .sa-bg-cyan    { background:#cffafe; color:#155e75; } .dark .sa-bg-cyan    { background:rgba(6,182,212,.15); color:#67e8f9; }
    .sa-bg-rose    { background:#ffe4e6; color:#9f1239; } .dark .sa-bg-rose    { background:rgba(244,63,94,.15); color:#fda4af; }
    .sa-bg-gray    { background:#f1f5f9; color:#475569; } .dark .sa-bg-gray    { background:rgba(100,116,139,.15); color:#cbd5e1; }
    .sa-card { transition:all .18s; cursor:pointer; position:relative; overflow:hidden; }
    .sa-card::before { content:""; position:absolute; left:0; top:0; bottom:0; width:3px;
                       background:transparent; transition:background .18s; border-radius:0 3px 3px 0; }
    .sa-card:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(19,91,236,.09); border-color:#93c5fd !important; }
    .sa-card:hover::before { background:var(--sa-primary); }
    .dark .sa-card:hover { border-color:#3b82f6 !important; box-shadow:0 4px 14px rgba(59,130,246,.18); }
    .sa-card-selected { border-color:var(--sa-primary) !important; background:var(--sa-primary-5) !important; }
    .sa-card-selected::before { background:var(--sa-primary); }
    @keyframes sa-pop { 0%{transform:scale(.85);opacity:0} 100%{transform:scale(1);opacity:1} }
    .sa-pop { animation:sa-pop .35s cubic-bezier(.175,.885,.32,1.275); }
    @keyframes sa-shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
    .sa-shimmer { background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,.15) 50%,transparent 100%);
                  background-size:200% 100%; animation:sa-shimmer 2.2s infinite; }
    /* Dark-mode surface overrides – Filament build may tree-shake unused dark:bg-* variants */
    .dark .sa-page .bg-white { background-color: #0f172a; }
    .dark .sa-page select,
    .dark .sa-page input[type="text"] { background-color: #1e293b; }
    .dark .sa-page .bg-slate-50 { background-color: #1e293b; }
    .dark .sa-page .bg-slate-900 { background-color: #1e293b; }
    .dark .sa-page .border-slate-200 { border-color: #334155; }
    .dark .sa-page .border-slate-100 { border-color: #1e293b; }
    .dark .sa-page .hover\:bg-slate-50:hover { background-color: #1e293b; }
    .dark .sa-page .hover\:bg-slate-100:hover { background-color: #1e293b; }
    .dark .sa-page .bg-amber-50 { background-color: rgba(217,119,6,.12); }
    .dark .sa-page .bg-amber-100 { background-color: rgba(217,119,6,.2); }
    .dark .sa-page .bg-orange-100 { background-color: rgba(234,88,12,.2); }
    .dark .sa-page .bg-blue-50 { background-color: rgba(59,130,246,.15); }
</style>

<div class="sa-page py-2">

{{-- Page header --}}
<div class="mb-5 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Buat Billing Self-Assessment</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Cari objek pajak, isi data pelaporan, terbitkan Kode Pembayaran Aktif.</p>
    </div>
    @if($selectedTaxObjectId && !$billingResult)
    <button wire:click="buatBaru"
            class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200
                   flex items-center gap-1.5 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Batal pilih
    </button>
    @endif
</div>

{{-- Two-column layout --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">

    {{-- LEFT: Search panel (2/5) --}}
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
            <div class="relative">
                <input type="text"
                       wire:model.live.debounce.400ms="searchKeyword"
                       autofocus
                       placeholder="NIK, NPWPD, atau nama objek pajak..."
                       class="p-8 sa-ring block w-full pl-12 pr-9 py-2.5 border border-slate-200 dark:border-slate-700
                              rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100
                              placeholder-slate-400 dark:placeholder-slate-500 text-sm">
                <span class="absolute inset-y-0 right-0 pr-3 flex items-center" wire:loading wire:target="updatedSearchKeyword">
                    <svg class="animate-spin h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </span>
            </div>


        </div>

        @if(strlen(trim($searchKeyword ?? '')) >= 3)
            @if(count($searchResults) > 0)
            <div>
                <div class="flex items-center gap-2 mb-2 px-1">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                        {{ count($searchResults) }} Hasil
                    </span>
                </div>
                <div class="space-y-2 max-h-[calc(100vh-22rem)] overflow-y-auto pr-0.5">
                    @foreach($searchResults as $obj)
                        @php
                            $color = \App\Filament\Pages\BuatBillingSelfAssessment::getBadgeColor($obj['jenis_pajak_nama']);
                            $initial = strtoupper(substr($obj['nama'] ?? '?', 0, 1));
                            $isSelected = $selectedTaxObjectId === $obj['id'];
                            $isExpanded = $expandedDetailId === $obj['id'];
                        @endphp
                        <div wire:click="selectObject('{{ $obj['id'] }}')"
                             class="sa-card bg-white dark:bg-slate-900 border rounded-xl p-3
                                    {{ $isSelected ? 'sa-card-selected border-blue-400 dark:border-blue-500' : 'border-slate-200 dark:border-slate-800' }}">
                            <div class="flex items-center gap-3">
                                <div class="sa-av sa-av-{{ $color }}">{{ $initial }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-semibold text-slate-900 dark:text-white text-sm truncate max-w-[10rem]">{{ $obj['nama'] }}</span>
                                        <span class="sa-badge sa-bg-{{ $color }}">{{ $obj['jenis_pajak_nama'] }}</span>
                                        @if($isSelected)
                                            <span class="sa-badge sa-bg-blue ml-auto">&#10003; Dipilih</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                        {{ $obj['npwpd'] ?? '-' }} &middot; {{ $obj['alamat'] ?? '-' }}
                                    </p>
                                    @if($isExpanded)
                                    <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700 grid grid-cols-2 gap-x-3 gap-y-0.5 text-[11px]">
                                        <div><span class="text-slate-500 dark:text-slate-400">Sub Jenis:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ $obj['sub_jenis'] }}</span></div>
                                        <div><span class="text-slate-500 dark:text-slate-400">Tarif:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ $obj['tarif_persen'] }}%</span></div>
                                        <div><span class="text-slate-500 dark:text-slate-400">NOPD:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ $obj['nopd'] ?? '-' }}</span></div>
                                        <div><span class="text-slate-500 dark:text-slate-400">Masa berikut:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ $obj['next_label'] }}</span></div>
                                    </div>
                                    @endif
                                </div>
                                <button wire:click.stop="toggleDetail('{{ $obj['id'] }}')"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center border flex-shrink-0 transition-colors
                                               {{ $isExpanded ? 'border-blue-200 bg-blue-50 text-blue-500 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
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
    </div>

    {{-- RIGHT: Panel (3/5) --}}
    <div class="lg:col-span-3">

        {{-- SUCCESS --}}
        @if($billingResult)
        <div class="sa-pop space-y-4">
            <div class="text-center py-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600
                            flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/25">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white mt-3">
                    {{ $billingResult['is_tambahan'] ? 'Billing Tambahan Berhasil!' : 'Billing Berhasil Dibuat!' }}
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Kode Pembayaran Aktif siap digunakan untuk pembayaran.</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="relative overflow-hidden bg-slate-900 dark:bg-slate-800 px-6 py-5 text-center">
                    <div class="sa-shimmer absolute inset-0 pointer-events-none"></div>
                    <div class="relative z-10">
                        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[.2em] mb-1.5">Kode Pembayaran Aktif</p>
                        <p class="text-white text-3xl font-mono font-extrabold tracking-[.15em]">{{ $billingResult['billing_code'] }}</p>
                    </div>
                </div>
                <div class="p-2 space-y-4">
                    <div class="grid grid-cols-2 gap-1.5 text-sm">
                        <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Wajib Pajak</p>
                            <p class="font-semibold text-slate-900 dark:text-white mt-0.5 truncate">{{ $billingResult['nama_wp'] }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Objek Pajak</p>
                            <p class="font-semibold text-slate-900 dark:text-white mt-0.5 truncate">{{ $billingResult['nama_objek'] }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Masa Pajak</p>
                            <p class="font-semibold text-slate-900 dark:text-white mt-0.5">{{ $billingResult['masa_pajak'] }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jatuh Tempo</p>
                            <p class="font-semibold text-slate-900 dark:text-white mt-0.5">{{ $billingResult['expired_at'] }}</p>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">{{ ($billingResult['is_ppj'] ?? false) ? 'DPP' : 'Omzet' }}</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200 tabular-nums">Rp {{ number_format($billingResult['omzet'],0,',','.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Tarif {{ $billingResult['jenis_pajak'] }}</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $billingResult['tarif'] }}%</span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-slate-100 dark:border-slate-800">
                            <span class="font-bold text-slate-900 dark:text-white">Total Pajak</span>
                            <span class="text-xl font-black sa-text-primary tabular-nums">Rp {{ number_format($billingResult['amount'],0,',','.') }}</span>
                        </div>
                    </div>
                    @if($billingResult['is_tambahan'])
                    <div class="bg-amber-50 dark:bg-amber-900/15 border border-amber-200 dark:border-amber-800/50 rounded-lg px-3 py-2 text-xs text-amber-700 dark:text-amber-300 font-medium">
                        Pembetulan ke-{{ $billingResult['pembetulan_ke'] }}
                    </div>
                    @endif
                </div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('portal.billing.document.show', $billingResult['tax_id']) }}" target="_blank"
                   title="{{ $billingResult['is_tambahan'] ? 'Cetak billing pembetulan' : 'Cetak billing' }}"
                   class="flex-1 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-bold
                          py-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50
                          dark:hover:bg-slate-800 transition-all flex items-center justify-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    {{ $billingResult['is_tambahan'] ? 'Cetak Billing Pembetulan' : 'Cetak Billing' }}
                </a>
                <button wire:click="buatBaru"
                        class="flex-1 sa-bg-primary sa-bg-ph sa-shadow text-white font-bold
                               py-3 rounded-xl transition-all flex items-center justify-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Billing Baru
                </button>
            </div>
        </div>

        {{-- FORM --}}
        @elseif($selectedTaxObjectId && $selectedTaxObjectData)
        @php
            $tarif = $selectedTaxObjectData['tarif_persen'];
            $color = \App\Filament\Pages\BuatBillingSelfAssessment::getBadgeColor($selectedTaxObjectData['jenis_pajak_nama']);
            $isPpjPln    = ($selectedTaxObjectData['sub_jenis_kode'] ?? null) === 'PPJ_SUMBER_LAIN';
            $isPpjNonPln = ($selectedTaxObjectData['sub_jenis_kode'] ?? null) === 'PPJ_DIHASILKAN_SENDIRI';
            $isPpj       = $isPpjPln || $isPpjNonPln;
        @endphp
        <div class="space-y-4"
             x-data="{
                rawValue: @entangle('omzet').live,
                displayValue: '',
                ppjPokokPajak: @entangle('ppjPokokPajak').live,
                ppjPokokDisplay: '',
                ppjKapasitasKva: @entangle('ppjKapasitasKva').live,
                ppjTingkatPersen: @entangle('ppjTingkatPenggunaanPersen').live,
                ppjJangkaWaktuJam: @entangle('ppjJangkaWaktuJam').live,
                ppjHargaSatuan: @entangle('ppjHargaSatuan').live,
                init() {
                    if (this.rawValue) this.displayValue = this.fmt(this.rawValue);
                    if (this.ppjPokokPajak) this.ppjPokokDisplay = this.fmt(this.ppjPokokPajak);
                },
                fmt(n)   { return n ? Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.') : ''; },
                fmtRp(n) { return 'Rp ' + (n ? Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.') : '0'); },
                onInput(e) {
                    let v = e.target.value.replace(/\./g,'');
                    if (!v || isNaN(v)) { this.rawValue = null; this.displayValue = ''; return; }
                    this.rawValue = parseInt(v);
                    this.displayValue = this.fmt(v);
                },
                onPpjPokokInput(e) {
                    let v = e.target.value.replace(/\./g,'');
                    if (!v || isNaN(v)) { this.ppjPokokPajak = null; this.ppjPokokDisplay = ''; return; }
                    this.ppjPokokPajak = parseInt(v);
                    this.ppjPokokDisplay = this.fmt(v);
                },
                get tax() { return Math.round((this.rawValue || 0) * {{ $tarif }} / 100); },
                get njtl() { return Math.round((this.ppjKapasitasKva||0) * ((this.ppjTingkatPersen||0)/100) * (this.ppjJangkaWaktuJam||0) * (this.ppjHargaSatuan||0)); },
                get ppjNonPlnTax() { return Math.round(this.njtl * {{ $tarif }} / 100); },
                get ppjPlnDpp() { return {{ $tarif }} > 0 ? Math.round((this.ppjPokokPajak||0) / ({{ $tarif }}/100)) : 0; },
                get canSubmit() {
                    @if($isPpjPln) return (this.ppjPokokPajak||0) > 0;
                    @elseif($isPpjNonPln) return (this.ppjKapasitasKva||0) > 0 && (this.ppjTingkatPersen||0) > 0 && (this.ppjJangkaWaktuJam||0) > 0 && (this.ppjHargaSatuan||0) > 0;
                    @else return (this.rawValue||0) > 0;
                    @endif
                }
             }">

            {{-- Selected object card --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="sa-av sa-av-{{ $color }}">{{ strtoupper(substr($selectedTaxObjectData['nama'] ?? '?', 0, 1)) }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ $selectedTaxObjectData['nama'] }}</span>
                            <span class="sa-badge sa-bg-{{ $color }}">{{ $selectedTaxObjectData['jenis_pajak_nama'] }}</span>
                            @if($selectedTaxObjectData['is_multi_billing'] ?? false)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    {{ $selectedTaxObjectData['is_opd'] ? 'OPD' : 'Insidentil' }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            NPWPD: {{ $selectedTaxObjectData['npwpd'] ?? '-' }}
                            @if($wajibPajakData) &middot; {{ $wajibPajakData['nama_lengkap'] }} @endif
                        </p>
                    </div>
                    <span class="text-xs font-bold sa-text-primary sa-bg-p10 rounded-lg px-2 py-1 flex-shrink-0">{{ $tarif }}%</span>
                </div>
            </div>

            {{-- Form --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-4">
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Data Pelaporan</h4>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Bulan</label>
                        <select wire:model="masaPajakBulan"
                                class="sa-ring block w-full py-2 px-3 border border-slate-200 dark:border-slate-700
                                       rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tahun</label>
                        <select wire:model="masaPajakTahun"
                                class="sa-ring block w-full py-2 px-3 border border-slate-200 dark:border-slate-700
                                       rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                            @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                @if($isPpjPln)
                {{-- PPJ PLN: Input Pokok Pajak Terutang --}}
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Pokok Pajak Terutang (dari PLN)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 p-4 pl-3 flex items-center text-slate-500 dark:text-slate-400 text-sm font-bold">Rp</span>
                        <input type="text"
                               x-model="ppjPokokDisplay"
                               @input="onPpjPokokInput($event)"
                               inputmode="numeric"
                               placeholder="0"
                               class="sa-ring block w-full pl-10 pr-3 py-2.5 border border-slate-200 dark:border-slate-700
                                      rounded-lg bg-white dark:bg-slate-800 text-base font-semibold text-right
                                      text-slate-900 dark:text-white placeholder-slate-300 dark:placeholder-slate-600">
                    </div>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">
                        Masukkan jumlah pokok pajak terutang langsung dari tagihan PLN.
                    </p>
                </div>

                @elseif($isPpjNonPln)
                {{-- PPJ Non-PLN: 4 Komponen NJTL --}}
                <div class="mb-5 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Kapasitas (kVA)</label>
                            <input type="number"
                                   x-model.number="ppjKapasitasKva"
                                   step="0.01" min="0"
                                   placeholder="0"
                                   class="sa-ring block w-full px-3 py-2.5 border border-slate-200 dark:border-slate-700
                                          rounded-lg bg-white dark:bg-slate-800 text-sm text-right
                                          text-slate-900 dark:text-white placeholder-slate-300 dark:placeholder-slate-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tingkat Penggunaan (%)</label>
                            <input type="number"
                                   x-model.number="ppjTingkatPersen"
                                   step="0.01" min="0" max="100"
                                   placeholder="0"
                                   class="sa-ring block w-full px-3 py-2.5 border border-slate-200 dark:border-slate-700
                                          rounded-lg bg-white dark:bg-slate-800 text-sm text-right
                                          text-slate-900 dark:text-white placeholder-slate-300 dark:placeholder-slate-600">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Jangka Waktu (Jam)</label>
                            <input type="number"
                                   x-model.number="ppjJangkaWaktuJam"
                                   step="0.01" min="0"
                                   placeholder="0"
                                   class="sa-ring block w-full px-3 py-2.5 border border-slate-200 dark:border-slate-700
                                          rounded-lg bg-white dark:bg-slate-800 text-sm text-right
                                          text-slate-900 dark:text-white placeholder-slate-300 dark:placeholder-slate-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Harga Satuan Listrik</label>
                            <select wire:model.live="ppjHargaSatuanListrikId"
                                    class="sa-ring block w-full py-2.5 px-3 border border-slate-200 dark:border-slate-700
                                           rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                                <option value="">-- Pilih --</option>
                                @foreach($ppjHargaSatuanOptions as $opt)
                                    <option value="{{ $opt['id'] }}">{{ $opt['nama'] }} - Rp {{ number_format($opt['harga'], 0, ',', '.') }}/kWh</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if($ppjHargaSatuan)
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Harga satuan terpilih: <span class="font-semibold text-slate-700 dark:text-slate-300">Rp {{ number_format($ppjHargaSatuan, 0, ',', '.') }}/kWh</span>
                    </p>
                    @endif
                </div>

                @else
                {{-- Standard: Omzet --}}
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Total Omzet / Pendapatan</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 p-4 pl-3 flex items-center text-slate-500 dark:text-slate-400 text-sm font-bold">Rp</span>
                        <input type="text"
                               x-model="displayValue"
                               @input="onInput($event)"
                               inputmode="numeric"
                               placeholder="0"
                               class="sa-ring block w-full pl-10 pr-3 py-2.5 border border-slate-200 dark:border-slate-700
                                      rounded-lg bg-white dark:bg-slate-800 text-base font-semibold text-right
                                      text-slate-900 dark:text-white placeholder-slate-300 dark:placeholder-slate-600">
                    </div>
                </div>
                @endif

                {{-- Keterangan (hanya untuk multi-billing: OPD / insidentil) --}}
                @if($selectedTaxObjectData['is_multi_billing'] ?? false)
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Keterangan Billing <span class="text-red-500">*</span></label>
                    <textarea wire:model="keterangan"
                              rows="2"
                              placeholder="Contoh: Katering Rapat Dinas, Pertunjukan HUT Kab. Bojonegoro, dll."
                              class="sa-ring block w-full px-3 py-2 border border-slate-200 dark:border-slate-700
                                     rounded-lg bg-white dark:bg-slate-800 text-sm
                                     text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500"
                              style="resize: vertical;"></textarea>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">
                        <svg class="inline w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Wajib diisi karena dalam satu masa pajak dapat memiliki lebih dari satu billing.
                    </p>
                </div>
                @endif

                @if($isPpjPln)
                {{-- PPJ PLN Preview --}}
                <div class="rounded-xl sa-bg-p5 border sa-border-p20 p-4 mb-5">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-slate-500 dark:text-slate-400">Pokok Pajak (Input)</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200 tabular-nums" x-text="fmtRp(ppjPokokPajak || 0)">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-slate-500 dark:text-slate-400">DPP (Kalkulasi Balik)</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200 tabular-nums" x-text="fmtRp(ppjPlnDpp)">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-sm mb-3">
                        <span class="text-slate-500 dark:text-slate-400">Tarif PPJ PLN</span>
                        <span class="sa-bg-p20 sa-text-primary text-xs font-bold px-2 py-0.5 rounded">{{ number_format($tarif,0) }}%</span>
                    </div>
                    <div class="border-t sa-border-p10 pt-3 flex justify-between items-center">
                        <span class="font-bold text-slate-900 dark:text-white">Total Pajak Terutang</span>
                        <span class="text-2xl font-black tabular-nums transition-colors"
                              :class="(ppjPokokPajak || 0) > 0 ? 'sa-text-primary' : 'text-slate-300 dark:text-slate-600'"
                              x-text="fmtRp(ppjPokokPajak || 0)">Rp 0</span>
                    </div>
                </div>

                @elseif($isPpjNonPln)
                {{-- PPJ Non-PLN Preview --}}
                <div class="rounded-xl sa-bg-p5 border sa-border-p20 p-4 mb-5">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-slate-500 dark:text-slate-400">NJTL</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200 tabular-nums" x-text="fmtRp(njtl)">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-slate-500 dark:text-slate-400">DPP (= NJTL)</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200 tabular-nums" x-text="fmtRp(njtl)">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-sm mb-3">
                        <span class="text-slate-500 dark:text-slate-400">Tarif PPJ Non-PLN</span>
                        <span class="sa-bg-p20 sa-text-primary text-xs font-bold px-2 py-0.5 rounded">{{ number_format($tarif,0) }}%</span>
                    </div>
                    <div class="border-t sa-border-p10 pt-3 flex justify-between items-center">
                        <span class="font-bold text-slate-900 dark:text-white">Total Pajak Terutang</span>
                        <span class="text-2xl font-black tabular-nums transition-colors"
                              :class="njtl > 0 ? 'sa-text-primary' : 'text-slate-300 dark:text-slate-600'"
                              x-text="fmtRp(ppjNonPlnTax)">Rp 0</span>
                    </div>
                </div>

                @else
                {{-- Standard Preview --}}
                <div class="rounded-xl sa-bg-p5 border sa-border-p20 p-4 mb-5">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-slate-500 dark:text-slate-400">DPP (Omzet)</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200 tabular-nums" x-text="fmtRp(rawValue || 0)">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-sm mb-3">
                        <span class="text-slate-500 dark:text-slate-400">Tarif {{ $selectedTaxObjectData['jenis_pajak_nama'] }}</span>
                        <span class="sa-bg-p20 sa-text-primary text-xs font-bold px-2 py-0.5 rounded">{{ number_format($tarif,0) }}%</span>
                    </div>
                    <div class="border-t sa-border-p10 pt-3 flex justify-between items-center">
                        <span class="font-bold text-slate-900 dark:text-white">Total Pajak Terutang</span>
                        <span class="text-2xl font-black tabular-nums transition-colors"
                              :class="(rawValue || 0) > 0 ? 'sa-text-primary' : 'text-slate-300 dark:text-slate-600'"
                              x-text="fmtRp(tax)">Rp 0</span>
                    </div>
                </div>
                @endif

                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-4">
                    <span>Masa pajak yang akan ditagih</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">
                        {{ \Carbon\Carbon::create($masaPajakTahun, $masaPajakBulan, 1)->translatedFormat('F Y') }}
                    </span>
                </div>

                <button wire:click="terbitkanBilling"
                        wire:loading.attr="disabled"
                        x-bind:disabled="!canSubmit"
                        class="w-full sa-bg-primary sa-bg-ph sa-shadow disabled:opacity-40 disabled:cursor-not-allowed
                               text-white font-bold py-3.5 rounded-xl transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                    <svg wire:loading.remove wire:target="terbitkanBilling" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg wire:loading wire:target="terbitkanBilling" class="animate-spin h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span wire:loading.remove wire:target="terbitkanBilling">Terbitkan Billing</span>
                    <span wire:loading wire:target="terbitkanBilling">Memproses...</span>
                </button>
                <p class="text-[10px] text-center text-slate-500 dark:text-slate-400 mt-2 italic">
                    Data yang diinput dianggap benar sesuai kondisi lapangan.
                </p>
            </div>
        </div>

        @else
        <div class="flex flex-col items-center justify-center h-full min-h-[24rem]
                    bg-white dark:bg-slate-900 rounded-xl border border-dashed
                    border-slate-200 dark:border-slate-700 p-10 text-center">
            <div class="w-14 h-14 rounded-2xl sa-bg-p10 flex items-center justify-center mt-2 mb-4">
                <svg class="w-7 h-7 sa-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                             M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <h3 class="font-bold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Objek Pajak</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs mb-2">
                Cari dengan mengetik minimal 3 karakter untuk mulai mengisi form billing.
            </p>
        </div>
        @endif

    </div>{{-- end right col --}}
</div>{{-- end grid --}}

{{-- Duplicate Confirmation Modal --}}
@if($showDuplicateConfirm)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/70 backdrop-blur-sm"
         wire:click="cancelDuplicateConfirm"></div>
    <div class="relative z-10 w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl
                border border-slate-200 dark:border-slate-700 overflow-hidden sa-pop">
        <div class="px-6 pt-6 pb-3 flex items-start gap-4">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0
                        {{ str()->contains($duplicateConfirmTitle, 'Pembetulan') ? 'bg-amber-100 dark:bg-amber-900/30' : 'bg-orange-100 dark:bg-orange-900/30' }}">
                @if(str()->contains($duplicateConfirmTitle, 'Pembetulan'))
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                @else
                    <svg class="w-5 h-5 text-orange-500 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{!! $duplicateConfirmTitle !!}</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">{!! $duplicateConfirmMessage !!}</p>
                @if($existingBillingInfo)
                    @include('filament.pages.partials.existing-billing-summary', ['existingBillingInfo' => $existingBillingInfo])
                @endif
            </div>
            <button wire:click="cancelDuplicateConfirm"
                    class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-slate-400
                           hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="px-6 py-4 flex gap-3 justify-end border-t border-slate-100 dark:border-slate-800">
            <button wire:click="cancelDuplicateConfirm"
                    class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700
                           bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300
                           font-semibold text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                Batal
            </button>
            <button wire:click="confirmAndGenerate" wire:loading.attr="disabled"
                    class="px-4 py-2 rounded-lg sa-bg-primary sa-bg-ph sa-shadow text-white font-bold text-sm
                           transition-all flex items-center gap-2">
                <span wire:loading.remove wire:target="confirmAndGenerate">Ya, Lanjutkan</span>
                <span wire:loading wire:target="confirmAndGenerate" class="flex items-center gap-1.5">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Memproses...
                </span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Skipped Month Confirmation Modal --}}
@if($showSkippedMonthConfirm)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/70 backdrop-blur-sm"
         wire:click="cancelSkippedMonthConfirm"></div>
    <div class="relative z-10 w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl
                border border-slate-200 dark:border-slate-700 overflow-hidden sa-pop">
        <div class="px-6 pt-6 pb-3 flex items-start gap-4">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-amber-100 dark:bg-amber-900/30">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{!! $skippedMonthConfirmTitle !!}</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">{!! $skippedMonthConfirmMessage !!}</p>
            </div>
            <button wire:click="cancelSkippedMonthConfirm"
                    class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-slate-400
                           hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="px-6 py-4 flex gap-3 justify-end border-t border-slate-100 dark:border-slate-800">
            <button wire:click="cancelSkippedMonthConfirm"
                    class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700
                           bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300
                           font-semibold text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                Tidak
            </button>
            <button wire:click="confirmSkippedMonthAndContinue" wire:loading.attr="disabled"
                    class="px-4 py-2 rounded-lg sa-bg-primary sa-bg-ph sa-shadow text-white font-bold text-sm
                           transition-all flex items-center gap-2">
                <span wire:loading.remove wire:target="confirmSkippedMonthAndContinue">Ya, Lanjutkan</span>
                <span wire:loading wire:target="confirmSkippedMonthAndContinue" class="flex items-center gap-1.5">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Memproses...
                </span>
            </button>
        </div>
    </div>
</div>
@endif

</div>
</x-filament-panels::page>