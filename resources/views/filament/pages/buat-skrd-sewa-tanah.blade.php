<x-filament-panels::page>

<style>
    :root {
        --skrd-primary: #0f766e; --skrd-primary-h: #115e59;
        --skrd-primary-5: rgba(15,118,110,.05);  --skrd-primary-10: rgba(15,118,110,.10);
        --skrd-primary-20: rgba(15,118,110,.20); --skrd-primary-30: rgba(15,118,110,.30);
        --skrd-shadow: rgba(15,118,110,.20);
    }
    .dark {
        --skrd-primary: #2dd4bf; --skrd-primary-h: #14b8a6;
        --skrd-primary-5: rgba(45,212,191,.10);  --skrd-primary-10: rgba(45,212,191,.18);
        --skrd-primary-20: rgba(45,212,191,.28); --skrd-primary-30: rgba(45,212,191,.38);
        --skrd-shadow: rgba(45,212,191,.28);
    }
    .skrd-page .text-primary { color: var(--skrd-primary); }
    .skrd-page .bg-primary { background-color: var(--skrd-primary); }
    .skrd-page .bg-primary-hover:hover { background-color: var(--skrd-primary-h); }
    .skrd-page .bg-primary-soft { background-color: var(--skrd-primary-5); }
    .skrd-page .border-primary-soft { border-color: var(--skrd-primary-20); }
    .skrd-page .ring-primary:focus { --tw-ring-color: var(--skrd-primary); border-color: var(--skrd-primary); }
    .skrd-card { transition: all .18s; cursor: pointer; position: relative; overflow: hidden; }
    .skrd-card::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: transparent; transition: background .18s; border-radius: 0 3px 3px 0; }
    .skrd-card:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(15,118,110,.10); border-color: #5eead4 !important; }
    .skrd-card:hover::before, .skrd-card-selected::before { background: var(--skrd-primary); }
    .skrd-card-selected { border-color: var(--skrd-primary) !important; background: var(--skrd-primary-5) !important; }
    .skrd-avatar { width: 2.1rem; height: 2.1rem; border-radius: .45rem; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; color: #fff; flex-shrink: 0; background: linear-gradient(135deg, #14b8a6, #0f766e); }
    .skrd-badge { padding: .1rem .45rem; border-radius: .3rem; font-size: .58rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .skrd-badge-info { background: #ccfbf1; color: #115e59; }
    .skrd-badge-muted { background: #e2e8f0; color: #475569; }
    .dark .skrd-badge-info { background: rgba(45,212,191,.16); color: #99f6e4; }
    .dark .skrd-badge-muted { background: rgba(148,163,184,.15); color: #cbd5e1; }
    .dark .skrd-page .bg-white { background-color: #0f172a; }
    .dark .skrd-page .bg-slate-50 { background-color: #1e293b; }
    .dark .skrd-page .border-slate-200 { border-color: #334155; }
    .dark .skrd-page .border-slate-100 { border-color: #1e293b; }
    .dark .skrd-page input[type="text"],
    .dark .skrd-page input[type="number"],
    .dark .skrd-page input[type="date"],
    .dark .skrd-page select,
    .dark .skrd-page textarea { background-color: #1e293b; }
</style>

<div class="skrd-page py-2">
    <div class="mb-5">
        <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Buat SKRD Sewa Tanah</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pilih objek retribusi, atur parameter perhitungan, lalu simpan draft SKRD.</p>
    </div>

    <form wire:submit="simpanDraft" class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">1. Cari Objek Retribusi</x-slot>
            <x-slot name="description">Cari berdasarkan NOPD, NPWPD, nama objek, atau nama pemilik.</x-slot>

            <div class="relative mb-4">
                <input type="text"
                    wire:model.live.debounce.300ms="searchObjekKeyword"
                    placeholder="NOPD, NPWPD, nama objek, atau nama pemilik..."
                    class="ring-primary block w-full pl-11 pr-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-sm">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
            </div>

            @php
                $objekOptions = $this->getFilteredObjekRetribusiOptions();
            @endphp

            <div class="flex items-center gap-2 mb-2 px-1">
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ count($objekOptions) }} Objek</span>
            </div>

            @if(count($objekOptions) > 0)
                <div class="space-y-2 max-h-[28rem] overflow-y-auto pr-0.5">
                    @foreach($objekOptions as $objek)
                        @php
                            $isSelected = $objekRetribusiId === $objek['id'];
                            $initial = strtoupper(substr($objek['nama_objek'] ?? '?', 0, 1));
                            $subJenisNama = $objek['sub_jenis_nama'] ?? null;
                        @endphp
                        <div wire:click="selectObjekRetribusi('{{ $objek['id'] }}')"
                            class="skrd-card bg-white dark:bg-slate-900 border rounded-xl p-3 {{ $isSelected ? 'skrd-card-selected border-teal-500 dark:border-teal-400' : 'border-slate-200 dark:border-slate-800' }}">
                            <div class="flex items-center gap-3">
                                <div class="skrd-avatar">{{ $initial }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-semibold text-slate-900 dark:text-white text-sm truncate max-w-[16rem]">{{ $objek['nama_objek'] }}</span>
                                        @if($subJenisNama)
                                            <span class="skrd-badge skrd-badge-info">{{ $subJenisNama }}</span>
                                        @endif
                                        <span class="skrd-badge skrd-badge-muted">NOPD {{ $objek['nopd'] }}</span>
                                        @if($isSelected)
                                            <span class="skrd-badge ml-auto skrd-badge-info">Dipilih</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $objek['npwpd'] ?: '-' }} &middot; {{ $objek['nama_pemilik'] ?: '-' }}</p>
                                    <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700 grid grid-cols-1 md:grid-cols-2 gap-x-3 gap-y-0.5 text-[11px]">
                                        <div><span class="text-slate-500 dark:text-slate-400">Alamat:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ str()->limit($objek['alamat_objek'] ?: '-', 55) }}</span></div>
                                        <div><span class="text-slate-500 dark:text-slate-400">Luas:</span> <span class="font-medium text-slate-700 dark:text-slate-300">{{ number_format($objek['luas_m2'], 2, ',', '.') }} m²</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 text-center">
                    <svg class="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Objek retribusi tidak ditemukan</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Coba kata kunci lain atau buat objek baru dari menu pendaftaran.</p>
                </div>
            @endif

            @error('objekRetribusiId')
                <p class="text-sm text-danger-600 mt-3">{{ $message }}</p>
            @enderror
        </x-filament::section>

        @if($objekRetribusiId)
            <x-filament::section>
                <x-slot name="heading">2. Data Wajib Bayar &amp; Objek Retribusi</x-slot>

                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-teal-100 dark:bg-teal-900/30">
                            <x-heroicon-s-check-circle class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                        </div>
                        <h4 class="font-semibold text-teal-700 dark:text-teal-300 text-base">Objek Retribusi Dipilih</h4>
                    </div>
                    <button type="button" wire:click="clearObjekRetribusiSelection"
                        class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center gap-1.5 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Wajib Bayar</p>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-user class="w-4 h-4 text-slate-400 shrink-0" />
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $namaWajibPajak ?: '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-identification class="w-4 h-4 text-slate-400 shrink-0" />
                                <span class="text-sm font-mono text-slate-700 dark:text-slate-300">{{ $nikWajibPajak ?: '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-document-text class="w-4 h-4 text-slate-400 shrink-0" />
                                <span class="text-sm font-mono text-slate-700 dark:text-slate-300">{{ $npwpd ?: '-' }}</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-map-pin class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                                <span class="text-sm text-slate-700 dark:text-slate-300">{{ $alamatWajibPajak ?: '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Objek Retribusi</p>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $namaObjek ?: '-' }}</span>
                                <span class="skrd-badge skrd-badge-info">Luas {{ number_format((float) $luasM2, 2, ',', '.') }} m²</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-map-pin class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                                <span class="text-sm text-slate-700 dark:text-slate-300">{{ $alamatObjek ?: '-' }}</span>
                            </div>
                            <div class="text-sm text-slate-600 dark:text-slate-400">
                                Tarif reklame acuan:
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ number_format($tarifPajakPersen, 0) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">3. Perhitungan SKRD Sewa Tanah</x-slot>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                Sub Jenis Retribusi
                            </label>
                            <input type="text"
                                value="{{ $subJenisPajakNama ?? '-' }}"
                                readonly
                                class="block w-full py-2 px-3 border border-dashed border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Sub jenis selalu mengikuti objek retribusi yang dipilih.</p>

                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-3 py-2">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tarif Aktif</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                                        @if($previewTarif)
                                            Rp {{ number_format($previewTarif, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">{{ $previewSatuanLabel ?: 'Satuan waktu belum tersedia' }}</p>
                                </div>

                                <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-3 py-2">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Masa Tarif Sub Jenis</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ $previewTarifMasa ?: '-' }}</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Dipakai untuk menghitung tarif pada tanggal mulai yang dipilih.</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                    Jumlah Reklame
                                </label>
                                <input type="number" wire:model.live="jumlahReklame" min="1"
                                    class="ring-primary block w-full py-2 px-3 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                                @error('jumlahReklame')
                                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                    Durasi
                                </label>
                                <input type="number" wire:model.live="durasi" min="1"
                                    class="ring-primary block w-full py-2 px-3 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                                @if($previewSatuanLabel)
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Satuan waktu: {{ $previewSatuanLabel }}</p>
                                @endif
                                @error('durasi')
                                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                    Masa Berlaku Mulai
                                </label>
                                <input type="date" wire:model.live="masaBerlakuMulai"
                                    class="ring-primary block w-full py-2 px-3 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                                @error('masaBerlakuMulai')
                                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-500 mb-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    Masa Berlaku Sampai
                                </label>
                                <input type="date" wire:model="masaBerlakuSampai" readonly
                                    class="block w-full py-2 px-3 border border-dashed border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Tanggal ini dihitung otomatis dari tanggal mulai, durasi, dan satuan masa sub jenis.</p>
                                @error('masaBerlakuSampai')
                                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 h-fit">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Preview Perhitungan</p>
                                <h4 class="text-base font-bold text-slate-900 dark:text-white mt-1">Formula SKRD Sewa Tanah</h4>
                            </div>
                            <span class="skrd-badge skrd-badge-info">{{ number_format($tarifPajakPersen, 0) }}%</span>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500 dark:text-slate-400">Luas</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ number_format((float) $luasM2, 2, ',', '.') }} m²</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500 dark:text-slate-400">Jumlah Reklame</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $jumlahReklame ?: 0 }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500 dark:text-slate-400">Harga Sub Jenis</span>
                                <span class="font-medium text-slate-900 dark:text-white">
                                    @if($previewTarif)
                                        Rp {{ number_format($previewTarif, 0, ',', '.') }} {{ $previewSatuanLabel }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500 dark:text-slate-400">Tarif Retribusi</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ number_format($tarifPajakPersen, 0) }}%</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500 dark:text-slate-400">Durasi</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $durasi ?: 0 }} {{ $previewSatuanLabel ?: '' }}</span>
                            </div>

                            <div class="rounded-xl border border-dashed border-primary-soft bg-primary-soft px-4 py-3">
                                <p class="text-[11px] font-semibold text-primary mb-1">Rumus</p>
                                <p class="text-sm text-slate-700 dark:text-slate-200">Luas m² × Jumlah Reklame × Harga Sub Jenis × Tarif Retribusi × Durasi</p>
                            </div>

                            <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-between gap-4 items-center">
                                <span class="text-base font-bold text-slate-900 dark:text-white">Total Retribusi</span>
                                <span class="text-xl font-black text-primary">Rp {{ number_format((float) ($previewJumlahRetribusi ?? 0), 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif

        <div class="flex justify-end">
            <button type="submit"
                @disabled(! $objekRetribusiId || ! $subJenisPajakId || ! $previewTarif)
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white bg-primary bg-primary-hover disabled:opacity-50 disabled:cursor-not-allowed transition-shadow shadow-sm hover:shadow-md">
                <x-heroicon-m-document-check class="w-5 h-5" />
                Simpan Draft SKRD
            </button>
        </div>
    </form>
</div>

</x-filament-panels::page>
