@php
    $changes = $getRecord()->field_changes;
    $fieldLabels = [
        'nik' => 'NIK',
        'nama_lengkap' => 'Nama Lengkap',
        'alamat' => 'Alamat',
        'tipe_wajib_pajak' => 'Tipe Wajib Pajak',
        'nama_perusahaan' => 'Nama Perusahaan',
        'nib' => 'NIB',
        'npwp_pusat' => 'NPWP Pusat',
        'asal_wilayah' => 'Asal Wilayah',
        'nama_objek_pajak' => 'Nama Objek Pajak',
        'alamat_objek' => 'Alamat Objek',
        'kelurahan' => 'Kelurahan',
        'kecamatan' => 'Kecamatan',
        'panjang' => 'Panjang (m)',
        'lebar' => 'Lebar (m)',
        'luas_m2' => 'Luas (m²)',
        'jumlah_muka' => 'Jumlah Muka',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'kelompok_lokasi' => 'Kelompok Lokasi',
        'tarif_persen' => 'Tarif (%)',
    ];
@endphp

@if($changes && count($changes) > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse border border-gray-200 dark:border-gray-700">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800">
                    <th class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-left font-semibold">Field</th>
                    <th class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-left font-semibold">Nilai Lama</th>
                    <th class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-left font-semibold">Nilai Baru</th>
                </tr>
            </thead>
            <tbody>
                @foreach($changes as $field => $change)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 font-medium">
                            {{ $fieldLabels[$field] ?? str_replace('_', ' ', ucfirst($field)) }}
                        </td>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-red-600 dark:text-red-400">
                            {{ $change['old'] ?? '-' }}
                        </td>
                        <td class="border border-gray-200 dark:border-gray-700 px-4 py-2 text-green-600 dark:text-green-400">
                            {{ $change['new'] ?? '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p class="text-gray-500 dark:text-gray-400 italic">Tidak ada perubahan</p>
@endif
