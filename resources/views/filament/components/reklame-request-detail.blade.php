@php($record->loadMissing(['reklameObject', 'user', 'verificationStatusHistories.actor']))

<div class="space-y-4 text-sm">
    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 space-y-2">
        <h4 class="font-bold text-slate-700 dark:text-slate-300">Informasi Pengajuan</h4>
        <div class="grid grid-cols-2 gap-2">
            <div class="text-slate-500">Objek Reklame</div>
            <div class="font-semibold">{{ $record->reklameObject?->nama_objek_pajak ?? '-' }}</div>
            <div class="text-slate-500">Wajib Pajak</div>
            <div class="font-semibold">{{ $record->user_name ?? $record->user?->nama_lengkap ?? '-' }}</div>
            <div class="text-slate-500">Durasi Perpanjangan</div>
            <div class="font-semibold">{{ $record->durasi_perpanjangan_hari }} hari</div>
            <div class="text-slate-500">Tanggal Pengajuan</div>
            <div class="font-semibold">{{ $record->tanggal_pengajuan?->translatedFormat('d F Y H:i') ?? '-' }}</div>
            <div class="text-slate-500">Status</div>
            <div class="font-semibold">{{ $record->status_label }}</div>
        </div>
    </div>

    @if($record->catatan_pengajuan)
        <div class="bg-blue-50 dark:bg-blue-900/15 rounded-xl p-4">
            <h4 class="font-bold text-blue-700 dark:text-blue-300 mb-1">Catatan Pengajuan</h4>
            <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $record->catatan_pengajuan }}</p>
        </div>
    @endif

    @if($record->catatan_petugas)
        <div class="bg-amber-50 dark:bg-amber-900/15 rounded-xl p-4">
            <h4 class="font-bold text-amber-700 dark:text-amber-300 mb-1">Catatan Petugas</h4>
            <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $record->catatan_petugas }}</p>
        </div>
    @endif

    <div style="--verification-timeline-bg: #f8fafc; --verification-timeline-border: #e2e8f0; --verification-timeline-heading: #0f172a; --verification-timeline-muted: #64748b; --verification-timeline-dot: #1565c0; --verification-timeline-card-bg: #ffffff; --verification-timeline-card-border: #e2e8f0; --verification-timeline-card-heading: #0f172a; --verification-timeline-badge-bg: #e3f2fd; --verification-timeline-badge-text: #1565c0;">
        <x-verification-status-timeline
            :histories="$record->verificationStatusHistories"
            heading="Riwayat Verifikasi"
            empty-message="Belum ada riwayat verifikasi untuk pengajuan ini."
        />
    </div>
</div>
