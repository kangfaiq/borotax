@php($record->loadMissing(['user', 'jenisPajak', 'verificationStatusHistories.actor']))

<div class="space-y-4 text-sm">
    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 space-y-2">
        <h4 class="font-bold text-slate-700 dark:text-slate-300">Informasi Pengajuan</h4>
        <div class="grid grid-cols-2 gap-2">
            <div class="text-slate-500">Wajib Pajak</div>
            <div class="font-semibold">{{ $record->user_name ?? $record->user?->nama_lengkap ?? '-' }}</div>
            <div class="text-slate-500">Jenis Pajak</div>
            <div class="font-semibold">{{ $record->jenisPajak?->nama ?? '-' }}</div>
            <div class="text-slate-500">Tempat Transaksi</div>
            <div class="font-semibold">{{ $record->place_name ?? '-' }}</div>
            <div class="text-slate-500">Tanggal Transaksi</div>
            <div class="font-semibold">{{ $record->transaction_date?->translatedFormat('d F Y') ?? '-' }}</div>
            <div class="text-slate-500">Nominal</div>
            <div class="font-semibold">Rp {{ number_format((float) ($record->transaction_amount ?? 0), 0, ',', '.') }}</div>
            <div class="text-slate-500">Status</div>
            <div class="font-semibold">{{ $record->status_label }}</div>
        </div>
    </div>

    @if($record->rejection_reason)
        <div class="bg-red-50 dark:bg-red-900/15 rounded-xl p-4">
            <h4 class="font-bold text-red-700 dark:text-red-300 mb-1">Alasan Penolakan</h4>
            <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $record->rejection_reason }}</p>
        </div>
    @endif

    <div style="--verification-timeline-bg: #f8fafc; --verification-timeline-border: #e2e8f0; --verification-timeline-heading: #0f172a; --verification-timeline-muted: #64748b; --verification-timeline-dot: #0f766e; --verification-timeline-card-bg: #ffffff; --verification-timeline-card-border: #e2e8f0; --verification-timeline-card-heading: #0f172a; --verification-timeline-badge-bg: #ecfeff; --verification-timeline-badge-text: #0f766e;">
        <x-verification-status-timeline
            :histories="$record->verificationStatusHistories"
            heading="Riwayat Verifikasi"
            empty-message="Belum ada riwayat verifikasi untuk pengajuan ini."
        />
    </div>
</div>
