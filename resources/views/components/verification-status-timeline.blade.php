@props([
    'histories' => collect(),
    'heading' => 'Riwayat Status Verifikasi',
    'emptyMessage' => 'Belum ada riwayat status.',
])

<style>
    .verification-status-timeline {
        border-radius: 16px;
        border: 1px solid var(--verification-history-border, var(--border, #e2e8f0));
        background: var(--verification-history-bg, var(--bg-card, #ffffff));
        padding: 20px 22px;
    }

    .verification-status-timeline__heading {
        margin-bottom: 14px;
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--verification-history-heading, var(--text-primary, #0f172a));
    }

    .verification-status-timeline__empty {
        font-size: 0.84rem;
        color: var(--verification-history-muted, var(--text-secondary, #64748b));
    }

    .verification-status-timeline__list {
        display: grid;
        gap: 14px;
    }

    .verification-status-timeline__item {
        position: relative;
        padding-left: 20px;
    }

    .verification-status-timeline__item::before {
        content: '';
        position: absolute;
        left: 4px;
        top: 7px;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: var(--verification-history-accent, var(--primary, #3b82f6));
    }

    .verification-status-timeline__item::after {
        content: '';
        position: absolute;
        left: 7px;
        top: 20px;
        bottom: -18px;
        width: 2px;
        background: var(--verification-history-line, var(--border, #e2e8f0));
    }

    .verification-status-timeline__item:last-child::after {
        display: none;
    }

    .verification-status-timeline__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 12px;
        margin-bottom: 4px;
        font-size: 0.75rem;
        color: var(--verification-history-muted, var(--text-secondary, #64748b));
    }

    .verification-status-timeline__title {
        font-size: 0.88rem;
        font-weight: 800;
        color: var(--verification-history-heading, var(--text-primary, #0f172a));
    }

    .verification-status-timeline__transition {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 4px;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--verification-history-text, var(--text-primary, #334155));
    }

    .verification-status-timeline__note {
        margin-top: 8px;
        font-size: 0.82rem;
        line-height: 1.65;
        white-space: pre-wrap;
        color: var(--verification-history-text, var(--text-primary, #334155));
    }
</style>

<section class="verification-status-timeline">
    <h3 class="verification-status-timeline__heading">{{ $heading }}</h3>

    @if(collect($histories)->isEmpty())
        <div class="verification-status-timeline__empty">{{ $emptyMessage }}</div>
    @else
        <div class="verification-status-timeline__list">
            @foreach($histories as $history)
                <article class="verification-status-timeline__item">
                    <div class="verification-status-timeline__meta">
                        <span>{{ $history->happened_at?->format('d/m/Y H:i') ?? '-' }}</span>
                        <span>{{ $history->actor_display_name }}</span>
                        @if($history->actor_role)
                            <span>{{ str($history->actor_role)->headline()->toString() }}</span>
                        @endif
                    </div>
                    <div class="verification-status-timeline__title">{{ $history->action_label }}</div>
                    <div class="verification-status-timeline__transition">
                        <i class="bi bi-arrow-repeat"></i>
                        {{ $history->status_transition_label }}
                    </div>
                    @if($history->note)
                        <div class="verification-status-timeline__note">{{ $history->note }}</div>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</section>