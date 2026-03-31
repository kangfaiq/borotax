@php
    $npwpd = $getState() ?? $this->data['npwpd'] ?? null;
    $search = $this->daftarObjekSearch ?? '';
    $page = $this->daftarObjekPage ?? 1;
    $perPage = 5;

    $query = \App\Domain\Tax\Models\TaxObject::where('npwpd', $npwpd);

    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('nama_objek_pajak', 'like', '%' . $search . '%')
              ->orWhereHas('jenisPajak', fn ($j) => $j->where('nama', 'like', '%' . $search . '%'))
              ->orWhereHas('subJenisPajak', fn ($s) => $s->where('nama', 'like', '%' . $search . '%'));
        });
    }

    $total = (clone $query)->count();
    $totalPages = max(1, ceil($total / $perPage));
    $page = min($page, $totalPages);
    $objects = $query->with(['jenisPajak', 'subJenisPajak'])
        ->orderBy('created_at', 'desc')
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();
@endphp

@if($npwpd)
<style>
    .dark .dop-table tbody tr:hover td { color: #111827 !important; }
</style>
<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 flex items-center justify-between">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
            Objek Pajak Terdaftar ({{ $total }})
        </h4>
        <div>
            <input
                type="text"
                wire:model.live.debounce.300ms="daftarObjekSearch"
                placeholder="Cari nama objek..."
                class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-primary-500"
            />
        </div>
    </div>

    @if($objects->count() > 0)
        <table class="w-full text-sm dop-table">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Nama Objek</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Jenis Pajak</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Sub Jenis</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Status</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($objects as $obj)
                    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors">
                        <td class="px-4 py-2 text-gray-700 dark:text-white">{{ $obj->nama_objek_pajak }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-white">{{ $obj->jenisPajak->nama ?? '-' }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-white">{{ $obj->subJenisPajak->nama ?? '-' }}</td>
                        <td class="px-4 py-2 text-center text-gray-700 dark:text-white">
                            @if($obj->is_active)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Aktif</span>
                            @else
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center text-gray-700 dark:text-white">
                            <a href="{{ \App\Filament\Resources\TaxObjectResource::getUrl('edit', ['record' => $obj->id]) }}"
                               class="text-primary-600 hover:text-primary-800 dark:text-primary-400 text-xs font-medium">
                                Edit
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($totalPages > 1)
            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 flex items-center justify-between border-t border-gray-200 dark:border-gray-700">
                <span class="text-xs text-gray-500">Hal {{ $page }} dari {{ $totalPages }}</span>
                <div class="flex gap-1">
                    @if($page > 1)
                        <button wire:click="$set('daftarObjekPage', {{ $page - 1 }})" class="px-2 py-1 text-xs bg-white dark:bg-gray-700 border rounded hover:bg-gray-100">&laquo;</button>
                    @endif
                    @if($page < $totalPages)
                        <button wire:click="$set('daftarObjekPage', {{ $page + 1 }})" class="px-2 py-1 text-xs bg-white dark:bg-gray-700 border rounded hover:bg-gray-100">&raquo;</button>
                    @endif
                </div>
            </div>
        @endif
    @else
        <div class="px-4 py-6 text-center text-sm text-gray-400">
            @if(!empty($search))
                Tidak ada objek pajak yang sesuai pencarian.
            @else
                Belum ada objek pajak terdaftar untuk NPWPD ini.
            @endif
        </div>
    @endif
</div>
@endif
