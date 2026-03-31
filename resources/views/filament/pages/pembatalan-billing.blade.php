<x-filament-panels::page>

<style>
    :root {
        --pb-danger: #ef4444; --pb-danger-h: #dc2626;
        --pb-success: #10b981; --pb-success-h: #059669;
        --pb-danger-5: rgba(239,68,68,.05); --pb-danger-10: rgba(239,68,68,.10);
        --pb-success-5: rgba(16,185,129,.05); --pb-success-10: rgba(16,185,129,.10);
    }
    .dark {
        --pb-danger: #f87171; --pb-danger-h: #ef4444;
        --pb-success: #34d399; --pb-success-h: #10b981;
        --pb-danger-5: rgba(248,113,113,.10); --pb-danger-10: rgba(248,113,113,.18);
        --pb-success-5: rgba(52,211,153,.10); --pb-success-10: rgba(52,211,153,.18);
    }
</style>

<div class="space-y-6">

    {{-- Page header --}}
    <div>
        <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Pembatalan Billing</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola pembatalan dan pemulihan billing self-assessment.</p>
    </div>

    {{-- Tabs --}}
    <style>
        .pb-tab-wrap { display:flex; gap:4px; padding:4px; border-radius:12px; width:fit-content;
                       background:#f1f5f9; border:1px solid #e2e8f0; }
        .dark .pb-tab-wrap { background:#1e293b; border-color:#334155; }

        .pb-tab { padding:8px 20px; border-radius:8px; font-size:14px; font-weight:600;
                  transition:all .15s; cursor:pointer; border:1px solid transparent; }
        .pb-tab-active { background:#fff; color:#0f172a; border-color:#e2e8f0;
                         box-shadow:0 1px 3px rgba(0,0,0,.08); }
        .dark .pb-tab-active { background:#475569; color:#f1f5f9; border-color:#64748b; }

        .pb-tab-inactive { color:#64748b; }
        .pb-tab-inactive:hover { color:#334155; background:#e2e8f0; }
        .dark .pb-tab-inactive { color:#94a3b8; }
        .dark .pb-tab-inactive:hover { color:#f1f5f9; background:#334155; }
    </style>
    <div class="pb-tab-wrap">
        <button wire:click="switchTab('aktif')"
                class="pb-tab {{ $activeTab === 'aktif' ? 'pb-tab-active' : 'pb-tab-inactive' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Billing Aktif
            </span>
        </button>
        <button wire:click="switchTab('dibatalkan')"
                class="pb-tab {{ $activeTab === 'dibatalkan' ? 'pb-tab-active' : 'pb-tab-inactive' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Dibatalkan
            </span>
        </button>
    </div>

    {{-- Info banner --}}
    @if($activeTab === 'aktif')
    <div class="flex items-start gap-3 bg-blue-50 dark:bg-blue-900/15 border border-blue-200 dark:border-blue-800/50
                rounded-xl px-4 py-3 text-sm text-blue-700 dark:text-blue-300">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <p>Klik tombol <strong>"Batalkan"</strong> untuk membatalkan billing. Data tidak akan hilang dan bisa dipulihkan kembali melalui tab "Dibatalkan".</p>
    </div>
    @else
    <div class="flex items-start gap-3 bg-amber-50 dark:bg-amber-900/15 border border-amber-200 dark:border-amber-800/50
                rounded-xl px-4 py-3 text-sm text-amber-700 dark:text-amber-300">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <p>Daftar billing yang sudah dibatalkan. Klik <strong>"Pulihkan"</strong> untuk mengembalikan billing ke status semula.</p>
    </div>
    @endif

    {{-- Filament Table --}}
    {{ $this->table }}

</div>

</x-filament-panels::page>
