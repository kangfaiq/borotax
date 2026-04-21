<x-filament-panels::page>

<style>
    :root {
        --mb-primary: #7c3aed; --mb-primary-h: #6d28d9;
        --mb-primary-5: rgba(124,58,237,.05);  --mb-primary-10: rgba(124,58,237,.10);
        --mb-primary-20: rgba(124,58,237,.20); --mb-primary-30: rgba(124,58,237,.30);
        --mb-shadow: rgba(124,58,237,.20);
    }
    .dark {
        --mb-primary: #a78bfa; --mb-primary-h: #8b5cf6;
        --mb-primary-5: rgba(167,139,250,.10);  --mb-primary-10: rgba(167,139,250,.18);
        --mb-primary-20: rgba(167,139,250,.28); --mb-primary-30: rgba(167,139,250,.38);
        --mb-shadow: rgba(167,139,250,.30);
    }
    .mb-text-primary  { color: var(--mb-primary); }
    .mb-bg-primary    { background-color: var(--mb-primary); }
    .mb-bg-ph:hover   { background-color: var(--mb-primary-h); }
    .mb-bg-p5         { background-color: var(--mb-primary-5); }
    .mb-bg-p10        { background-color: var(--mb-primary-10); }
    .mb-bg-p20        { background-color: var(--mb-primary-20); }
    .mb-border-p10    { border-color: var(--mb-primary-10); }
    .mb-border-p20    { border-color: var(--mb-primary-20); }
    .mb-ring:focus    { --tw-ring-color: var(--mb-primary); border-color: var(--mb-primary); }
    .mb-shadow        { box-shadow: 0 4px 12px -1px var(--mb-shadow); }
    .mb-av { width:2.1rem; height:2.1rem; border-radius:.45rem; display:flex; align-items:center;
             justify-content:center; font-weight:700; font-size:.8rem; color:#fff; flex-shrink:0;
             background:linear-gradient(135deg,#a855f7,#7c3aed); }
    .mb-badge { padding:.1rem .4rem; border-radius:.25rem; font-size:.58rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em;
                background:#ede9fe; color:#5b21b6; }
    .dark .mb-badge { background:rgba(167,139,250,.15); color:#c4b5fd; }
    .mb-card { transition:all .18s; cursor:pointer; position:relative; overflow:hidden; }
    .mb-card::before { content:""; position:absolute; left:0; top:0; bottom:0; width:3px;
                       background:transparent; transition:background .18s; border-radius:0 3px 3px 0; }
    .mb-card:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(124,58,237,.09); border-color:#c4b5fd !important; }
    .mb-card:hover::before { background:var(--mb-primary); }
    .dark .mb-card:hover { border-color:#8b5cf6 !important; box-shadow:0 4px 14px rgba(167,139,250,.18); }
    .mb-card-selected { border-color:var(--mb-primary) !important; background:var(--mb-primary-5) !important; }
    .mb-card-selected::before { background:var(--mb-primary); }
    @keyframes mb-pop { 0%{transform:scale(.85);opacity:0} 100%{transform:scale(1);opacity:1} }
    .mb-pop { animation:mb-pop .35s cubic-bezier(.175,.885,.32,1.275); }
    @keyframes mb-shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
    .mb-shimmer { background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,.15) 50%,transparent 100%);
                  background-size:200% 100%; animation:mb-shimmer 2.2s infinite; }
    .dark .mb-page .bg-white { background-color: #0f172a; }
    .dark .mb-page select,
    .dark .mb-page input[type="text"],
    .dark .mb-page input[type="number"] { background-color: #1e293b; }
    .dark .mb-page .bg-slate-50 { background-color: #1e293b; }
    .dark .mb-page .border-slate-200 { border-color: #334155; }
    .dark .mb-page .border-slate-100 { border-color: #1e293b; }
    .mineral-row-empty { opacity: 0.5; }
    .mineral-row-filled { background-color: rgba(124,58,237,.03); }
    .dark .mineral-row-filled { background-color: rgba(167,139,250,.06); }
</style>

<div class="mb-page py-2">

{{-- Page header --}}
<div class="mb-5 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Buat Billing MBLB</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pajak Mineral Bukan Logam dan Batuan — Self Assessment</p>
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
                       placeholder="NIK, NPWPD, atau nama objek pajak MBLB..."
                       class="mb-ring block w-full pl-4 pr-9 py-2.5 border border-slate-200 dark:border-slate-700
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
                            $initial = strtoupper(substr($obj['nama'] ?? '?', 0, 1));
                            $isSelected = $selectedTaxObjectId === $obj['id'];
                            $isExpanded = $expandedDetailId === $obj['id'];
                        @endphp
                        <div wire:click="selectObject('{{ $obj['id'] }}')"
                             class="mb-card bg-white dark:bg-slate-900 border rounded-xl p-3
                                    {{ $isSelected ? 'mb-card-selected border-purple-400 dark:border-purple-500' : 'border-slate-200 dark:border-slate-800' }}">
                            <div class="flex items-center gap-3">
                                <div class="mb-av">{{ $initial }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-semibold text-slate-900 dark:text-white text-sm truncate max-w-[10rem]">{{ $obj['nama'] }}</span>
                                        <span class="mb-badge">MBLB</span>
                                        @if($obj['is_opd'])
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">OPD</span>
                                        @endif
                                        @if($isSelected)
                                            <span class="mb-badge ml-auto">&#10003; Dipilih</span>
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
                                               {{ $isExpanded ? 'border-purple-200 bg-purple-50 text-purple-500 dark:border-purple-800 dark:bg-purple-900/20 dark:text-purple-400'
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
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Coba kata kunci lain. Pastikan objek pajak MBLB sudah terdaftar.</p>
            </div>
            @endif
        @endif
    </div>

    {{-- RIGHT: Panel (3/5) --}}
    <div class="lg:col-span-3">

        {{-- SUCCESS --}}
        @if($billingResult)
        <div class="mb-pop space-y-4">
            <div class="text-center py-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600
                            flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/25">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white mt-3">
                    {{ $billingResult['is_tambahan'] ? 'Billing Tambahan Berhasil!' : 'Billing MBLB Berhasil Dibuat!' }}
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Kode Pembayaran Aktif siap digunakan untuk pembayaran.</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="relative overflow-hidden bg-slate-900 dark:bg-slate-800 px-6 py-5 text-center">
                    <div class="mb-shimmer absolute inset-0 pointer-events-none"></div>
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
                        @if(!empty($billingResult['instansi']))
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Instansi</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200 text-right">{{ $billingResult['instansi'] }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Total DPP ({{ $billingResult['detail_count'] }} jenis mineral)</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200 tabular-nums">Rp {{ number_format($billingResult['total_dpp'],0,',','.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Tarif Pajak MBLB</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $billingResult['tarif'] }}%</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Pokok Pajak MBLB</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200 tabular-nums">Rp {{ number_format($billingResult['amount'],0,',','.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Opsen MBLB ({{ $billingResult['opsen_persen'] }}%)</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200 tabular-nums">Rp {{ number_format($billingResult['opsen'],0,',','.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-slate-100 dark:border-slate-800">
                            <span class="font-bold text-slate-900 dark:text-white">Total Billing</span>
                            <span class="text-xl font-black mb-text-primary tabular-nums">Rp {{ number_format($billingResult['total'],0,',','.') }}</span>
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
                        class="flex-1 mb-bg-primary mb-bg-ph mb-shadow text-white font-bold
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
        <div class="space-y-4"
             x-data="{
                minerals: @entangle('mineralItems').live,
                volDisplays: {},
                mineralSearch: '',
                tarifPersen: {{ $tarifPersen }},
                opsenPersen: {{ $opsenPersen }},
                fmt(n)   { return n ? Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.') : '0'; },
                fmtRp(n) { return 'Rp ' + this.fmt(n); },
                fmtVol(n) {
                    if (!n && n !== 0) return '';
                    let s = n.toString();
                    let parts = s.split('.');
                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    return parts.length > 1 ? parts[0] + ',' + parts[1] : parts[0];
                },
                parseVol(str) {
                    if (!str) return 0;
                    let clean = str.replace(/\./g, '').replace(',', '.');
                    return parseFloat(clean) || 0;
                },
                onVolInput(idx, event) {
                    let val = event.target.value;
                    // hanya angka, titik (ribuan), dan koma (desimal)
                    val = val.replace(/[^0-9.,]/g, '');
                    // max 1 koma
                    let parts = val.split(',');
                    if (parts.length > 2) val = parts[0] + ',' + parts.slice(1).join('');
                    // max 2 digit setelah koma
                    if (parts.length === 2 && parts[1].length > 2) {
                        val = parts[0] + ',' + parts[1].substring(0, 2);
                    }
                    // hapus titik lama, parse angka, format ulang ribuan
                    let intPart = val.split(',')[0].replace(/\./g, '');
                    let decPart = val.split(',')[1];
                    intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    val = decPart !== undefined ? intPart + ',' + decPart : intPart;
                    event.target.value = val;
                    this.volDisplays[idx] = val;
                    // simpan nilai numerik ke model
                    this.minerals[idx].volume = this.parseVol(val);
                },
                onVolFocus(idx, event) {
                    // tampilkan value yang sudah diformat
                },
                onVolBlur(idx, event) {
                    let num = this.parseVol(event.target.value);
                    if (num > 0) {
                        event.target.value = this.fmtVol(num);
                        this.volDisplays[idx] = event.target.value;
                    } else {
                        event.target.value = '';
                        this.volDisplays[idx] = '';
                    }
                    this.minerals[idx].volume = num;
                },
                initVolDisplays() {
                    this.minerals.forEach((m, idx) => {
                        let v = parseFloat(m.volume) || 0;
                        this.volDisplays[idx] = v > 0 ? this.fmtVol(v) : '';
                    });
                },
                matchMineral(mineral) {
                    if (!this.mineralSearch) return true;
                    let q = this.mineralSearch.toLowerCase();
                    if (mineral.nama_mineral && mineral.nama_mineral.toLowerCase().includes(q)) return true;
                    if (mineral.nama_alternatif && Array.isArray(mineral.nama_alternatif)) {
                        return mineral.nama_alternatif.some(a => a.toLowerCase().includes(q));
                    }
                    return false;
                },
                get matchCount() {
                    if (!this.mineralSearch) return this.minerals.length;
                    return this.minerals.filter(m => this.matchMineral(m)).length;
                },
                subtotal(idx) {
                    let v = parseFloat(this.minerals[idx]?.volume) || 0;
                    let h = parseFloat(this.minerals[idx]?.harga_patokan) || 0;
                    return Math.round(v * h);
                },
                get totalDpp() {
                    let sum = 0;
                    for (let i = 0; i < this.minerals.length; i++) sum += this.subtotal(i);
                    return sum;
                },
                get pokokPajak() { return Math.round(this.totalDpp * this.tarifPersen / 100); },
                get opsen() { return Math.round(this.pokokPajak * this.opsenPersen / 100); },
                get totalBilling() { return this.pokokPajak + this.opsen; },
                get hasVolume() {
                    return this.minerals.some(m => parseFloat(m.volume) > 0);
                },
                init() {
                    this.$nextTick(() => this.initVolDisplays());
                }
             }">

            {{-- Selected object card --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="mb-av">{{ strtoupper(substr($selectedTaxObjectData['nama'] ?? '?', 0, 1)) }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ $selectedTaxObjectData['nama'] }}</span>
                            <span class="mb-badge">MBLB</span>
                            @if($selectedTaxObjectData['is_multi_billing'] ?? false)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                    {{ $selectedTaxObjectData['is_opd'] ? 'WP Pungut / OPD' : 'Insidentil' }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            NPWPD: {{ $selectedTaxObjectData['npwpd'] ?? '-' }}
                            @if($wajibPajakData) &middot; {{ $wajibPajakData['nama_lengkap'] }} @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-4">
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Data Mineral & Volume</h4>

                {{-- Masa pajak --}}
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Bulan</label>
                        <select wire:model="masaPajakBulan"
                                class="mb-ring block w-full py-2 px-3 border border-slate-200 dark:border-slate-700
                                       rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tahun</label>
                        <select wire:model="masaPajakTahun"
                                class="mb-ring block w-full py-2 px-3 border border-slate-200 dark:border-slate-700
                                       rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                            @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- Mineral search --}}
                <div class="relative mb-4">
                    <input type="text" x-model="mineralSearch" placeholder="Cari jenis mineral..."
                           class="mb-ring block w-full pl-9 pr-8 py-2 border border-slate-200 dark:border-slate-700
                                  rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white
                                  placeholder-slate-400 dark:placeholder-slate-500">
                    
                    <template x-if="mineralSearch">
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1" x-text="matchCount + ' mineral ditemukan'"></p>
                    </template>
                </div>

                {{-- Mineral table --}}
                <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700 mb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800">
                                <th class="px-3 py-2 text-left text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-8">No</th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jenis MBLB</th>
                                <th class="px-3 py-2 text-right text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-32">Harga Patokan</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-28">Volume (m&sup3;)</th>
                                <th class="px-3 py-2 text-right text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-36">Subtotal DPP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <template x-for="(mineral, idx) in minerals" :key="idx">
                                <tr x-show="matchMineral(mineral)" :class="parseFloat(mineral.volume) > 0 ? 'mineral-row-filled' : 'mineral-row-empty'">
                                    <td class="px-3 py-2 text-slate-500 dark:text-slate-400 tabular-nums" x-text="idx + 1"></td>
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-slate-900 dark:text-white text-xs" x-text="mineral.nama_mineral"></div>
                                        <template x-if="mineral.nama_alternatif && mineral.nama_alternatif.length > 0">
                                            <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                <span x-text="'Alt: ' + mineral.nama_alternatif.join(', ')"></span>
                                            </div>
                                        </template>
                                    </td>
                                    <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-300 tabular-nums text-xs" x-text="fmtRp(mineral.harga_patokan)"></td>
                                    <td class="px-3 py-2">
                                        <input type="text"
                                               inputmode="decimal"
                                               :value="volDisplays[idx] || ''"
                                               @input="onVolInput(idx, $event)"
                                               @blur="onVolBlur(idx, $event)"
                                               placeholder="0"
                                               class="mb-ring w-full px-2 py-1.5 border border-slate-200 dark:border-slate-600
                                                      rounded-md bg-white dark:bg-slate-800 text-right text-sm
                                                      text-slate-900 dark:text-white placeholder-slate-300 dark:placeholder-slate-600">
                                    </td>
                                    <td class="px-3 py-2 text-right tabular-nums text-xs font-medium"
                                        :class="subtotal(idx) > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-300 dark:text-slate-600'"
                                        x-text="fmtRp(subtotal(idx))"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                @if($this->shouldShowInstansiField())
                    <x-searchable-instansi-select
                        model="instansiId"
                        :options="$instansiOptions"
                        :selected="$instansiId"
                        label="Instansi / Lembaga"
                        container-class="mb-4"
                        ring-class="mb-ring"
                        placeholder="Cari instansi / OPD / lembaga..."
                        empty-label="-- Opsional, tanpa instansi --"
                        help-text="Isi jika tagihan MBLB ini diterbitkan untuk skema WAPU tertentu." />
                @endif

                {{-- Keterangan (OPD / insidentil) --}}
                @if($selectedTaxObjectData['is_multi_billing'] ?? false)
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Keterangan Billing <span class="text-red-500">*</span></label>
                    <textarea wire:model="keterangan"
                              rows="2"
                              placeholder="Contoh: Pengambilan mineral untuk proyek jalan desa, dll."
                              class="mb-ring block w-full px-3 py-2 border border-slate-200 dark:border-slate-700
                                     rounded-lg bg-white dark:bg-slate-800 text-sm
                                     text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500"
                              style="resize: vertical;"></textarea>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">
                        Wajib diisi karena dalam satu masa pajak dapat memiliki lebih dari satu billing.
                    </p>
                </div>
                @endif

                {{-- Summary calculation --}}
                <div class="rounded-xl mb-bg-p5 border mb-border-p20 p-4 mb-5">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-slate-500 dark:text-slate-400">Total DPP (Volume &times; Harga Patokan)</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200 tabular-nums" x-text="fmtRp(totalDpp)">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-slate-500 dark:text-slate-400">Tarif Pajak MBLB</span>
                        <span class="mb-bg-p20 mb-text-primary text-xs font-bold px-2 py-0.5 rounded">{{ number_format($tarifPersen, 0) }}%</span>
                    </div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-slate-500 dark:text-slate-400">Pokok Pajak MBLB</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200 tabular-nums" x-text="fmtRp(pokokPajak)">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-sm mb-3">
                        <span class="text-slate-500 dark:text-slate-400">Opsen MBLB ({{ number_format($opsenPersen, 0) }}%)</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200 tabular-nums" x-text="fmtRp(opsen)">Rp 0</span>
                    </div>
                    <div class="border-t mb-border-p10 pt-3 flex justify-between items-center">
                        <span class="font-bold text-slate-900 dark:text-white">Total Billing</span>
                        <span class="text-2xl font-black tabular-nums transition-colors"
                              :class="totalDpp > 0 ? 'mb-text-primary' : 'text-slate-300 dark:text-slate-600'"
                              x-text="fmtRp(totalBilling)">Rp 0</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-4">
                    <span>Masa pajak yang akan ditagih</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">
                        {{ \Carbon\Carbon::create($masaPajakTahun, $masaPajakBulan, 1)->translatedFormat('F Y') }}
                    </span>
                </div>

                <button wire:click="terbitkanBilling"
                        wire:loading.attr="disabled"
                        x-bind:disabled="!hasVolume"
                        class="w-full mb-bg-primary mb-bg-ph mb-shadow disabled:opacity-40 disabled:cursor-not-allowed
                               text-white font-bold py-3.5 rounded-xl transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                    <svg wire:loading.remove wire:target="terbitkanBilling" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg wire:loading wire:target="terbitkanBilling" class="animate-spin h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span wire:loading.remove wire:target="terbitkanBilling">Terbitkan Billing MBLB</span>
                    <span wire:loading wire:target="terbitkanBilling">Memproses...</span>
                </button>
                <p class="text-[10px] text-center text-slate-500 dark:text-slate-400 mt-2 italic">
                    Data yang diinput dianggap benar sesuai kondisi lapangan.
                </p>
            </div>
        </div>

        @else
        {{-- Empty state --}}
        <div class="flex flex-col items-center justify-center h-full min-h-[24rem]
                    bg-white dark:bg-slate-900 rounded-xl border border-dashed
                    border-slate-200 dark:border-slate-700 p-10 text-center">
            <div class="w-14 h-14 rounded-2xl mb-bg-p10 flex items-center justify-center mt-2 mb-4">
                <svg class="w-7 h-7 mb-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h3 class="font-bold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Objek Pajak MBLB</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs mb-2">
                Cari objek pajak MBLB dengan mengetik minimal 3 karakter untuk mulai mengisi form billing.
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
                border border-slate-200 dark:border-slate-700 overflow-hidden mb-pop">
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
                    class="px-4 py-2 rounded-lg mb-bg-primary mb-bg-ph mb-shadow text-white font-bold text-sm
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

</div>
</x-filament-panels::page>
