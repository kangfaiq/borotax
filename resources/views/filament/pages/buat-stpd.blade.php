<x-filament-panels::page>

<style>
    :root {
        --stpd-primary: #7c3aed; --stpd-primary-h: #6d28d9;
        --stpd-primary-5: rgba(124,58,237,.05);  --stpd-primary-10: rgba(124,58,237,.10);
        --stpd-primary-20: rgba(124,58,237,.20); --stpd-primary-30: rgba(124,58,237,.30);
        --stpd-shadow: rgba(124,58,237,.20);
    }
    .dark {
        --stpd-primary: #a78bfa; --stpd-primary-h: #8b5cf6;
        --stpd-primary-5: rgba(167,139,250,.10);  --stpd-primary-10: rgba(167,139,250,.18);
        --stpd-primary-20: rgba(167,139,250,.28); --stpd-primary-30: rgba(167,139,250,.38);
        --stpd-shadow: rgba(167,139,250,.30);
    }
    .stpd-text-primary  { color: var(--stpd-primary); }
    .stpd-bg-primary    { background-color: var(--stpd-primary); }
    .stpd-bg-ph:hover   { background-color: var(--stpd-primary-h); }
    .stpd-bg-p5         { background-color: var(--stpd-primary-5); }
    .stpd-bg-p10        { background-color: var(--stpd-primary-10); }
    .stpd-border-p20    { border-color: var(--stpd-primary-20); }
    .stpd-ring:focus    { --tw-ring-color: var(--stpd-primary); border-color: var(--stpd-primary); }
    .stpd-shadow        { box-shadow: 0 4px 12px -1px var(--stpd-shadow); }
    .stpd-card { transition:all .18s; cursor:pointer; position:relative; overflow:hidden; }
    .stpd-card::before { content:""; position:absolute; left:0; top:0; bottom:0; width:3px;
                         background:transparent; transition:background .18s; border-radius:0 3px 3px 0; }
    .stpd-card:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(124,58,237,.09); border-color:#c4b5fd !important; }
    .stpd-card:hover::before { background:var(--stpd-primary); }
    .dark .stpd-card:hover { border-color:#8b5cf6 !important; box-shadow:0 4px 14px rgba(167,139,250,.18); }
    .stpd-card-selected { border-color:var(--stpd-primary) !important; background:var(--stpd-primary-5) !important; }
    .stpd-card-selected::before { background:var(--stpd-primary); }
    @keyframes stpd-pop { 0%{transform:scale(.85);opacity:0} 100%{transform:scale(1);opacity:1} }
    .stpd-pop { animation:stpd-pop .35s cubic-bezier(.175,.885,.32,1.275); }
    .dark .stpd-page .bg-white { background-color: #0f172a; }
    .dark .stpd-page select,
    .dark .stpd-page input[type="text"],
    .dark .stpd-page input[type="date"],
    .dark .stpd-page textarea { background-color: #1e293b; }
    .dark .stpd-page .bg-slate-50 { background-color: #1e293b; }
    .dark .stpd-page .border-slate-200 { border-color: #334155; }
</style>

<div class="stpd-page py-2">

{{-- Page Header --}}
<div class="mb-5">
    <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Buat STPD</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Cari Billing Sumber, pilih tipe tagihan, hitung sanksi, buat draft STPD.</p>
</div>

{{-- ================================================================== --}}
{{-- SUCCESS STATE                                                       --}}
{{-- ================================================================== --}}
@if($stpdResult)
<div class="stpd-pop bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-8 text-center">
    <div class="mx-auto w-16 h-16 rounded-full stpd-bg-p10 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 stpd-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Draft STPD Berhasil Dibuat!</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Menunggu verifikasi oleh Verifikator.</p>

    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 text-left mb-6 max-w-md mx-auto space-y-2 text-sm">
        <div class="flex justify-between">
            <span class="text-slate-500">Tipe STPD</span>
            <span class="font-semibold text-slate-900 dark:text-white">{{ $stpdResult['tipe'] }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Billing Sumber</span>
            <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $stpdResult['billing_code'] }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Wajib Pajak</span>
            <span class="font-semibold text-slate-900 dark:text-white">{{ $stpdResult['nama_wp'] }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Sanksi Dihitung</span>
            <span class="font-bold stpd-text-primary">Rp {{ number_format($stpdResult['sanksi'], 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="flex justify-center gap-3">
        <button wire:click="buatBaru"
                class="px-6 py-2.5 rounded-xl text-sm font-bold stpd-bg-primary stpd-bg-ph text-white transition-all stpd-shadow">
            Buat STPD Baru
        </button>
        @if($stpdResult['verifikasi_url'] ?? null)
        <a href="{{ $stpdResult['verifikasi_url'] }}"
           class="px-6 py-2.5 rounded-xl text-sm font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
            Lihat Verifikasi →
        </a>
        @endif
    </div>
</div>
@else

{{-- ================================================================== --}}
{{-- SEARCH BAR                                                          --}}
{{-- ================================================================== --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 mb-5">
    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
        Cari Billing Sumber
    </label>
    <div class="flex gap-3">
        <div class="flex-1 relative">
            <input type="text" wire:model.defer="searchKeyword"
                   wire:keydown.enter="cariBilling"
                     placeholder="Masukkan 18 digit Billing Sumber atau 13 digit NPWPD"
                   class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-3 text-sm
                          bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white
                          placeholder-slate-400 stpd-ring focus:ring-2 focus:outline-none font-mono tracking-wide">
            <svg class="w-5 h-5 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <button wire:click="cariBilling"
                class="px-6 py-3 rounded-xl text-sm font-bold stpd-bg-primary stpd-bg-ph text-white transition-all stpd-shadow whitespace-nowrap">
            <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Cari
        </button>
    </div>
</div>

{{-- ================================================================== --}}
{{-- SEARCH RESULTS (multiple billings)                                  --}}
{{-- ================================================================== --}}
@if(count($searchResults) > 0)
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 mb-5">
    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">
        Pilih Billing Sumber ({{ count($searchResults) }} ditemukan)
    </h3>
    <div class="space-y-2">
        @foreach($searchResults as $result)
        <div wire:click="selectBilling('{{ $result['id'] }}')"
             class="stpd-card rounded-xl border border-slate-200 dark:border-slate-700 p-4 hover:bg-slate-50 dark:hover:bg-slate-800">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-mono text-sm font-bold text-slate-900 dark:text-white">{{ $result['billing_code'] }}</span>
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                'verified' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'expired' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                                'partially_paid' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                            ];
                        @endphp
                        <span class="text-xs font-bold px-2 py-0.5 rounded {{ $statusColors[$result['status']] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ strtoupper($result['status_label']) }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        {{ $result['jenis_pajak'] }} &middot; {{ $result['nama_objek'] }} &middot; {{ $result['masa_pajak'] }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-bold text-slate-900 dark:text-white">Rp {{ number_format($result['pokok'], 0, ',', '.') }}</div>
                    <div class="text-xs text-slate-500">Sisa: Rp {{ number_format($result['sisa'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ================================================================== --}}
{{-- SELECTED BILLING DETAILS                                            --}}
{{-- ================================================================== --}}
@if($taxData)
<div class="stpd-pop space-y-5">

    {{-- Billing Info Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg stpd-bg-p10 flex items-center justify-center">
                <svg class="w-4 h-4 stpd-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Detail Billing Sumber</h3>
            @php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                    'verified' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                    'expired' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                    'partially_paid' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                ];
            @endphp
            <span class="text-xs font-bold px-2 py-0.5 rounded {{ $statusColors[$taxData['status']] ?? 'bg-gray-100 text-gray-600' }}">
                {{ strtoupper($taxData['status_label']) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm">
            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-500">Billing Sumber</span>
                <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $taxData['billing_code'] }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-500">Jenis Pajak</span>
                <span class="font-semibold text-slate-900 dark:text-white">{{ $taxData['jenis_pajak'] }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-500">Nama Objek</span>
                <span class="font-semibold text-slate-900 dark:text-white">{{ $taxData['nama_objek'] }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-500">NOPD</span>
                <span class="font-semibold text-slate-900 dark:text-white">{{ $taxData['nopd'] }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-500">Masa Pajak</span>
                <span class="font-semibold text-slate-900 dark:text-white">{{ $taxData['masa_pajak'] }}</span>
            </div>
            @if($wpData)
            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-500">Wajib Pajak</span>
                <span class="font-semibold text-slate-900 dark:text-white">{{ $wpData['nama'] }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-500">NPWPD</span>
                <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $wpData['npwpd'] }}</span>
            </div>
            @endif
        </div>

        {{-- Payment Status Summary --}}
        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="rounded-xl stpd-bg-p5 p-3 text-center">
                <div class="text-xs text-slate-500 dark:text-slate-400">Pokok Pajak</div>
                <div class="text-sm font-bold text-slate-900 dark:text-white">Rp {{ number_format($taxData['pokok_pajak'], 0, ',', '.') }}</div>
            </div>
            <div class="rounded-xl {{ $taxData['pokok_belum_dibayar'] > 0 ? 'bg-red-50 dark:bg-red-900/15' : 'bg-green-50 dark:bg-green-900/15' }} p-3 text-center">
                <div class="text-xs text-slate-500 dark:text-slate-400">Pokok Belum Bayar</div>
                <div class="text-sm font-bold {{ $taxData['pokok_belum_dibayar'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                    Rp {{ number_format($taxData['pokok_belum_dibayar'], 0, ',', '.') }}
                </div>
            </div>
            <div class="rounded-xl stpd-bg-p5 p-3 text-center">
                <div class="text-xs text-slate-500 dark:text-slate-400">Sanksi Tercatat</div>
                <div class="text-sm font-bold text-slate-900 dark:text-white">Rp {{ number_format($taxData['sanksi_existing'], 0, ',', '.') }}</div>
            </div>
            <div class="rounded-xl {{ $taxData['sanksi_belum_dibayar'] > 0 ? 'bg-red-50 dark:bg-red-900/15' : 'bg-green-50 dark:bg-green-900/15' }} p-3 text-center">
                <div class="text-xs text-slate-500 dark:text-slate-400">Sanksi Belum Bayar</div>
                <div class="text-sm font-bold {{ $taxData['sanksi_belum_dibayar'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                    Rp {{ number_format($taxData['sanksi_belum_dibayar'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- TIPE STPD SELECTOR                                                  --}}
    {{-- ================================================================== --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">Tipe STPD</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            {{-- Pokok & Sanksi --}}
            <label wire:click="$set('tipeStpd', 'pokok_sanksi')"
                   class="stpd-card rounded-xl border-2 p-4
                          {{ $tipeStpd === 'pokok_sanksi' ? 'stpd-card-selected border-purple-500' : 'border-slate-200 dark:border-slate-700' }}
                          {{ $taxData['is_pokok_lunas'] ? 'opacity-40 cursor-not-allowed' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg {{ $tipeStpd === 'pokok_sanksi' ? 'stpd-bg-primary text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }} flex items-center justify-center transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-sm text-slate-900 dark:text-white">Pokok & Sanksi</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Billing belum dibayar sama sekali + proyeksi sanksi</div>
                    </div>
                </div>
                @if($taxData['is_pokok_lunas'])
                <div class="mt-2 text-xs text-red-500 font-medium">Pokok sudah lunas — pilih "Sanksi Saja"</div>
                @endif
            </label>

            {{-- Sanksi Saja --}}
            <label wire:click="$set('tipeStpd', 'sanksi_saja')"
                   class="stpd-card rounded-xl border-2 p-4
                          {{ $tipeStpd === 'sanksi_saja' ? 'stpd-card-selected border-purple-500' : 'border-slate-200 dark:border-slate-700' }}
                          {{ $taxData['sanksi_belum_dibayar'] <= 0 && $taxData['sanksi_existing'] <= 0 ? 'opacity-40 cursor-not-allowed' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg {{ $tipeStpd === 'sanksi_saja' ? 'stpd-bg-primary text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }} flex items-center justify-center transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-sm text-slate-900 dark:text-white">Sanksi Saja</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Pokok sudah lunas, sanksi belum terbayar</div>
                    </div>
                </div>
                @if($taxData['sanksi_belum_dibayar'] <= 0 && $taxData['sanksi_existing'] <= 0)
                <div class="mt-2 text-xs text-red-500 font-medium">Tidak ada sanksi yang belum dibayar</div>
                @endif
            </label>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- FORM: POKOK & SANKSI (Proyeksi tanggal bayar)                      --}}
    {{-- ================================================================== --}}
    @if($tipeStpd === 'pokok_sanksi')
    <div class="stpd-pop bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">
            <svg class="w-4 h-4 inline stpd-text-primary -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Proyeksi Tanggal Pembayaran
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
            Tentukan proyeksi tanggal pembayaran untuk menghitung sanksi. Sanksi dihitung otomatis berdasarkan keterlambatan dari jatuh tempo.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Proyeksi Tanggal Bayar</label>
                <input type="date" wire:model="proyeksiTanggalBayar"
                       class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 text-sm
                              bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white stpd-ring focus:ring-2 focus:outline-none">
            </div>
            @if($taxData['jatuh_tempo'])
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Jatuh Tempo Asli</label>
                <div class="rounded-xl bg-slate-50 dark:bg-slate-800 px-4 py-2.5 text-sm font-mono text-slate-900 dark:text-white border border-slate-200 dark:border-slate-600">
                    {{ \Carbon\Carbon::parse($taxData['jatuh_tempo'])->translatedFormat('d F Y') }}
                </div>
            </div>
            @endif
        </div>

        {{-- Calculation Result --}}
        @if($hitungSanksi !== null)
        <div class="mt-4 rounded-xl bg-purple-50 dark:bg-purple-900/15 border border-purple-200 dark:border-purple-800/50 p-4">
            <h4 class="text-xs font-bold text-purple-700 dark:text-purple-300 mb-3">HASIL PERHITUNGAN PROYEKSI</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div>
                    <div class="text-xs text-purple-500 dark:text-purple-400">Bulan Terlambat</div>
                    <div class="font-bold text-purple-800 dark:text-purple-200">{{ $hitungBulanTerlambat }} bulan</div>
                </div>
                <div>
                    <div class="text-xs text-purple-500 dark:text-purple-400">Tarif Sanksi</div>
                    <div class="font-bold text-purple-800 dark:text-purple-200">{{ $tarifSanksiPersen }}% / bulan</div>
                </div>
                <div>
                    <div class="text-xs text-purple-500 dark:text-purple-400">Pokok Belum Dibayar</div>
                    <div class="font-bold text-red-600 dark:text-red-400">Rp {{ number_format($hitungPokokBelumDibayar, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-xs text-purple-500 dark:text-purple-400">Sanksi (Proyeksi)</div>
                    <div class="font-bold text-red-600 dark:text-red-400">Rp {{ number_format($hitungSanksi, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ================================================================== --}}
    {{-- FORM: SANKSI SAJA                                                   --}}
    {{-- ================================================================== --}}
    @if($tipeStpd === 'sanksi_saja')
    <div class="stpd-pop bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">
            <svg class="w-4 h-4 inline stpd-text-primary -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Detail Sanksi Belum Dibayar
        </h3>

        <div class="rounded-xl bg-amber-50 dark:bg-amber-900/15 border border-amber-200 dark:border-amber-800/50 p-4">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                <div>
                    <div class="text-xs text-amber-600 dark:text-amber-400">Bulan Terlambat</div>
                    <div class="font-bold text-amber-800 dark:text-amber-200">{{ $hitungBulanTerlambat ?? 0 }} bulan</div>
                </div>
                <div>
                    <div class="text-xs text-amber-600 dark:text-amber-400">Tarif Sanksi</div>
                    <div class="font-bold text-amber-800 dark:text-amber-200">{{ $tarifSanksiPersen ?? 0 }}% / bulan</div>
                </div>
                <div>
                    <div class="text-xs text-amber-600 dark:text-amber-400">Sanksi Belum Terbayar</div>
                    <div class="font-bold text-red-600 dark:text-red-400">Rp {{ number_format($hitungSanksi ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ================================================================== --}}
    {{-- CATATAN PETUGAS                                                     --}}
    {{-- ================================================================== --}}
    @if($tipeStpd)
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Catatan Petugas (Opsional)</label>
        <textarea wire:model.defer="catatanPetugas" rows="3"
                  placeholder="Tambahkan catatan jika diperlukan..."
                  class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-3 text-sm
                         bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white
                         placeholder-slate-400 stpd-ring focus:ring-2 focus:outline-none resize-none"></textarea>
    </div>

    {{-- Submit Button --}}
    <div class="flex justify-end gap-3">
        <button wire:click="buatBaru"
                class="px-6 py-3 rounded-xl text-sm font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                       border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
            Batal
        </button>
        <button wire:click="buatStpd"
                class="px-8 py-3 rounded-xl text-sm font-bold stpd-bg-primary stpd-bg-ph text-white transition-all stpd-shadow">
            <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Buat Draft STPD
        </button>
    </div>
    @endif

</div>
@endif

@endif
</div>

</x-filament-panels::page>
