<style>
    /* ========================================
       TOP NAVIGATION — LIGHT MODE
       Clean white nav with dark text
       ======================================== */
    .fi-topbar > nav {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
        border-bottom: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
        --tw-ring-color: transparent !important;
    }

    /* Brand — light */
    .fi-topbar .fi-topbar-brand,
    .fi-topbar .fi-topbar-brand span,
    .fi-topbar .fi-topbar-brand svg {
        color: #0f172a !important;
    }

    /* Nav items — light */
    .fi-topbar > nav a,
    .fi-topbar > nav button,
    .fi-topbar > nav span,
    .fi-topbar > nav p,
    .fi-topbar > nav label {
        color: #475569 !important;
    }
    .fi-topbar > nav a:hover,
    .fi-topbar > nav button:hover {
        color: #0f172a !important;
    }

    /* SVG icons — light */
    .fi-topbar > nav svg {
        color: #64748b !important;
    }
    .fi-topbar > nav a:hover svg,
    .fi-topbar > nav button:hover svg {
        color: #0f172a !important;
    }

    /* Active nav item — light */
    .fi-topbar-item-active,
    .fi-topbar-item-active > a,
    .fi-topbar-item-active > button {
        color: #1d4ed8 !important;
        background-color: #eff6ff !important;
        border-radius: 0.5rem;
    }
    .fi-topbar-item-active svg {
        color: #2563eb !important;
    }

    /* Badge & avatar — light */
    .fi-topbar .fi-badge { color: inherit !important; }
    .fi-topbar .fi-avatar { border-color: #e2e8f0 !important; }

    /* ========================================
       TOP NAVIGATION — DARK MODE
       Deep navy nav with white text
       ======================================== */
    .dark .fi-topbar > nav {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
        border-bottom: 1px solid rgba(255,255,255,0.06) !important;
        box-shadow: 0 2px 12px rgba(0,0,0,0.3) !important;
    }

    /* Brand — dark */
    .dark .fi-topbar .fi-topbar-brand,
    .dark .fi-topbar .fi-topbar-brand span,
    .dark .fi-topbar .fi-topbar-brand svg {
        color: #ffffff !important;
    }

    /* Nav items — dark */
    .dark .fi-topbar > nav a,
    .dark .fi-topbar > nav button,
    .dark .fi-topbar > nav span,
    .dark .fi-topbar > nav p,
    .dark .fi-topbar > nav label {
        color: rgba(255,255,255,0.7) !important;
    }
    .dark .fi-topbar > nav a:hover,
    .dark .fi-topbar > nav button:hover {
        color: #ffffff !important;
    }

    /* SVG icons — dark */
    .dark .fi-topbar > nav svg {
        color: rgba(255,255,255,0.55) !important;
    }
    .dark .fi-topbar > nav a:hover svg,
    .dark .fi-topbar > nav button:hover svg {
        color: #ffffff !important;
    }

    /* Active nav item — dark */
    .dark .fi-topbar-item-active,
    .dark .fi-topbar-item-active > a,
    .dark .fi-topbar-item-active > button {
        color: #ffffff !important;
        background-color: rgba(59, 130, 246, 0.2) !important;
        border-radius: 0.5rem;
    }
    .dark .fi-topbar-item-active svg {
        color: rgba(255,255,255,0.95) !important;
    }

    /* Badge & avatar — dark */
    .dark .fi-topbar .fi-badge { color: inherit !important; }
    .dark .fi-topbar .fi-avatar { border-color: rgba(255,255,255,0.15) !important; }

    /* ========================================
       SIDEBAR — LIGHT MODE
       Clean white sidebar with dark text
       ======================================== */

    /* --- Base sidebar container --- */
    .fi-sidebar {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%) !important;
        border-right: 1px solid #e2e8f0 !important;
    }

    /* --- Header: brand area --- */
    .fi-sidebar-header {
        background: transparent !important;
        border-bottom: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
        --tw-ring-color: transparent !important;
    }
    .fi-sidebar-header,
    .fi-sidebar-header a,
    .fi-sidebar-header span,
    .fi-sidebar-header svg {
        color: #0f172a !important;
    }
    /* Hide original header collapse button — replaced by footer */
    .fi-sidebar-header > .ms-auto {
        display: none !important;
    }
    /* Expand button (collapsed state) */
    .fi-sidebar-header > .mx-auto {
        color: #64748b !important;
    }
    .fi-sidebar-header > .mx-auto:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
    }

    /* --- Nav container --- */
    .fi-sidebar-nav {
        padding-top: 1.25rem !important;
        padding-bottom: 1.25rem !important;
    }

    /* --- Group labels --- */
    .fi-sidebar-group-label {
        color: #94a3b8 !important;
        font-size: 0.7rem !important;
        font-weight: 600 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
    }

    /* --- Group collapse chevron --- */
    .fi-sidebar-group-collapse-button {
        color: #cbd5e1 !important;
    }
    .fi-sidebar-group-collapse-button:hover {
        color: #64748b !important;
        background: #f1f5f9 !important;
    }

    /* --- Nav items: default --- */
    .fi-sidebar-item-button {
        border-radius: 0.5rem !important;
        transition: all 150ms ease !important;
    }
    .fi-sidebar-item-label {
        color: #475569 !important;
    }
    .fi-sidebar-item-icon {
        color: #94a3b8 !important;
    }

    /* --- Nav items: hover --- */
    .fi-sidebar-item-button:hover {
        background: #f1f5f9 !important;
    }
    .fi-sidebar-item-button:hover .fi-sidebar-item-label {
        color: #0f172a !important;
    }
    .fi-sidebar-item-button:hover .fi-sidebar-item-icon {
        color: #475569 !important;
    }

    /* --- Nav items: active --- */
    .fi-sidebar-item.fi-active > .fi-sidebar-item-button,
    .fi-sidebar-item.fi-sidebar-item-active > .fi-sidebar-item-button {
        background: #eff6ff !important;
    }
    .fi-sidebar-item.fi-active .fi-sidebar-item-label,
    .fi-sidebar-item.fi-sidebar-item-active .fi-sidebar-item-label {
        color: #1d4ed8 !important;
        font-weight: 600 !important;
    }
    .fi-sidebar-item.fi-active .fi-sidebar-item-icon,
    .fi-sidebar-item.fi-sidebar-item-active .fi-sidebar-item-icon {
        color: #2563eb !important;
    }
    /* Active left accent bar */
    .fi-sidebar-item.fi-active > .fi-sidebar-item-button::before,
    .fi-sidebar-item.fi-sidebar-item-active > .fi-sidebar-item-button::before {
        content: '' !important;
        position: absolute !important;
        left: 0 !important;
        top: 0.375rem !important;
        bottom: 0.375rem !important;
        width: 3px !important;
        background: #3b82f6 !important;
        border-radius: 0 2px 2px 0 !important;
    }

    /* --- Sub-group connectors --- */
    .fi-sidebar-item-grouped-border .absolute {
        background-color: #e2e8f0 !important;
    }
    .fi-sidebar-item-grouped-border > .relative:not(.absolute) {
        background-color: #cbd5e1 !important;
    }
    .fi-sidebar-item.fi-active .fi-sidebar-item-grouped-border > .relative:not(.absolute) {
        background-color: #3b82f6 !important;
    }

    /* --- Group icon (collapsed dropdown trigger) --- */
    .fi-sidebar-group .fi-sidebar-group-icon {
        color: #94a3b8 !important;
    }
    .fi-sidebar-group.fi-active .fi-sidebar-group-icon {
        color: #2563eb !important;
    }
    .fi-sidebar-group button:hover {
        background: #f1f5f9 !important;
    }

    /* --- Scrollbar --- */
    .fi-sidebar-nav::-webkit-scrollbar {
        width: 4px;
    }
    .fi-sidebar-nav::-webkit-scrollbar-track {
        background: transparent;
    }
    .fi-sidebar-nav::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 2px;
    }
    .fi-sidebar-nav::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }

    /* --- Footer collapse button --- */
    .fi-sidebar-footer-collapse {
        background: #f8fafc !important;
        border-top: 1px solid #e2e8f0 !important;
    }
    .fi-sidebar-footer-collapse button {
        color: #94a3b8 !important;
    }
    .fi-sidebar-footer-collapse button:hover {
        color: #0f172a !important;
        background: #f1f5f9 !important;
    }

    /* --- Mobile overlay blur --- */
    .fi-sidebar-close-overlay {
        backdrop-filter: blur(2px);
    }

    /* ========================================
       SIDEBAR — DARK MODE
       Deep navy sidebar with white text
       ======================================== */

    /* --- Base sidebar container --- */
    .dark .fi-sidebar {
        background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;
        border-right: 1px solid rgba(255,255,255,0.06) !important;
    }

    /* --- Header: brand area --- */
    .dark .fi-sidebar-header {
        border-bottom: 1px solid rgba(255,255,255,0.08) !important;
    }
    .dark .fi-sidebar-header,
    .dark .fi-sidebar-header a,
    .dark .fi-sidebar-header span,
    .dark .fi-sidebar-header svg {
        color: #ffffff !important;
    }
    .dark .fi-sidebar-header > .mx-auto {
        color: rgba(255,255,255,0.7) !important;
    }
    .dark .fi-sidebar-header > .mx-auto:hover {
        background: rgba(255,255,255,0.08) !important;
        color: #ffffff !important;
    }

    /* --- Group labels --- */
    .dark .fi-sidebar-group-label {
        color: rgba(255,255,255,0.4) !important;
    }

    /* --- Group collapse chevron --- */
    .dark .fi-sidebar-group-collapse-button {
        color: rgba(255,255,255,0.3) !important;
    }
    .dark .fi-sidebar-group-collapse-button:hover {
        color: rgba(255,255,255,0.7) !important;
        background: rgba(255,255,255,0.06) !important;
    }

    /* --- Nav items: default --- */
    .dark .fi-sidebar-item-label {
        color: rgba(255,255,255,0.7) !important;
    }
    .dark .fi-sidebar-item-icon {
        color: rgba(255,255,255,0.4) !important;
    }

    /* --- Nav items: hover --- */
    .dark .fi-sidebar-item-button:hover {
        background: rgba(255,255,255,0.08) !important;
    }
    .dark .fi-sidebar-item-button:hover .fi-sidebar-item-label {
        color: #ffffff !important;
    }
    .dark .fi-sidebar-item-button:hover .fi-sidebar-item-icon {
        color: rgba(255,255,255,0.9) !important;
    }

    /* --- Nav items: active --- */
    .dark .fi-sidebar-item.fi-active > .fi-sidebar-item-button,
    .dark .fi-sidebar-item.fi-sidebar-item-active > .fi-sidebar-item-button {
        background: rgba(59, 130, 246, 0.15) !important;
    }
    .dark .fi-sidebar-item.fi-active .fi-sidebar-item-label,
    .dark .fi-sidebar-item.fi-sidebar-item-active .fi-sidebar-item-label {
        color: #60a5fa !important;
    }
    .dark .fi-sidebar-item.fi-active .fi-sidebar-item-icon,
    .dark .fi-sidebar-item.fi-sidebar-item-active .fi-sidebar-item-icon {
        color: #60a5fa !important;
    }

    /* --- Sub-group connectors --- */
    .dark .fi-sidebar-item-grouped-border .absolute {
        background-color: rgba(255,255,255,0.12) !important;
    }
    .dark .fi-sidebar-item-grouped-border > .relative:not(.absolute) {
        background-color: rgba(255,255,255,0.3) !important;
    }

    /* --- Group icon --- */
    .dark .fi-sidebar-group .fi-sidebar-group-icon {
        color: rgba(255,255,255,0.4) !important;
    }
    .dark .fi-sidebar-group.fi-active .fi-sidebar-group-icon {
        color: #60a5fa !important;
    }
    .dark .fi-sidebar-group button:hover {
        background: rgba(255,255,255,0.08) !important;
    }

    /* --- Scrollbar --- */
    .dark .fi-sidebar-nav::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.1);
    }
    .dark .fi-sidebar-nav::-webkit-scrollbar-thumb:hover {
        background: rgba(255,255,255,0.2);
    }

    /* --- Footer collapse button --- */
    .dark .fi-sidebar-footer-collapse {
        background: rgba(0,0,0,0.15) !important;
        border-top: 1px solid rgba(255,255,255,0.06) !important;
    }
    .dark .fi-sidebar-footer-collapse button {
        color: rgba(255,255,255,0.5) !important;
    }
    .dark .fi-sidebar-footer-collapse button:hover {
        color: #ffffff !important;
        background: rgba(255,255,255,0.08) !important;
    }

    /* ========================================
       TOP NAVIGATION — SINGLE-LINE SCROLL
       ======================================== */
    .fi-topbar-nav-groups {
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
        overflow-y: visible !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
        scroll-behavior: smooth !important;
        min-width: 0 !important;
        flex: 1 1 0% !important;
    }
    .fi-topbar-nav-groups::-webkit-scrollbar {
        display: none !important;
    }

    /* Scroll arrow buttons — positioned inside <nav> */
    .fi-topbar-scroll-btn {
        display: none;
        z-index: 10;
        width: 28px;
        height: 28px;
        flex-shrink: 0;
        border-radius: 50%;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        transition: all 150ms ease;
        padding: 0;
    }
    .fi-topbar-scroll-btn:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    }
    .fi-topbar-scroll-btn.is-visible {
        display: inline-flex;
    }
    .fi-topbar-scroll-btn svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    .dark .fi-topbar-scroll-btn {
        border-color: rgba(255,255,255,0.12) !important;
        background: #1e293b !important;
        color: rgba(255,255,255,0.7) !important;
        box-shadow: 0 1px 4px rgba(0,0,0,0.3);
    }
    .dark .fi-topbar-scroll-btn:hover {
        background: #334155 !important;
        color: #ffffff !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const navGroups = document.querySelector('.fi-topbar-nav-groups');
    if (!navGroups) return;

    const nav = navGroups.closest('.fi-topbar');
    if (!nav) return;

    const btnLeft = document.createElement('button');
    btnLeft.type = 'button';
    btnLeft.className = 'fi-topbar-scroll-btn fi-topbar-scroll-btn-left';
    btnLeft.setAttribute('aria-label', 'Scroll kiri');
    btnLeft.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>';

    const btnRight = document.createElement('button');
    btnRight.type = 'button';
    btnRight.className = 'fi-topbar-scroll-btn fi-topbar-scroll-btn-right';
    btnRight.setAttribute('aria-label', 'Scroll kanan');
    btnRight.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>';

    navGroups.before(btnLeft);
    navGroups.after(btnRight);

    function updateScrollState() {
        const scrollLeft = navGroups.scrollLeft;
        const maxScroll = navGroups.scrollWidth - navGroups.clientWidth;
        const hasOverflowLeft = scrollLeft > 2;
        const hasOverflowRight = maxScroll > 2 && scrollLeft < maxScroll - 2;

        btnLeft.classList.toggle('is-visible', hasOverflowLeft);
        btnRight.classList.toggle('is-visible', hasOverflowRight);
    }

    btnLeft.addEventListener('click', function () {
        navGroups.scrollBy({ left: -200, behavior: 'smooth' });
    });
    btnRight.addEventListener('click', function () {
        navGroups.scrollBy({ left: 200, behavior: 'smooth' });
    });

    navGroups.addEventListener('scroll', updateScrollState, { passive: true });
    window.addEventListener('resize', updateScrollState);

    setTimeout(updateScrollState, 100);
    document.addEventListener('livewire:navigated', function () {
        setTimeout(updateScrollState, 100);
    });
});
</script>
