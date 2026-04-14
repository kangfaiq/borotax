<x-filament-panels::page>

<style>
    .pb-card {
        background: #fff; border-radius: 16px; padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -2px rgba(0,0,0,.05);
        border: 1px solid #f1f5f9;
    }
    .dark .pb-card {
        background: #1e293b; border-color: #334155;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,.3), 0 2px 4px -2px rgba(0,0,0,.3);
    }
    .pb-label { font-size: 13px; font-weight: 500; color: #64748b; margin-bottom: 4px; }
    .dark .pb-label { color: #94a3b8; }
    .pb-value { font-size: 15px; font-weight: 600; color: #0f172a; }
    .dark .pb-value { color: #f8fafc; }

    .pb-input {
        width: 100%; padding: 10px 14px; border-radius: 8px; font-size: 14px;
        border: 1px solid #cbd5e1; background-color: #fff; color: #0f172a;
        transition: all .2s; outline: none;
    }
    .pb-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
    .dark .pb-input {
        background-color: #0f172a; border-color: #475569; color: #f8fafc;
    }
    .dark .pb-input:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(96,165,250,.15); }

    .pb-btn {
        padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600;
        display: inline-flex; items-center gap: 8px; transition: all .2s; cursor: pointer;
    }
    .pb-btn-primary {
        background: #3b82f6; color: white; border: none;
    }
    .pb-btn-primary:hover { background: #2563eb; }
    .pb-btn-success {
        background: #10b981; color: white; border: none;
    }
    .pb-btn-success:hover { background: #059669; }

    .pb-list-item {
        padding: 12px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        transition: all .2s; cursor: pointer; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
    }
    .pb-list-item:hover { border-color: #60a5fa; box-shadow: 0 0 0 1px #60a5fa; }
    
    .dark .pb-list-item {
        background-color: #1e293b; border-color: #334155;
    }

    .pb-total-box {
        padding: 16px; border-radius: 12px;
        background-color: #0f172a; color: #ffffff;
        display: flex; justify-content: space-between; align-items: center;
        box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);
    }
    .dark .pb-total-box {
        background-color: #020617;
    }
</style>

<div class="space-y-6">

    {{-- Page header --}}
    <div>
        <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Lunas Bayar Manual</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Konfirmasi pelunasan tagihan pajak self-assessment secara manual.</p>
    </div>

    @if (\Illuminate\Support\Facades\Auth::user()->role !== 'admin')
        <div class="p-4 bg-red-50 text-red-700 rounded-xl dark:bg-red-900/20 dark:text-red-400 border border-red-200 dark:border-red-800">
            Akses Ditolak: Halaman ini hanya untuk Administrator.
        </div>
    @else

        @if($successData)
            {{-- Success Panel --}}
            <div class="pb-card border-l-4 border-l-emerald-500">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pelunasan Berhasil</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Kode Pembayaran Aktif: <strong class="text-emerald-600 dark:text-emerald-400">{{ $successData['billing_code'] }}</strong></p>
                        @if(!empty($successData['has_stpd_alias']))
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Billing Sumber: <strong>{{ $successData['source_billing_code'] }}</strong> • Alias STPD manual: <strong>{{ $successData['stpd_payment_code'] }}</strong></p>
                        @endif
                        @if(!$successData['is_lunas'])
                            <p class="text-xs text-amber-600 font-medium mt-1">Status: {{ $successData['keterangan_status'] }}</p>
                        @endif
                    </div>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl mb-6">
                    <div>
                        <div class="pb-label">Obyek / WP</div>
                        <div class="pb-value">{{ $successData['nama_wp'] }}</div>
                    </div>
                    <div>
                        <div class="pb-label">Masa Pajak</div>
                        <div class="pb-value">{{ $successData['masa_pajak'] }}</div>
                    </div>
                    <div>
                        <div class="pb-label">Sisa Tagihan Sekarang</div>
                        @if($successData['is_lunas'])
                            <div class="pb-value text-emerald-600 dark:text-emerald-400">Rp 0 (LUNAS)</div>
                        @else
                            <div class="pb-value text-amber-600 dark:text-amber-400">Rp {{ number_format($successData['sisa_tagihan'], 0, ',', '.') }}</div>
                        @endif
                    </div>
                    <div>
                        <div class="pb-label">Referensi / Bukti</div>
                        <div class="pb-value">{{ $successData['referensi'] }}</div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button wire:click="resetForm" class="pb-btn pb-btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Lakukan Pelunasan Lain
                    </button>
                    @if($successData['is_lunas'])
                    <a href="{{ route('portal.sptpd.show', $successData['tax_id']) }}" target="_blank" class="pb-btn" style="background: #f1f5f9; color: #334155;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Cetak Doc SPTPD
                    </a>
                    @endif
                </div>
            </div>
        @else

            {{-- Info banner --}}
            <div class="flex items-start gap-3 bg-blue-50 dark:bg-blue-900/15 border border-blue-200 dark:border-blue-800/50 rounded-xl px-4 py-3 text-sm text-blue-700 dark:text-blue-300">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <p><strong>Mode Administrator:</strong> Gunakan form ini untuk mencatat pembayaran pajak yang dilakukan di luar sistem (misal: setoran tunai atau transfer manual). Sistem akan langsung menerbitkan SPTPD setelah pembayaran dicatat.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- Kiri: Cari Billing & Ringkasan --}}
                <div class="lg:col-span-5 space-y-6">
                    <div class="pb-card">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Cari Tagihan</h3>
                        
                        <form wire:submit.prevent="searchBilling">
                            <div class="mb-4">
                                <label class="pb-label block">Kode Pembayaran Aktif / NPWPD <span class="text-red-500">*</span></label>
                                <div class="flex gap-2">
                                    <input type="text" wire:model.defer="searchBillingCode" class="pb-input flex-1 font-mono text-base" placeholder="Contoh Kode Pembayaran Aktif / Billing Sumber (18 digit) / NPWPD (13 digit)" required minlength="13" maxlength="18">
                                    <button type="submit" class="pb-btn pb-btn-primary">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if(count($foundTaxesList) > 0)
                    <div class="pb-card bg-slate-50 dark:bg-slate-800/30 border-dashed">
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white mb-3 flex items-center justify-between">
                            Daftar Tagihan Aktif
                            <span class="bg-blue-100 text-blue-700 text-xs py-0.5 px-2 rounded-full">{{ count($foundTaxesList) }} Ditemukan</span>
                        </h3>
                        <div class="space-y-2 border-t border-slate-200 dark:border-slate-700 pt-3">
                            @foreach($foundTaxesList as $taxOption)
                            <div wire:click="selectTaxToPay('{{ $taxOption['id'] }}')" class="pb-list-item">
                                <div>
                                    <div class="font-bold text-sm text-slate-900 dark:text-white tracking-tight">{{ $taxOption['billing_code'] }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">{{ str($taxOption['nama_objek'])->limit(25) }} • {{ $taxOption['masa_pajak'] }}</div>
                                    @if(!empty($taxOption['has_stpd_alias']))
                                        <div class="text-[11px] text-amber-600 dark:text-amber-400 mt-1">Billing Sumber: {{ $taxOption['source_billing_code'] }} • Alias STPD manual aktif</div>
                                    @endif
                                </div>
                                <div class="text-right flex flex-col items-end">
                                    <div class="text-sm font-black text-amber-600 dark:text-amber-500">Rp {{ number_format($taxOption['sisa_tagihan'], 0, ',', '.') }}</div>
                                    <span class="mt-1 px-1.5 py-0.5 text-[10px] uppercase font-bold rounded {{ $taxOption['status'] === 'expired' ? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200' : ($taxOption['status'] === 'partially_paid' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400') }}">
                                        {{ $taxOption['status_label'] }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($taxDetails)
                    <div class="pb-card bg-slate-50 dark:bg-slate-800/50 border-0">
                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-200 dark:border-slate-700">
                            <h3 class="font-bold text-slate-900 dark:text-white">Detail Tagihan</h3>
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $taxDetails['status'] === 'expired' ? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600' : ($taxDetails['status'] === 'partially_paid' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 border border-blue-200 dark:border-blue-800/50' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50') }}">
                                {{ strtoupper($taxDetails['status_label']) }}
                            </span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <div class="pb-label">Kode Pembayaran Aktif</div>
                                <div class="pb-value font-mono">{{ $taxDetails['billing_code'] }}</div>
                                @if(!empty($taxDetails['has_stpd_alias']))
                                    <div class="text-xs text-slate-500 mt-0.5">Billing Sumber: {{ $taxDetails['source_billing_code'] }} • Alias STPD manual sanksi saja</div>
                                @endif
                            </div>

                            <div>
                                <div class="pb-label">Wajib Pajak</div>
                                <div class="pb-value">{{ $taxDetails['nama_wp'] }}</div>
                                <div class="text-xs text-slate-500 font-mono mt-0.5">NPWPD: {{ $taxDetails['npwpd'] }}</div>
                            </div>
                            
                            <div>
                                <div class="pb-label">Objek Pajak</div>
                                <div class="pb-value">{{ $taxDetails['nama_objek'] }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $taxDetails['alamat_objek'] }}</div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="pb-label">Jenis Pajak</div>
                                    <div class="pb-value">{{ $taxDetails['jenis_pajak'] }}</div>
                                </div>
                                <div>
                                    <div class="pb-label">Masa Pajak</div>
                                    <div class="pb-value">{{ $taxDetails['masa_pajak'] }}</div>
                                </div>
                            </div>

                            <div class="pt-3 mt-3 border-t border-slate-200 dark:border-slate-700 bg-slate-100/50 dark:bg-slate-900/50 p-3 rounded-xl space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600 dark:text-slate-400">Total Tagihan Awal</span>
                                    <strong class="text-slate-900 dark:text-white">Rp {{ number_format($taxDetails['total_tagihan'], 0, ',', '.') }}</strong>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600 dark:text-slate-400">Total Sudah Dibayar</span>
                                    <strong class="text-emerald-500">Rp {{ number_format($taxDetails['total_dibayar'], 0, ',', '.') }}</strong>
                                </div>
                                <div class="flex justify-between items-center text-lg pt-2 border-t border-slate-200 dark:border-slate-700">
                                    <span class="font-bold text-slate-900 dark:text-white">Sisa Tagihan</span>
                                    <strong class="text-red-500 font-bold">Rp {{ number_format($taxDetails['sisa_tagihan'], 0, ',', '.') }}</strong>
                                </div>
                            </div>
                            
                            <div class="pt-3 mt-1">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm text-slate-600 dark:text-slate-400">Jatuh Tempo Berakhir</span>
                                    <strong class="{{ \Carbon\Carbon::createFromFormat('d/m/Y', $taxDetails['jatuh_tempo'], 'Asia/Jakarta')->isPast() ? 'text-red-500' : 'text-slate-900 dark:text-white' }}">{{ $taxDetails['jatuh_tempo'] }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Kanan: Form input pembayaran --}}
                <div class="lg:col-span-7">
                    @if($taxDetails)
                    <div class="pb-card relative overflow-hidden">
                        {{-- Decorative background element --}}
                        <div class="absolute top-0 right-0 -mt-16 -mr-16 text-slate-50 dark:text-slate-800/30 opacity-50 transform rotate-12 pointer-events-none">
                            <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        </div>

                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Formulir Pelunasan Manual
                        </h3>

                        <form wire:submit.prevent="lunaskanBilling" class="relative z-10 space-y-6">
                            
                            {{-- Input Nominal --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="pb-label block">Pokok Pajak (Rp) <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-slate-500 font-medium text-sm">Rp</span>
                                        </div>
                                        <input type="number" wire:model.live="inputPokokPajak" class="pb-input font-mono font-bold" style="padding-left: 24px;" min="0" step="1" required>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">Sistem menyarankan: Rp {{ number_format($taxDetails['pokok_asli'], 0, ',', '.') }}</p>
                                </div>
                                
                                <div>
                                    <label class="pb-label block">Sanksi / Denda (Rp)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-slate-500 font-medium text-sm">Rp</span>
                                        </div>
                                        <input type="number" wire:model.live="inputSanksiPajak" class="pb-input font-mono font-bold" style="padding-left: 24px;" min="0" step="1">
                                    </div>
                                    @if(isset($taxDetails['sanksi_rekomen']) && $taxDetails['sanksi_rekomen'] > 0)
                                        <p class="text-xs text-red-500 mt-1 font-bold flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            Estimasi Denda (Telat {{ $taxDetails['bulan_terlambat'] }} bln): Rp {{ number_format($taxDetails['sanksi_rekomen'], 0, ',', '.') }}
                                        </p>
                                    @else
                                        <p class="text-xs text-slate-500 mt-1">Tercatat sebelumnya: Rp {{ number_format($taxDetails['sanksi_asli'], 0, ',', '.') }}</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Info Total --}}
                            <div class="pb-total-box">
                                <span style="font-size: 14px; font-weight: 500; color: #cbd5e1;">Total Nominal Pembayaran</span>
                                <span style="font-size: 24px; font-weight: 900; color: #34d399;">
                                    Rp {{ number_format(floatval($inputPokokPajak) + floatval($inputSanksiPajak), 0, ',', '.') }}
                                </span>
                            </div>

                            {{-- Input Meta --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="pb-label block">Tanggal Pembayaran <span class="text-red-500">*</span></label>
                                    <input type="datetime-local" wire:model.live.debounce.300ms="inputTanggalBayar" class="pb-input" required>
                                </div>
                                
                                <div>
                                    <label class="pb-label block">Lokasi Pembayaran <span class="text-red-500">*</span></label>
                                    <select wire:model.defer="inputLokasiPembayaran" class="pb-input" required>
                                        <option value="MANUAL">Transfer Langsung RKUD (Manual)</option>
                                        <option value="BJATIM">Teller/Mobile Bank Jatim</option>
                                        <option value="QRISBJATIM">QRIS Bank Jatim</option>
                                        <option value="TOKOPEDIA">Tokopedia</option>
                                        <option value="ALFAMART">Alfamart</option>
                                        <option value="INDOMARET">Indomaret</option>
                                        <option value="BNI">Bank BNI</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="pb-label block">Referensi Bukti Bayar <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.defer="inputReferensiBayar" class="pb-input" placeholder="Misal: Nomor kuitansi, struk BPD, dll" required>
                                </div>

                                <div class="md:col-span-2" x-data="imageCompressor()">
                                    <label class="pb-label block">Unggah Bukti Fisik (Wajib) <span class="text-red-500">*</span></label>
                                    <input type="file" @change="handleFile" id="buktiBayarInput" class="pb-input w-full cursor-pointer bg-slate-50 dark:bg-slate-800" accept="image/*,.pdf" required>
                                    
                                    <div x-show="isCompressing" style="display: none;" class="text-xs text-blue-500 mt-2 flex items-center gap-1.5 font-medium">
                                        <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Sedang memproses & kompresi gambar otomatis...
                                    </div>
                                    
                                    <p x-show="!isCompressing" class="text-xs text-slate-500 mt-1">Format didukung: JPG, PNG, PDF (Otomatis kompresi maks 1MB)</p>
                                    @error('inputBuktiBayar') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            {{-- Warning Confirm --}}
                            <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
                                <button type="button" wire:click="resetForm" class="pb-btn" style="background: transparent; color: #64748b; border: 1px solid #cbd5e1;">Batal</button>
                                
                                {{-- Trigger modal for confirm, directly submit for demo --}}
                                <button type="submit" onclick="return confirm('Apakah Anda yakin data pembayaran sudah benar? Tindakan pelunasan ini akan langsung menerbitkan SPTPD jika lunas penuh.')" class="pb-btn pb-btn-success">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Konfirmasi Pembayaran
                                </button>
                            </div>

                        </form>
                    </div>
                    @else
                    <div class="h-full min-h-[400px] flex flex-col items-center justify-center text-center p-8 pb-card border-dashed border-2 bg-slate-50 dark:bg-slate-800/30">
                        <div class="w-20 h-20 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500 mb-4">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h4 class="text-lg font-bold text-slate-700 dark:text-slate-300">Belum Ada Tagihan Terpilih</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mt-2">Masukkan Kode Pembayaran Aktif atau Billing Sumber pada form pencarian di sebelah kiri untuk menampilkan detail tagihan dan form pelunasan manual.</p>
                    </div>
                    @endif
                </div>

            </div>

        @endif

    @endif

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('imageCompressor', () => ({
            isCompressing: false,
            
            async handleFile(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Max size in bytes (1MB)
                const maxSize = 1 * 1024 * 1024;
                
                // If it's not an image or already under 1MB, upload directly
                if (!file.type.startsWith('image/') || file.size <= maxSize) {
                    this.$wire.upload('inputBuktiBayar', file);
                    return;
                }

                this.isCompressing = true;
                
                try {
                    const compressedFile = await this.compressImage(file, maxSize);
                    // Upload via Livewire
                    this.$wire.upload('inputBuktiBayar', compressedFile, () => {
                        this.isCompressing = false;
                    }, () => {
                        this.isCompressing = false;
                        alert('Gagal mengunggah file.');
                    });
                } catch (error) {
                    console.error('Compression error:', error);
                    // Fallback to original file if compression fails
                    this.$wire.upload('inputBuktiBayar', file);
                    this.isCompressing = false;
                }
            },
            
            compressImage(file, maxSize) {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onload = event => {
                        const img = new Image();
                        img.src = event.target.result;
                        img.onload = () => {
                            const canvas = document.createElement('canvas');
                            let width = img.width;
                            let height = img.height;
                            
                            // Scale down if too large (max 1920x1080 bounds roughly)
                            const max_dim = 1600;
                            if (width > max_dim || height > max_dim) {
                                if (width > height) {
                                    height = Math.round((height *= max_dim / width));
                                    width = max_dim;
                                } else {
                                    width = Math.round((width *= max_dim / height));
                                    height = max_dim;
                                }
                            }
                            
                            canvas.width = width;
                            canvas.height = height;
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, width, height);
                            
                            // Iteratively reduce quality to hit target size constraint
                            let quality = 0.8;
                            let dataUrl = canvas.toDataURL('image/jpeg', quality);
                            
                            // Approximate byte size logic base64 string
                            let getFileSize = (b64) => Math.round((b64.length * 3) / 4);
                            
                            while (getFileSize(dataUrl) > maxSize && quality > 0.1) {
                                quality -= 0.1;
                                dataUrl = canvas.toDataURL('image/jpeg', quality);
                            }
                            
                            // Convert back to file / blob object
                            fetch(dataUrl)
                                .then(res => res.blob())
                                .then(blob => {
                                    const cFile = new File([blob], file.name, {
                                        type: 'image/jpeg',
                                        lastModified: Date.now()
                                    });
                                    resolve(cFile);
                                })
                                .catch(reject);
                        };
                        img.onerror = reject;
                    };
                    reader.onerror = reject;
                });
            }
        }));
    });
