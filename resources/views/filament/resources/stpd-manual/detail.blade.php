@php($record->loadMissing('verificationStatusHistories.actor'))

<div class="space-y-4 text-sm">
    {{-- Billing Info --}}
    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 space-y-2">
        <h4 class="font-bold text-slate-700 dark:text-slate-300">Data Billing</h4>
        <div class="grid grid-cols-2 gap-2">
            <div class="text-slate-500">Kode Pembayaran Aktif</div>
            <div class="font-mono font-bold">{{ $tax?->getPreferredPaymentCode() ?? '-' }}</div>
            @if($tax?->stpd_payment_code)
            <div class="text-slate-500">Billing Sumber</div>
            <div class="font-mono font-semibold">{{ $tax->billing_code }}</div>
            @endif
            <div class="text-slate-500">Jenis Pajak</div>
            <div class="font-semibold">{{ $tax?->jenisPajak?->nama ?? '-' }}</div>
            <div class="text-slate-500">Nama Objek</div>
            <div class="font-semibold">{{ $tax?->taxObject?->nama_objek_pajak ?? '-' }}</div>
            <div class="text-slate-500">Masa Pajak</div>
            <div class="font-semibold">
                @if($tax?->masa_pajak_bulan)
                    {{ \Carbon\Carbon::create()->month((int) $tax->masa_pajak_bulan)->translatedFormat('F') }} {{ $tax->masa_pajak_tahun }}
                @else
                    {{ $tax?->masa_pajak_tahun ?? '-' }}
                @endif
            </div>
            <div class="text-slate-500">Wajib Pajak</div>
            <div class="font-semibold">{{ $wp?->nama_lengkap ?? '-' }}</div>
            <div class="text-slate-500">NPWPD</div>
            <div class="font-mono font-semibold">{{ $wp?->npwpd ?? '-' }}</div>
        </div>
    </div>

    {{-- STPD Info --}}
    <div class="bg-purple-50 dark:bg-purple-900/15 rounded-xl p-4 space-y-2">
        <h4 class="font-bold text-purple-700 dark:text-purple-300">Detail STPD</h4>
        <div class="grid grid-cols-2 gap-2">
            <div class="text-purple-500 dark:text-purple-400">Nomor STPD</div>
            <div class="font-bold">{{ $record->nomor_stpd ?? '(Draft)' }}</div>
            <div class="text-purple-500 dark:text-purple-400">Tipe</div>
            <div class="font-semibold">{{ $record->tipe === 'pokok_sanksi' ? 'Pokok & Sanksi' : 'Sanksi Saja' }}</div>
            <div class="text-purple-500 dark:text-purple-400">Status</div>
            <div>
                @php
                    $badgeColors = [
                        'draft' => 'bg-yellow-100 text-yellow-700',
                        'disetujui' => 'bg-green-100 text-green-700',
                        'ditolak' => 'bg-red-100 text-red-700',
                    ];
                @endphp
                <span class="px-2 py-0.5 rounded text-xs font-bold {{ $badgeColors[$record->status] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ strtoupper($record->status) }}
                </span>
            </div>
            <div class="text-purple-500 dark:text-purple-400">Bulan Terlambat</div>
            <div class="font-semibold">{{ $record->bulan_terlambat }} bulan</div>
            <div class="text-purple-500 dark:text-purple-400">Sanksi Dihitung</div>
            <div class="font-bold text-red-600">Rp {{ number_format((float) $record->sanksi_dihitung, 0, ',', '.') }}</div>
            @if($record->tipe === 'pokok_sanksi')
            <div class="text-purple-500 dark:text-purple-400">Pokok Belum Dibayar</div>
            <div class="font-bold text-red-600">Rp {{ number_format((float) $record->pokok_belum_dibayar, 0, ',', '.') }}</div>
            <div class="text-purple-500 dark:text-purple-400">Proyeksi Tgl Bayar</div>
            <div class="font-semibold">{{ $record->proyeksi_tanggal_bayar?->translatedFormat('d F Y') ?? '-' }}</div>
            @endif
        </div>
    </div>

    {{-- Petugas & Verifikator --}}
    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 space-y-2">
        <h4 class="font-bold text-slate-700 dark:text-slate-300">Petugas & Verifikasi</h4>
        <div class="grid grid-cols-2 gap-2">
            <div class="text-slate-500">Petugas</div>
            <div class="font-semibold">{{ $record->petugas_nama ?? '-' }}</div>
            <div class="text-slate-500">Tgl Buat</div>
            <div class="font-semibold">{{ $record->tanggal_buat?->translatedFormat('d F Y H:i') ?? '-' }}</div>
            @if($record->verifikator_nama)
            <div class="text-slate-500">Verifikator</div>
            <div class="font-semibold">{{ $record->verifikator_nama }}</div>
            <div class="text-slate-500">Tgl Verifikasi</div>
            <div class="font-semibold">{{ $record->tanggal_verifikasi?->translatedFormat('d F Y H:i') ?? '-' }}</div>
            @endif
        </div>
    </div>

    {{-- Catatan --}}
    @if($record->catatan_petugas)
    <div class="bg-blue-50 dark:bg-blue-900/15 rounded-xl p-4">
        <h4 class="font-bold text-blue-700 dark:text-blue-300 mb-1">Catatan Petugas</h4>
        <p class="text-slate-700 dark:text-slate-300">{{ $record->catatan_petugas }}</p>
    </div>
    @endif

    @if($record->catatan_verifikasi)
    <div class="bg-red-50 dark:bg-red-900/15 rounded-xl p-4">
        <h4 class="font-bold text-red-700 dark:text-red-300 mb-1">Catatan Verifikasi</h4>
        <p class="text-slate-700 dark:text-slate-300">{{ $record->catatan_verifikasi }}</p>
    </div>
    @endif

    <div style="--verification-timeline-bg: #f8fafc; --verification-timeline-border: #e2e8f0; --verification-timeline-heading: #0f172a; --verification-timeline-muted: #64748b; --verification-timeline-dot: #7c3aed; --verification-timeline-card-bg: #ffffff; --verification-timeline-card-border: #e2e8f0; --verification-timeline-card-heading: #0f172a; --verification-timeline-badge-bg: #f3e8ff; --verification-timeline-badge-text: #7c3aed;">
        <x-verification-status-timeline
            :histories="$record->verificationStatusHistories"
            heading="Riwayat Verifikasi"
            empty-message="Belum ada riwayat verifikasi untuk STPD ini."
        />
    </div>
</div>
