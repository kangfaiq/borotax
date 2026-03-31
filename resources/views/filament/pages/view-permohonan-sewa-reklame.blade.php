<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Status Banner --}}
        <div class="rounded-xl border p-4
            @switch($record->status)
                @case('diajukan') bg-warning-50 border-warning-300 dark:bg-warning-950 dark:border-warning-700 @break
                @case('perlu_revisi') bg-info-50 border-info-300 dark:bg-info-950 dark:border-info-700 @break
                @case('diproses') bg-primary-50 border-primary-300 dark:bg-primary-950 dark:border-primary-700 @break
                @case('disetujui') bg-success-50 border-success-300 dark:bg-success-950 dark:border-success-700 @break
                @case('ditolak') bg-danger-50 border-danger-300 dark:bg-danger-950 dark:border-danger-700 @break
            @endswitch
        ">
            <div class="flex items-center gap-3">
                <span class="text-lg font-semibold">
                    Status:
                    @switch($record->status)
                        @case('diajukan') <span class="text-warning-700 dark:text-warning-400">Diajukan</span> @break
                        @case('perlu_revisi') <span class="text-info-700 dark:text-info-400">Perlu Revisi</span> @break
                        @case('diproses') <span class="text-primary-700 dark:text-primary-400">Diproses</span> @break
                        @case('disetujui') <span class="text-success-700 dark:text-success-400">Disetujui</span> @break
                        @case('ditolak') <span class="text-danger-700 dark:text-danger-400">Ditolak</span> @break
                    @endswitch
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">— Nomor Tiket: <strong>{{ $record->nomor_tiket }}</strong></span>
            </div>
            @if($record->catatan_petugas)
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300"><strong>Catatan Petugas:</strong> {{ $record->catatan_petugas }}</p>
            @endif
        </div>

        {{-- Data Pemohon --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header flex items-center gap-3 px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <x-heroicon-o-user class="w-5 h-5 text-gray-400" />
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Data Pemohon</h3>
            </div>
            <div class="fi-section-content p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">NIK</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->nik }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Alamat</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->alamat }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">No. Telepon</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->no_telepon }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->email ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Usaha</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->nama_usaha ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">No. Registrasi Izin DPMPTSP</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->nomor_registrasi_izin }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Detail Sewa --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header flex items-center gap-3 px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-gray-400" />
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Detail Sewa</h3>
            </div>
            <div class="fi-section-content p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    @if($record->asetReklame)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kode Aset</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->asetReklame->kode_aset }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Lokasi Aset</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->asetReklame->lokasi }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenis Reklame Dipasang</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->jenis_reklame_dipasang }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Durasi Sewa</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->durasi_sewa_hari }} hari</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Mulai Diinginkan</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->tanggal_mulai_diinginkan?->format('d/m/Y') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Pengajuan</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->tanggal_pengajuan?->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if($record->catatan)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Catatan Pemohon</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->catatan }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Dokumen yang Diunggah --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header flex items-center gap-3 px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <x-heroicon-o-paper-clip class="w-5 h-5 text-gray-400" />
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Dokumen yang Diunggah</h3>
            </div>
            <div class="fi-section-content p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- KTP --}}
                    <div class="rounded-lg border border-gray-200 dark:border-white/10 overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 border-b border-gray-200 dark:border-white/10">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <x-heroicon-o-identification class="w-4 h-4 inline -mt-0.5" /> KTP
                            </h4>
                        </div>
                        <div class="p-4">
                            @if($record->file_ktp)
                                @if(str()->endsWith(strtolower($record->file_ktp), ['.jpg', '.jpeg', '.png']))
                                    <img src="{{ route('permohonan-sewa.file', ['id' => $record->id, 'field' => 'file_ktp']) }}"
                                         alt="KTP" class="w-full rounded border border-gray-200 dark:border-white/10 cursor-pointer"
                                         onclick="window.open(this.src, '_blank')">
                                @else
                                    <a href="{{ route('permohonan-sewa.file', ['id' => $record->id, 'field' => 'file_ktp']) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400">
                                        <x-heroicon-o-document class="w-5 h-5" /> Lihat PDF
                                    </a>
                                @endif
                            @else
                                <span class="text-sm text-gray-400 italic">Tidak ada file</span>
                            @endif
                        </div>
                    </div>

                    {{-- NPWP --}}
                    <div class="rounded-lg border border-gray-200 dark:border-white/10 overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 border-b border-gray-200 dark:border-white/10">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <x-heroicon-o-document-text class="w-4 h-4 inline -mt-0.5" /> NPWP
                            </h4>
                        </div>
                        <div class="p-4">
                            @if($record->file_npwp)
                                @if(str()->endsWith(strtolower($record->file_npwp), ['.jpg', '.jpeg', '.png']))
                                    <img src="{{ route('permohonan-sewa.file', ['id' => $record->id, 'field' => 'file_npwp']) }}"
                                         alt="NPWP" class="w-full rounded border border-gray-200 dark:border-white/10 cursor-pointer"
                                         onclick="window.open(this.src, '_blank')">
                                @else
                                    <a href="{{ route('permohonan-sewa.file', ['id' => $record->id, 'field' => 'file_npwp']) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400">
                                        <x-heroicon-o-document class="w-5 h-5" /> Lihat PDF
                                    </a>
                                @endif
                            @else
                                <span class="text-sm text-gray-400 italic">Tidak diunggah (opsional)</span>
                            @endif
                        </div>
                    </div>

                    {{-- Desain Reklame --}}
                    <div class="rounded-lg border border-gray-200 dark:border-white/10 overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 border-b border-gray-200 dark:border-white/10">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <x-heroicon-o-photo class="w-4 h-4 inline -mt-0.5" /> Desain / Materi Reklame
                            </h4>
                        </div>
                        <div class="p-4">
                            @if($record->file_desain_reklame)
                                @if(str()->endsWith(strtolower($record->file_desain_reklame), ['.jpg', '.jpeg', '.png']))
                                    <img src="{{ route('permohonan-sewa.file', ['id' => $record->id, 'field' => 'file_desain_reklame']) }}"
                                         alt="Desain Reklame" class="w-full rounded border border-gray-200 dark:border-white/10 cursor-pointer"
                                         onclick="window.open(this.src, '_blank')">
                                @else
                                    <a href="{{ route('permohonan-sewa.file', ['id' => $record->id, 'field' => 'file_desain_reklame']) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400">
                                        <x-heroicon-o-document class="w-5 h-5" /> Lihat PDF
                                    </a>
                                @endif
                            @else
                                <span class="text-sm text-gray-400 italic">Tidak ada file</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Petugas --}}
        @if($record->petugas_nama)
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header flex items-center gap-3 px-6 py-4 border-b border-gray-200 dark:border-white/10">
                    <x-heroicon-o-shield-check class="w-5 h-5 text-gray-400" />
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Info Petugas</h3>
                </div>
                <div class="fi-section-content p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Petugas</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->petugas_nama }}</dd>
                        </div>
                        @if($record->tanggal_diproses)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Diproses</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->tanggal_diproses->format('d/m/Y H:i') }}</dd>
                            </div>
                        @endif
                        @if($record->tanggal_selesai)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Selesai</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->tanggal_selesai->format('d/m/Y H:i') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