</script>

    {{-- Modal Konfirmasi Kurang/Lebih Bayar --}}
    @if($isConfirmingPayment)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200 dark:border-slate-700 mt-20">
            <div class="p-6">
                <div class="w-12 h-12 rounded-full {{ $paymentConfirmationType == 'lebih' ? 'bg-amber-100 dark:bg-amber-900/30' : 'bg-blue-100 dark:bg-blue-900/30' }} flex items-center justify-center mb-4">
                    @if($paymentConfirmationType == 'lebih')
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    @else
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    @endif
                </div>

                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
                    Konfirmasi {{ $paymentConfirmationType == 'lebih' ? 'Lebih Bayar' : 'Pembayaran Sebagian' }}
                </h3>
                
                <p class="text-slate-600 dark:text-slate-300 text-sm mb-4 leading-relaxed">
                    @if($paymentConfirmationType == 'lebih')
                        Nominal pembayaran yang Anda masukkan <strong class="text-amber-600 dark:text-amber-400">melebihi sisa tagihan</strong>. Apakah Anda yakin ingin memproses transaksi ini? Pembayaran tetap akan disimpan namun status menjadi Lunas.
                    @else
                        Nominal yang dibayarkan <strong class="text-blue-600 dark:text-blue-400">kurang dari total tagihan</strong>. Transaksi ini akan dicatat sebagai <strong>Cicilan Pembayaran</strong> dan status tagihan belum akan dianggap lunas sepenuhnya.
                    @endif
                </p>

                <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 text-sm border border-slate-100 dark:border-slate-800 mb-6 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Sisa Tagihan Sebelumnya:</span>
                        <strong class="text-slate-900 dark:text-white">Rp {{ number_format($taxDetails['sisa_tagihan'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Total Dimasukkan:</span>
                        <strong class="{{ $paymentConfirmationType == 'lebih' ? 'text-amber-600 dark:text-amber-400' : 'text-blue-600 dark:text-blue-400' }}">Rp {{ number_format(floatval($inputPokokPajak) + floatval($inputSanksiPajak), 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" wire:click="cancelPaymentConfirmation" class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 rounded-lg font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors focus:ring-2 focus:ring-slate-200">
                        Batal
                    </button>
                    <button type="button" wire:click="executeLunaskanBilling" class="{{ $paymentConfirmationType == 'lebih' ? 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500/50' : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500/50' }} flex-1 px-4 py-2 text-white rounded-lg font-medium transition-colors focus:ring-2">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</x-filament-panels::page>
