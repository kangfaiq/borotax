@props(['activityLogs', 'changeRequests'])

<div class="space-y-6">
    {{-- Permintaan Perubahan Data --}}
    @if($changeRequests && $changeRequests->count() > 0)
        <div>
            <h3 class="text-base font-semibold mb-3">Permintaan Perubahan Data</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse border border-gray-200 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="border border-gray-200 dark:border-gray-700 px-3 py-2 text-left">Tanggal</th>
                            <th class="border border-gray-200 dark:border-gray-700 px-3 py-2 text-left">Pengaju</th>
                            <th class="border border-gray-200 dark:border-gray-700 px-3 py-2 text-left">Field</th>
                            <th class="border border-gray-200 dark:border-gray-700 px-3 py-2 text-left">Status</th>
                            <th class="border border-gray-200 dark:border-gray-700 px-3 py-2 text-left">Reviewer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($changeRequests as $cr)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="border border-gray-200 dark:border-gray-700 px-3 py-2">
                                    {{ $cr->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 px-3 py-2">
                                    {{ $cr->requester?->name ?? '-' }}
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 px-3 py-2">
                                    @php $fields = array_keys($cr->field_changes ?? []); @endphp
                                    {{ implode(', ', $fields) }}
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 px-3 py-2">
                                    @php
                                        $statusColor = match($cr->status) {
                                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        $statusLabel = match($cr->status) {
                                            'pending' => 'Menunggu',
                                            'approved' => 'Disetujui',
                                            'rejected' => 'Ditolak',
                                            default => $cr->status,
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColor }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 px-3 py-2">
                                    {{ $cr->reviewer?->name ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Riwayat Aktivitas --}}
    @if($activityLogs && $activityLogs->count() > 0)
        <div>
            <h3 class="text-base font-semibold mb-3">Riwayat Aktivitas</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse border border-gray-200 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="border border-gray-200 dark:border-gray-700 px-3 py-2 text-left">Tanggal</th>
                            <th class="border border-gray-200 dark:border-gray-700 px-3 py-2 text-left">Aksi</th>
                            <th class="border border-gray-200 dark:border-gray-700 px-3 py-2 text-left">Petugas</th>
                            <th class="border border-gray-200 dark:border-gray-700 px-3 py-2 text-left">Deskripsi</th>
                            <th class="border border-gray-200 dark:border-gray-700 px-3 py-2 text-left">Perubahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activityLogs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="border border-gray-200 dark:border-gray-700 px-3 py-2 whitespace-nowrap">
                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 px-3 py-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        {{ $log->action_label }}
                                    </span>
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 px-3 py-2">
                                    {{ $log->actor?->name ?? 'System' }}
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 px-3 py-2">
                                    {{ $log->description ?? '-' }}
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 px-3 py-2">
                                    @if($log->old_values || $log->new_values)
                                        <details class="cursor-pointer">
                                            <summary class="text-primary-600 dark:text-primary-400 text-xs">
                                                Lihat detail ({{ count($log->old_values ?? []) }} field)
                                            </summary>
                                            <div class="mt-2 space-y-1">
                                                @foreach(($log->old_values ?? []) as $field => $oldVal)
                                                    <div class="text-xs">
                                                        <span class="font-medium">{{ str_replace('_', ' ', ucfirst($field)) }}:</span>
                                                        <span class="text-red-600 dark:text-red-400 line-through">{{ $oldVal ?? '-' }}</span>
                                                        →
                                                        <span class="text-green-600 dark:text-green-400">{{ ($log->new_values[$field] ?? '-') }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </details>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if((!$activityLogs || $activityLogs->count() === 0) && (!$changeRequests || $changeRequests->count() === 0))
        <p class="text-gray-500 dark:text-gray-400 italic text-center py-4">Belum ada riwayat perubahan</p>
    @endif
</div>
