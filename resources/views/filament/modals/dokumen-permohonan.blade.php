<div class="space-y-4">
    <div>
        <h4 class="text-sm font-medium text-gray-500">KTP</h4>
        @if($record->file_ktp)
            <a href="{{ Storage::disk('local')->url($record->file_ktp) }}" target="_blank" class="text-primary-600 hover:underline flex items-center gap-1">
                <x-heroicon-o-document class="w-4 h-4" /> Lihat KTP
            </a>
        @else
            <span class="text-gray-400">Tidak ada</span>
        @endif
    </div>

    <div>
        <h4 class="text-sm font-medium text-gray-500">NPWP</h4>
        @if($record->file_npwp)
            <a href="{{ Storage::disk('local')->url($record->file_npwp) }}" target="_blank" class="text-primary-600 hover:underline flex items-center gap-1">
                <x-heroicon-o-document class="w-4 h-4" /> Lihat NPWP
            </a>
        @else
            <span class="text-gray-400">Tidak ada (opsional)</span>
        @endif
    </div>

    <div>
        <h4 class="text-sm font-medium text-gray-500">Desain / Materi Reklame</h4>
        @if($record->file_desain_reklame)
            <a href="{{ Storage::disk('local')->url($record->file_desain_reklame) }}" target="_blank" class="text-primary-600 hover:underline flex items-center gap-1">
                <x-heroicon-o-document class="w-4 h-4" /> Lihat Desain Reklame
            </a>
        @else
            <span class="text-gray-400">Tidak ada</span>
        @endif
    </div>
</div>
