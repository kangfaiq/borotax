<div class="space-y-4">
    {{-- Billing Info --}}
    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <h4 class="font-semibold text-sm text-gray-500 dark:text-gray-400 mb-2">Informasi Billing Sumber</h4>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <p class="text-gray-500 dark:text-gray-400">Billing Sumber</p>
                <p class="font-mono font-semibold">{{ $record->tax->billing_code ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Status Billing Sumber</p>
                <p class="capitalize">{{ $record->tax->status ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Omzet Dilaporkan</p>
                <p>Rp {{ number_format($record->tax->omzet ?? 0, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Pajak</p>
                <p>Rp {{ number_format($record->tax->amount ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Alasan WP --}}
    <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
        <h4 class="font-semibold text-sm text-amber-700 dark:text-amber-300 mb-2">Alasan Pembetulan dari WP</h4>
        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $record->alasan }}</p>
    </div>

    {{-- Omzet Koreksi --}}
    @if($record->omzet_baru)
        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <h4 class="font-semibold text-sm text-blue-700 dark:text-blue-300 mb-1">Omzet Koreksi (Saran WP)</h4>
            <p class="text-lg font-bold text-blue-800 dark:text-blue-200">Rp
                {{ number_format($record->omzet_baru, 0, ',', '.') }}</p>
        </div>
    @endif

    {{-- Lampiran --}}
    @if($record->lampiran)
        <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
            <h4 class="font-semibold text-sm text-purple-700 dark:text-purple-300 mb-2">Lampiran Pendukung</h4>
            <div class="flex items-center gap-3">
                <a href="{{ asset('storage/' . $record->lampiran) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-purple-200 dark:border-purple-700 rounded-md shadow-sm text-sm font-medium text-purple-700 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                    </svg>
                    Lihat Lampiran
                </a>
                <span class="text-xs text-gray-500">{{ basename($record->lampiran) }}</span>
            </div>
        </div>
    @endif

    {{-- Catatan Petugas --}}
    @if($record->catatan_petugas)
        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
            <h4 class="font-semibold text-sm text-green-700 dark:text-green-300 mb-1">Catatan Petugas</h4>
            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $record->catatan_petugas }}</p>
            @if($record->processor)
                <p class="text-xs text-gray-500 mt-2">— {{ $record->processor->nama_lengkap ?? $record->processor->name }},
                    {{ $record->processed_at?->format('d/m/Y H:i') }}</p>
            @endif
        </div>
    @endif
</div>