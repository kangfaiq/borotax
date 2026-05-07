<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Wajib Pajak - Borotax')</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @livewireStyles

    <style>
        /* ============================================
           DESIGN SYSTEM TOKENS
           ============================================ */
        :root {
            --primary: #6CACCF;
            --primary-rgb: 108, 172, 207;
            --primary-dark: #4A8BAE;
            --primary-darker: #367A9C;
            --primary-light: #A8D4E8;
            --primary-50: #EBF5FA;

            --secondary: #232A3E;
            --secondary-light: #3A4460;
            --secondary-dark: #181D2E;
            --secondary-rgb: 35, 42, 62;

            --accent: #F9A826;
            --accent-light: #FFF3DC;

            --success: #22C55E;
            --success-light: #DCFCE7;
            --warning: #F59E0B;
            --warning-light: #FEF3C7;
            --error: #EF4444;
            --error-light: #FEE2E2;
            --info: #3B82F6;
            --info-light: #DBEAFE;

            --bg-body: #F1F5F9;
            --bg-card: #FFFFFF;
            --bg-surface: #F8FAFB;
            --bg-surface-variant: #F1F5F9;
            --bg-dark: #0F1724;

            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --text-tertiary: #94A3B8;
            --text-white: #FFFFFF;

            --border: #E2E8F0;
            --border-light: #F1F5F9;

            --shadow-xs: 0 1px 2px rgba(0,0,0,0.04);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.05);

            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-full: 9999px;

            --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);

            /* Sidebar */
            --sidebar-w: 260px;
            --sidebar-bg: #0F172A;
            --sidebar-text: rgba(255,255,255,0.6);
            --sidebar-text-active: #FFFFFF;
            --sidebar-hover: rgba(255,255,255,0.06);
            --sidebar-active: rgba(108,172,207,0.15);
            --sidebar-border: rgba(255,255,255,0.06);

            /* Topbar */
            --topbar-h: 64px;
        }

        /* ============================================
           RESET & BASE
           ============================================ */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-secondary);
            background: var(--bg-body);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            font-size: 15px;
            overflow-x: hidden;
        }

        a { color: inherit; text-decoration: none; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 28px;
            border-radius: var(--radius-full);
            font-weight: 600;
            font-size: 0.9rem;
            font-family: inherit;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all var(--transition);
            text-decoration: none;
            line-height: 1.4;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--text-white);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(var(--primary-rgb), 0.24);
        }

        .btn-primary:focus-visible {
            outline: 3px solid rgba(var(--primary-rgb), 0.22);
            outline-offset: 2px;
        }

        /* ============================================
           SIDEBAR
           ============================================ */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: transform var(--transition);
        }

        .sidebar-brand {
            height: var(--topbar-h);
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid var(--sidebar-border);
            gap: 12px;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 0.9rem;
        }

        .sidebar-brand .brand-text {
            color: var(--sidebar-text-active);
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: -0.02em;
        }

        .sidebar-brand .brand-text span {
            display: block;
            font-size: 0.65rem;
            font-weight: 500;
            color: var(--sidebar-text);
            letter-spacing: 0;
            margin-top: -2px;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 16px 12px;
        }

        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        .nav-section {
            margin-bottom: 22px;
        }

        .nav-section-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--sidebar-text);
            padding: 0 12px;
            margin-bottom: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: var(--radius-md);
            color: var(--sidebar-text);
            font-size: 0.88rem;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition);
            margin-bottom: 2px;
        }

        .nav-item:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-text-active);
        }

        .nav-item.active {
            background: var(--sidebar-active);
            color: var(--primary-light);
            font-weight: 600;
        }

        .nav-item i {
            font-size: 1.1rem;
            width: 22px;
            text-align: center;
            flex-shrink: 0;
        }

        .nav-item .nav-badge {
            margin-left: auto;
            background: var(--error);
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: var(--radius-full);
            min-width: 20px;
            text-align: center;
        }

        /* Sidebar footer */
        .sidebar-footer {
            padding: 14px 16px;
            border-top: 1px solid var(--sidebar-border);
            flex-shrink: 0;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-avatar {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.82rem;
            flex-shrink: 0;
        }

        .sidebar-user-info {
            flex: 1;
            min-width: 0;
        }

        .sidebar-user-name {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--sidebar-text-active);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 0.68rem;
            color: var(--sidebar-text);
        }

        .sidebar-logout {
            background: none;
            border: none;
            color: var(--sidebar-text);
            cursor: pointer;
            padding: 6px;
            border-radius: var(--radius-sm);
            transition: all var(--transition);
            font-size: 1rem;
        }

        .sidebar-logout:hover {
            background: rgba(239,68,68,0.15);
            color: var(--error);
        }

        /* ============================================
           MAIN CONTENT AREA
           ============================================ */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        .topbar {
            height: var(--topbar-h);
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.3rem;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 6px;
            border-radius: var(--radius-sm);
        }

        .mobile-toggle:hover { background: var(--bg-surface-variant); }

        .topbar-breadcrumb {
            font-size: 0.88rem;
            color: var(--text-tertiary);
        }

        .topbar-breadcrumb a {
            color: var(--text-tertiary);
            transition: color var(--transition);
        }

        .topbar-breadcrumb a:hover { color: var(--primary-dark); }

        .topbar-breadcrumb .bc-sep {
            margin: 0 6px;
            color: var(--border);
        }

        .topbar-breadcrumb .bc-current {
            color: var(--text-primary);
            font-weight: 600;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .topbar-date {
            font-size: 0.82rem;
            color: var(--text-tertiary);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .topbar-password-indicator {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: var(--radius-full);
            border: 1px solid var(--border);
            background: var(--bg-main);
            color: inherit;
            text-decoration: none;
            transition: all var(--transition);
        }

        .topbar-password-indicator:hover {
            border-color: var(--primary);
            background: var(--primary-50);
        }

        .topbar-password-indicator.warning {
            border-color: rgba(239, 68, 68, 0.28);
            background: linear-gradient(180deg, rgba(254, 242, 242, 0.98), rgba(255, 247, 237, 0.98));
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.1);
        }

        .topbar-password-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(var(--primary-rgb), 0.12);
            color: var(--primary-dark);
            font-size: 0.92rem;
            flex-shrink: 0;
        }

        .topbar-password-indicator.warning .topbar-password-icon {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(245, 158, 11, 0.18));
            color: #B91C1C;
        }

        .topbar-password-icon.warning-marker {
            position: relative;
        }

        .topbar-password-icon.warning-marker::after {
            content: '';
            position: absolute;
            inset: -5px;
            border-radius: 50%;
            border: 2px solid rgba(239, 68, 68, 0.2);
            opacity: 0;
        }

        .topbar-password-mobile-flag {
            display: none;
            align-items: center;
            gap: 5px;
            margin-left: 2px;
            padding: 3px 8px;
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, #DC2626, #F59E0B);
            color: #FFFFFF;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            box-shadow: 0 8px 18px rgba(220, 38, 38, 0.2);
        }

        .topbar-password-copy {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .topbar-password-label {
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--text-tertiary);
        }

        .topbar-password-value {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .topbar-password-hint {
            display: none;
            margin-top: 3px;
            width: max-content;
            padding: 2px 7px;
            border-radius: var(--radius-full);
            background: rgba(239, 68, 68, 0.12);
            color: #B91C1C;
            font-size: 0.56rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .topbar-password-indicator.warning .topbar-password-hint {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        @keyframes passwordWarningPulse {
            0% {
                opacity: 0;
                transform: scale(0.92);
            }

            35% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                transform: scale(1.28);
            }
        }

        @keyframes passwordWarningFloat {
            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-2px);
            }
        }

        .topbar-btn {
            width: 38px;
            height: 38px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            background: var(--bg-card);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all var(--transition);
            font-size: 1rem;
            position: relative;
        }

        .topbar-btn:hover {
            border-color: var(--primary);
            color: var(--primary-dark);
            background: var(--primary-50);
        }

        .topbar-btn .notif-dot {
            position: absolute;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--error);
            top: 8px;
            right: 9px;
        }

        .notif-badge {
            position: absolute;
            top: 4px;
            right: 2px;
            min-width: 18px;
            height: 18px;
            border-radius: 9px;
            background: var(--error, #e74c3c);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            line-height: 1;
        }

        .notif-dropdown {
            display: none;
            position: absolute;
            top: 50px;
            right: 0;
            width: 360px;
            max-height: 420px;
            background: var(--bg-card, #fff);
            border: 1px solid var(--border, #e0e0e0);
            border-radius: var(--radius-lg, 12px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            z-index: 1000;
            overflow: hidden;
        }

        .notif-dropdown.show {
            display: flex;
            flex-direction: column;
        }

        .notif-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border, #e0e0e0);
            font-size: 0.9rem;
        }

        .notif-mark-all {
            background: none;
            border: none;
            color: var(--primary, #6CACCF);
            font-size: 0.78rem;
            cursor: pointer;
            font-weight: 600;
        }

        .notif-mark-all:hover {
            text-decoration: underline;
        }

        .notif-list {
            overflow-y: auto;
            flex: 1;
        }

        .notif-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-light, #f0f0f0);
            cursor: pointer;
            transition: background 0.2s;
        }

        .notif-item:hover {
            background: var(--bg-main, #f8f9fa);
        }

        .notif-item.unread {
            background: rgba(var(--primary-rgb, 108,172,207), 0.08);
            border-left: 3px solid var(--primary, #6CACCF);
        }

        .notif-title {
            font-weight: 600;
            font-size: 0.82rem;
            color: var(--text-primary, #1a1a1a);
            margin-bottom: 2px;
        }

        .notif-body {
            font-size: 0.78rem;
            color: var(--text-secondary, #666);
            line-height: 1.4;
        }

        .notif-time {
            font-size: 0.7rem;
            color: var(--text-muted, #999);
            margin-top: 4px;
        }

        .notif-empty {
            padding: 32px 16px;
            text-align: center;
            color: var(--text-muted, #999);
            font-size: 0.85rem;
        }

        @media (max-width: 480px) {
            .notif-dropdown {
                width: calc(100vw - 24px);
                right: -8px;
            }
        }

        /* Page content */
        .page-content {
            flex: 1;
            padding: 28px;
        }

        .portal-flash-stack {
            display: grid;
            gap: 12px;
            margin-bottom: 20px;
        }

        .portal-flash {
            padding: 14px 16px;
            border-radius: var(--radius-md);
            border: 1px solid transparent;
            box-shadow: var(--shadow-sm);
        }

        .portal-flash.success {
            background: rgba(34, 197, 94, 0.12);
            border-color: rgba(34, 197, 94, 0.18);
            color: #166534;
        }

        .portal-flash.info {
            background: rgba(59, 130, 246, 0.10);
            border-color: rgba(59, 130, 246, 0.18);
            color: #1d4ed8;
        }

        /* ============================================
           BADGES (shared)
           ============================================ */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: var(--radius-full);
            font-size: 0.72rem;
            font-weight: 600;
        }

        .badge-pending { background: var(--warning-light); color: #E65100; }
        .badge-paid { background: var(--success-light); color: #2E7D32; }
        .badge-verified { background: var(--info-light); color: #1565C0; }
        .badge-expired { background: var(--bg-surface-variant); color: var(--text-tertiary); }
        .badge-rejected { background: var(--error-light); color: #C62828; }

        /* ============================================
           MOBILE OVERLAY
           ============================================ */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 99;
            opacity: 0;
            transition: opacity var(--transition);
        }

        .sidebar-overlay.show {
            opacity: 1;
        }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay.show {
                display: block;
                opacity: 1;
            }

            .main-wrapper {
                margin-left: 0;
            }

            .mobile-toggle {
                display: flex;
            }

            .page-content {
                padding: 20px 16px;
            }
        }

        @media (max-width: 640px) {
            .topbar { padding: 0 16px; }
            .topbar-date { display: none; }
            .topbar-password-indicator {
                padding: 8px 10px;
            }

            .topbar-password-label {
                display: none;
            }

            .topbar-password-value {
                max-width: 132px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                font-size: 0.68rem;
            }

            .topbar-password-indicator.warning {
                animation: passwordWarningFloat 2.2s ease-in-out infinite;
            }

            .topbar-password-indicator.warning .topbar-password-icon.warning-marker::after {
                animation: passwordWarningPulse 1.8s ease-out infinite;
            }

            .topbar-password-mobile-flag {
                display: inline-flex;
            }

        }

        @media (prefers-reduced-motion: reduce) {
            .topbar-password-indicator.warning,
            .topbar-password-indicator.warning .topbar-password-icon.warning-marker::after {
                animation: none !important;
            }
        }
    </style>

    @yield('styles')
</head>

<body>
    @php
        $portalUser = auth()->user();
        $passwordChangedAt = $portalUser?->password_changed_at?->timezone(config('app.timezone'));
        $passwordChangedLabel = $passwordChangedAt
            ? $passwordChangedAt->translatedFormat('d M Y, H:i')
            : 'Belum pernah diubah';
        $passwordStatusTooltip = $passwordChangedAt
            ? 'Password akun terakhir diperbarui pada ' . $passwordChangedLabel
            : 'Password akun belum pernah diubah. Segera perbarui untuk meningkatkan keamanan akun.';
    @endphp

    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">B</div>
            <div class="brand-text">
                Borotax
                <span>Portal Wajib Pajak</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-label">Menu Utama</div>
                <a href="{{ route('portal.dashboard') }}"
                   class="nav-item {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    Dashboard
                </a>
                <a href="{{ route('portal.history') }}"
                   class="nav-item {{ request()->routeIs('portal.history') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i>
                    Riwayat Transaksi
                </a>
                <a href="{{ route('portal.billing') }}"
                   class="nav-item {{ request()->routeIs('portal.billing') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    Cek Billing
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-label">Layanan Pajak</div>
                <a href="{{ route('portal.self-assessment.index') }}"
                   class="nav-item {{ request()->routeIs('portal.self-assessment.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>
                    Self Assessment
                </a>
                <a href="{{ route('portal.mblb-submissions.index') }}"
                   class="nav-item {{ request()->routeIs('portal.mblb-submissions.*') ? 'active' : '' }}">
                    <i class="bi bi-hourglass-split"></i>
                    Pengajuan MBLB
                </a>
                <a href="{{ route('portal.pembetulan.index') }}"
                   class="nav-item {{ request()->routeIs('portal.pembetulan.*') ? 'active' : '' }}">
                    <i class="bi bi-pencil-square"></i>
                    Ajukan Pembetulan
                </a>
                <a href="{{ route('portal.data-change-requests.index') }}"
                   class="nav-item {{ request()->routeIs('portal.data-change-requests.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right"></i>
                    Perubahan Data
                </a>
                <a href="{{ route('portal.air-tanah.index') }}"
                   class="nav-item {{ request()->routeIs('portal.air-tanah.*') ? 'active' : '' }}">
                    <i class="bi bi-droplet"></i>
                    Air Tanah
                </a>
                <a href="{{ route('portal.reklame.index') }}"
                   class="nav-item {{ request()->routeIs('portal.reklame.*') ? 'active' : '' }}">
                    <i class="bi bi-signpost-2"></i>
                    Reklame
                </a>
                <a href="{{ route('portal.stpd-manual.index') }}"
                   class="nav-item {{ request()->routeIs('portal.stpd-manual.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-ruled"></i>
                    STPD Manual
                </a>
                <a href="{{ route('portal.gebyar.index') }}"
                   class="nav-item {{ request()->routeIs('portal.gebyar.*') ? 'active' : '' }}">
                    <i class="bi bi-gift"></i>
                    Gebyar Pajak
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-label">Akun</div>
                <a href="{{ route('portal.password.edit') }}"
                   class="nav-item {{ request()->routeIs('portal.password.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i>
                    Ubah Password
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    {{ strtoupper(substr($portalUser->nama_lengkap ?? 'U', 0, 1)) }}
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ $portalUser->nama_lengkap }}</div>
                    <div class="sidebar-user-role">Wajib Pajak</div>
                </div>
                <form method="POST" action="{{ route('portal.logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="sidebar-logout" title="Keluar">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Mobile Overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    {{-- Main --}}
    <div class="main-wrapper">
        {{-- Topbar --}}
        <header class="topbar">
            <div class="topbar-left">
                <button class="mobile-toggle" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-breadcrumb">
                    <a href="{{ route('portal.dashboard') }}">Portal</a>
                    <span class="bc-sep">/</span>
                    <span class="bc-current">@yield('page-title', 'Dashboard')</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-date">
                    <i class="bi bi-calendar3"></i>
                    {{ now()->isoFormat('dddd, D MMMM Y') }}
                </div>
                <a href="{{ route('portal.password.edit') }}" class="topbar-password-indicator {{ $passwordChangedAt ? '' : 'warning' }}" title="{{ $passwordStatusTooltip }}">
                    <span class="topbar-password-icon {{ $passwordChangedAt ? '' : 'warning-marker' }}">
                        <i class="bi {{ $passwordChangedAt ? 'bi-shield-lock' : 'bi-shield-exclamation' }}"></i>
                    </span>
                    <span class="topbar-password-copy">
                        <span class="topbar-password-label">Password terakhir</span>
                        <span class="topbar-password-value">{{ $passwordChangedLabel }}</span>
                        @if (! $passwordChangedAt)
                            <span class="topbar-password-mobile-flag">
                                <i class="bi bi-bell-fill"></i>
                                Aksi
                            </span>
                            <span class="topbar-password-hint">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                Perlu diperbarui
                            </span>
                        @endif
                    </span>
                </a>
                <button class="topbar-btn" title="Notifikasi" onclick="toggleNotifDropdown()" id="notifBtn" style="position:relative;">
                    <i class="bi bi-bell"></i>
                    <span class="notif-badge" id="notifBadge" style="display:none;"></span>
                </button>
                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <strong>Notifikasi</strong>
                        <button onclick="markAllRead()" class="notif-mark-all" id="markAllBtn" style="display:none;">Tandai semua dibaca</button>
                    </div>
                    <div class="notif-list" id="notifList">
                        <div class="notif-empty">Memuat...</div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="page-content">
            @if (session('status') || session('session_notice'))
                <div class="portal-flash-stack">
                    @session('status')
                        <div class="portal-flash success">{{ $value }}</div>
                    @endsession

                    @session('session_notice')
                        <div class="portal-flash info">{{ $value }}</div>
                    @endsession
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @livewireScripts

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        }

        // Notification System
        const notifApiBase = '/portal/notifications';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function notifHeaders() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            };
        }

        function toggleNotifDropdown() {
            const dd = document.getElementById('notifDropdown');
            dd.classList.toggle('show');
            if (dd.classList.contains('show')) loadNotifications();
        }

        document.addEventListener('click', function(e) {
            const dd = document.getElementById('notifDropdown');
            const btn = document.getElementById('notifBtn');
            if (dd && btn && !dd.contains(e.target) && !btn.contains(e.target)) {
                dd.classList.remove('show');
            }
        });

        function loadNotifications() {
            fetch(notifApiBase + '?per_page=20', { headers: notifHeaders(), credentials: 'same-origin' })
                .then(r => r.json())
                .then(res => {
                    const list = document.getElementById('notifList');
                    const items = res.data?.data || res.data || [];
                    if (!items.length) {
                        list.innerHTML = '<div class="notif-empty">Tidak ada notifikasi</div>';
                        return;
                    }
                    list.innerHTML = items.map(n => {
                        const targetUrl = n.url || n.action_url || n.data_payload?.url || '';
                        const hasUrl = typeof targetUrl === 'string' && targetUrl.length > 0;
                        const encodedTargetUrl = hasUrl ? encodeURIComponent(targetUrl) : '';
                        return `
                        <div
                            class="notif-item ${n.is_read ? '' : 'unread'}"
                            data-notification-id="${n.id}"
                            data-notification-url="${encodedTargetUrl}"
                            role="button"
                            tabindex="0"
                        >
                            <div class="notif-title">${escapeHtml(n.title)}</div>
                            <div class="notif-body">${escapeHtml(n.body)}</div>
                            <div class="notif-time">${timeAgo(n.created_at)}</div>
                        </div>
                    `;
                    }).join('');

                    bindNotificationActions(list);
                })
                .catch(() => {
                    document.getElementById('notifList').innerHTML = '<div class="notif-empty">Gagal memuat notifikasi</div>';
                });
        }

        function bindNotificationActions(container) {
            container.querySelectorAll('.notif-item').forEach(el => {
                if (el.dataset.bound === 'true') {
                    return;
                }

                const runAction = () => {
                    const notificationId = el.dataset.notificationId;
                    const targetUrl = el.dataset.notificationUrl
                        ? decodeURIComponent(el.dataset.notificationUrl)
                        : '';

                    if (!notificationId) {
                        return;
                    }

                    if (targetUrl) {
                        openNotif(notificationId, el, targetUrl);

                        return;
                    }

                    markRead(notificationId, el);
                };

                el.dataset.bound = 'true';
                el.addEventListener('click', runAction);
                el.addEventListener('keydown', event => {
                    if (event.key !== 'Enter' && event.key !== ' ') {
                        return;
                    }

                    event.preventDefault();
                    runAction();
                });
            });
        }

        function loadUnreadCount() {
            fetch(notifApiBase + '/unread-count', { headers: notifHeaders(), credentials: 'same-origin' })
                .then(r => r.json())
                .then(res => {
                    const count = res.data?.unread_count || 0;
                    const badge = document.getElementById('notifBadge');
                    const markAllBtn = document.getElementById('markAllBtn');
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = '';
                        markAllBtn.style.display = '';
                    } else {
                        badge.style.display = 'none';
                        markAllBtn.style.display = 'none';
                    }
                })
                .catch(() => {});
        }

        function markRead(id, el) {
            fetch(notifApiBase + '/' + id + '/read', { method: 'POST', headers: notifHeaders(), credentials: 'same-origin' })
                .then(() => {
                    if (el) el.classList.remove('unread');
                    loadUnreadCount();
                });
        }

        function openNotif(id, el, url) {
            fetch(notifApiBase + '/' + id + '/read', { method: 'POST', headers: notifHeaders(), credentials: 'same-origin' })
                .then(() => {
                    if (el) el.classList.remove('unread');
                    loadUnreadCount();
                })
                .finally(() => {
                    window.location.href = url;
                });
        }

        function markAllRead() {
            fetch(notifApiBase + '/read-all', { method: 'POST', headers: notifHeaders(), credentials: 'same-origin' })
                .then(() => {
                    document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
                    loadUnreadCount();
                });
        }

        function escapeHtml(text) {
            const d = document.createElement('div');
            d.textContent = text || '';
            return d.innerHTML;
        }

        function timeAgo(dateStr) {
            const now = new Date();
            const date = new Date(dateStr);
            const diff = Math.floor((now - date) / 1000);
            if (diff < 60) return 'Baru saja';
            if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
            if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
            return Math.floor(diff / 86400) + ' hari lalu';
        }

        // Poll unread count every 60s
        loadUnreadCount();
        setInterval(loadUnreadCount, 60000);
    </script>

    @yield('scripts')
</body>
</html>
