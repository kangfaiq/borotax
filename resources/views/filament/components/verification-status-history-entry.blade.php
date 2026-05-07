@php
    $record = $getRecord();
    $record?->loadMissing('verificationStatusHistories.actor');
@endphp

<div style="--verification-timeline-bg: #f8fafc; --verification-timeline-border: #e2e8f0; --verification-timeline-heading: #0f172a; --verification-timeline-muted: #64748b; --verification-timeline-dot: #0f766e; --verification-timeline-card-bg: #ffffff; --verification-timeline-card-border: #e2e8f0; --verification-timeline-card-heading: #0f172a; --verification-timeline-badge-bg: #ecfeff; --verification-timeline-badge-text: #0f766e;">
    <x-verification-status-timeline
        :histories="$record?->verificationStatusHistories ?? collect()"
        heading="Riwayat Verifikasi"
        empty-message="Belum ada riwayat verifikasi untuk data ini."
    />
</div>
