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
        padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
        display: inline-flex; items-center gap: 6px; transition: all .2s; cursor: pointer;
    }
    .pb-btn-primary { background: #3b82f6; color: white; border: none; }
    .pb-btn-primary:hover { background: #2563eb; }
    .pb-btn-danger { background: #ef4444; color: white; border: none; }
    .pb-btn-danger:hover { background: #dc2626; }

    .pb-list-item {
        padding: 12px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        transition: all .2s; cursor: pointer; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
    }
    .pb-list-item:hover { border-color: #f87171; box-shadow: 0 0 0 1px #f87171; }
    
    .dark .pb-list-item {
        background-color: #1e293b; border-color: #334155;
    }
</style>

<div class="space-y-6">

    {{-- Page header --}}
    <div>
        <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Pembatalan Pembayaran (Batal Bayar)</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Gunakan fitur ini untuk membatalkan riwayat pembayaran yang salah input (Human Error).</p>
    </div>

    @if (\Illuminate\Support\Facades\Auth::user()->role !== 'admin')
        <div class="p-4 bg-red-50 text-red-700 rounded-xl dark:bg-red-900/20 dark:text-red-400 border border-red-200 dark:border-red-800">
            Akses Ditolak: Halaman ini hanya untuk Administrator.
        </div>
    @else

        {{-- Info banner --}}
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-900/15 border border-red-200 dark:border-red-800/50 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-300">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <p><strong>Perhatian:</strong> Membatalkan pembayaran akan menghapus nominal tersebut dari pembukuan, mengubah status tagihan kembali menjadi Pending/Sebagian, dan membatalkan SPTPD/STPD yang terkait jika saldo tidak mencukupi.</p>
                <p class="mt-2"><strong>Catatan kode bayar:</strong> Untuk STPD manual tipe <strong>sanksi saja</strong>, operator dapat mencari dengan alias pembayaran <strong>77</strong>. Halaman ini akan tetap menampilkan <strong>Billing Sumber</strong> dan <strong>Kode Pembayaran Aktif</strong> secara terpisah.</p>
            </div>
        </div>

        <div class="pb-card max-w-xl mb-6">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Cari Tagihan Lunas / Sebagian</h3>
            
            <form wire:submit.prevent="searchBilling">
                <div class="mb-4">
                    <label class="pb-label block">Kode Pembayaran Aktif / NPWPD <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" wire:model.defer="searchBillingCode" class="pb-input flex-1 font-mono text-base" placeholder="Contoh Kode Pembayaran Aktif / Billing Sumber / alias STPD 77 (18 digit) / NPWPD (13 digit)" required minlength="13" maxlength="18">
                        <button type="submit" class="pb-btn pb-btn-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if(count($foundTaxesList) > 0)
        <div class="pb-card max-w-xl mb-6 bg-slate-50 dark:bg-slate-800/30 border-dashed">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white mb-3 flex items-center justify-between">
                Daftar Tagihan dengan Riwayat Pembayaran
                <span class="bg-blue-100 text-blue-700 text-xs py-0.5 px-2 rounded-full">{{ count($foundTaxesList) }} Ditemukan</span>
            </h3>
            <div class="space-y-2 border-t border-slate-200 dark:border-slate-700 pt-3">
                @foreach($foundTaxesList as $taxOption)
                <div wire:click="selectTaxToCancel('{{ $taxOption['id'] }}')" class="pb-list-item">
                    <div>
                        <div class="font-bold text-sm text-slate-900 dark:text-white tracking-tight">{{ $taxOption['billing_code'] }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">{{ str($taxOption['nama_objek'])->limit(25) }} • {{ $taxOption['masa_pajak'] }}</div>
                        @if(!empty($taxOption['has_stpd_alias']))
                            <div class="text-[11px] text-amber-600 dark:text-amber-400 mt-1">Billing Sumber: {{ $taxOption['source_billing_code'] }} • Alias STPD manual aktif</div>
                        @endif
                    </div>
                    <div class="text-right flex flex-col items-end">
                        <div class="text-sm font-black text-emerald-600 dark:text-emerald-500">Masuk: Rp {{ number_format($taxOption['total_dibayar'], 0, ',', '.') }}</div>
                        <span class="mt-1 px-1.5 py-0.5 text-[10px] uppercase font-bold rounded {{ $taxOption['status'] === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : ($taxOption['status'] === 'expired' ? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400') }}">
                            {{ strtoupper($taxOption['status_label']) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($taxDetails)
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Kiri: Detail Obyek Pajak --}}
            <div class="lg:col-span-4">
                <div class="pb-card h-full">
                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="font-bold text-slate-900 dark:text-white">Ringkasan Tagihan</h3>
                        <span class="px-2.5 py-1 text-xs font-bold rounded-full 
                            {{ $taxDetails['status'] === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : ($taxDetails['status'] === 'expired' ? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400') }} border border-slate-200 dark:border-slate-800/50">
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
                            <div class="pb-label">Obyek Pajak</div>
                            <div class="pb-value">{{ $taxDetails['nama_objek'] }}</div>
                        </div>
                        
                        <div>
                            <div class="pb-label">Masa Pajak</div>
                            <div class="pb-value">{{ $taxDetails['masa_pajak'] }}</div>
                        </div>

                        <div class="mt-4 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg space-y-2 text-sm border border-slate-200 dark:border-slate-700">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Total Tagihan Awal</span>
                                <span class="font-medium text-slate-900 dark:text-white">Rp {{ number_format($taxDetails['total_tagihan'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between border-t border-slate-200 dark:border-slate-700 pt-2">
                                <span class="text-slate-500 focus font-bold">Total Pembayaran Masuk</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($taxDetails['total_dibayar'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kanan: Tabel Riwayat Pembayaran --}}
            <div class="lg:col-span-8">
                <div class="pb-card">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4">Riwayat Transaksi Bayar</h3>
                    
                    @if(count($payments) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        <th class="py-3 px-4">Tanggal Pembayaran</th>
                                        <th class="py-3 px-4">Nominal</th>
                                        <th class="py-3 px-4">Kanal Pembayaran</th>
                                        <th class="py-3 px-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($payments as $payment)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td class="py-3 px-4 relative">
                                            <div class="font-medium text-slate-900 dark:text-white">{{ $payment['paid_at'] }}</div>
                                            <div class="text-xs text-slate-500 truncate max-w-[200px]" title="{{ $payment['description'] }}">Ref: {{ str($payment['description'])->limit(30) }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($payment['amount_paid'], 0, ',', '.') }}</div>
                                            <div class="text-xs text-slate-500">P+S: {{ number_format($payment['principal_paid'], 0, ',', '.') }} + {{ number_format($payment['penalty_paid'], 0, ',', '.') }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                                {{ $payment['payment_channel'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <button type="button" wire:click="confirmCancelPayment('{{ $payment['id'] }}')" class="pb-btn pb-btn-danger px-3 py-1.5 text-xs">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Batal Bayar
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="h-48 flex flex-col items-center justify-center text-center p-8 border-dashed border-2 rounded-xl bg-slate-50 dark:bg-slate-800/30 border-slate-200 dark:border-slate-700">
                            <p class="text-sm text-slate-500 dark:text-slate-400">Tidak ada riwayat pembayaran aktif (atau semua sudah dibatalkan).</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Modal Dialog Konfirmasi Batal Bayar --}}
        @if($isConfirmingCancel)
        <div class="fixed inset-0 z-[99] flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-700">
                <div class="bg-white dark:bg-slate-900 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-bold leading-6 text-slate-900 dark:text-white" id="modal-title">Konfirmasi Pembatalan Pembayaran</h3>
                            <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                <p>Anda akan melakukan soft-delete pada transaksi ini dan menarik kembali saldo pembayaran. Harap sertakan alasan untuk keperluan audit log Pimpinan.</p>
                                
                                <div class="mt-4">
                                    <label class="pb-label block text-left">Alasan Pembatalan <span class="text-red-500">*</span></label>
                                    <textarea wire:model.defer="cancelReason" rows="3" class="pb-input w-full mt-1" placeholder="Misal: Teller salah input nominal, harusnya Rp 150.000 tapi masuk Rp 15.000" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" wire:click="executeCancelPayment" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Batalkan Transaksi</button>
                    <button type="button" wire:click="cancelConfirm" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-white shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 sm:mt-0 sm:w-auto">Kembali</button>
                </div>
            </div>
        </div>
        @endif

    @endif

</div>

</x-filament-panels::page>
