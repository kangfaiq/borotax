<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Borotax - Portal Pajak Online Kabupaten Bojonegoro. Layanan pajak daerah digital yang memudahkan masyarakat.">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Borotax - Portal Pajak Online Bojonegoro'); ?></title>

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>


    <style>
        /* ============================================
           DESIGN SYSTEM - Borotax Portal (Crafto-Inspired)
           ============================================ */
        :root {
            --primary: #6CACCF;
            --primary-rgb: 108, 172, 207;
            --primary-dark: #4A8BAE;
            --primary-darker: #367A9C;
            --primary-light: #A8D4E8;
            --primary-lighter: #D4EAF4;
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

            --bg-body: #FAFBFD;
            --bg-card: #FFFFFF;
            --bg-surface: #F1F5F9;
            --bg-surface-variant: #F8FAFB;
            --bg-dark: #0F1724;
            --bg-dark-2: #151E2F;

            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --text-tertiary: #94A3B8;
            --text-white: #FFFFFF;
            --text-white-muted: rgba(255,255,255,0.7);

            --border: #E2E8F0;
            --border-light: #F1F5F9;

            --shadow-xs: 0 1px 2px rgba(0,0,0,0.04);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.08), 0 8px 10px -6px rgba(0,0,0,0.04);
            --shadow-2xl: 0 25px 50px -12px rgba(0,0,0,0.15);
            --shadow-primary: 0 8px 24px rgba(var(--primary-rgb), 0.3);
            --shadow-card: 0 1px 3px rgba(0,0,0,0.04), 0 6px 16px rgba(0,0,0,0.04);

            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-2xl: 24px;
            --radius-full: 9999px;

            --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ============================================
           RESET & BASE
           ============================================ */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 80px; }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-secondary);
            background-color: var(--bg-body);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-size: 15px;
            overflow-x: hidden;
        }

        img { max-width: 100%; height: auto; }
        a { color: inherit; text-decoration: none; }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

        /* ============================================
           BUTTONS (Crafto pill-style)
           ============================================ */
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
            box-shadow: var(--shadow-primary);
        }

        .btn-secondary {
            background: var(--secondary);
            color: var(--text-white);
            border-color: var(--secondary);
        }
        .btn-secondary:hover {
            background: var(--secondary-light);
            border-color: var(--secondary-light);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border-color: var(--primary);
        }
        .btn-outline:hover {
            background: var(--primary);
            color: var(--text-white);
            transform: translateY(-2px);
        }

        .btn-outline-white {
            background: transparent;
            color: var(--text-white);
            border-color: rgba(255,255,255,0.4);
        }
        .btn-outline-white:hover {
            background: rgba(255,255,255,0.12);
            border-color: rgba(255,255,255,0.7);
            transform: translateY(-2px);
        }

        .btn-white {
            background: var(--text-white);
            color: var(--secondary);
            border-color: var(--text-white);
        }
        .btn-white:hover {
            background: rgba(255,255,255,0.9);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }

        .btn-lg { padding: 15px 36px; font-size: 0.95rem; }
        .btn-sm { padding: 8px 20px; font-size: 0.82rem; }

        /* ============================================
           TYPOGRAPHY HELPERS
           ============================================ */
        .section-header { text-align: center; margin-bottom: 56px; }

        .section-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 18px;
            background: var(--primary-50);
            color: var(--primary-dark);
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: var(--radius-full);
            margin-bottom: 16px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .section-badge-dark {
            background: rgba(var(--primary-rgb), 0.15);
            color: var(--primary-light);
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 14px;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .section-title-white { color: var(--text-white); }

        .section-subtitle {
            color: var(--text-secondary);
            font-size: 1.05rem;
            max-width: 560px;
            margin: 0 auto;
            line-height: 1.7;
        }

        .section-subtitle-white { color: var(--text-white-muted); }

        .text-gradient {
            background: linear-gradient(135deg, var(--primary-light), var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ============================================
           GRIDS
           ============================================ */
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }

        /* ============================================
           NAVBAR (Crafto-style transparent -> solid)
           ============================================ */
        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            transition: all var(--transition);
        }

        .navbar:not(.scrolled) { background: transparent; }

        .navbar.scrolled {
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }

        /* For pages that need solid navbar from start */
        .navbar.navbar-light {
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }

        .navbar.navbar-light .navbar-logo { color: var(--secondary); }
        .navbar.navbar-light .navbar-links a { color: var(--text-secondary); }
        .navbar.navbar-light .navbar-links a:hover { color: var(--primary-dark); background: var(--primary-50); }
        .navbar.navbar-light .navbar-toggle span { background: var(--secondary); }

        .navbar-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 76px;
        }

        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-white);
            transition: color var(--transition);
            letter-spacing: -0.01em;
        }

        .navbar.scrolled .navbar-logo { color: var(--secondary); }

        .navbar-logo img { height: 38px; width: auto; }
        .navbar-logo .logo-accent { color: var(--primary); }

        .navbar-links {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .navbar-links a {
            padding: 8px 16px;
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--text-white-muted);
            border-radius: var(--radius-full);
            transition: all var(--transition);
        }

        .navbar.scrolled .navbar-links a { color: var(--text-secondary); }

        .navbar-links a:hover {
            color: var(--text-white);
            background: rgba(255,255,255,0.1);
        }

        .navbar.scrolled .navbar-links a:hover {
            color: var(--primary-dark);
            background: var(--primary-50);
        }

        .navbar-cta {
            margin-left: 12px;
            padding: 9px 22px !important;
            background: var(--primary) !important;
            color: var(--text-white) !important;
            border-radius: var(--radius-full) !important;
            font-weight: 600 !important;
            font-size: 0.85rem !important;
            transition: all var(--transition) !important;
            border: 2px solid var(--primary) !important;
        }
        .navbar-cta:hover {
            background: var(--primary-dark) !important;
            border-color: var(--primary-dark) !important;
            transform: translateY(-1px);
            box-shadow: var(--shadow-primary);
        }

        .navbar-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: var(--radius-sm);
            flex-direction: column;
            gap: 5px;
        }

        .navbar-toggle span {
            display: block;
            width: 24px;
            height: 2px;
            background: var(--text-white);
            border-radius: 2px;
            transition: all var(--transition);
        }

        .navbar.scrolled .navbar-toggle span { background: var(--secondary); }

        .navbar-mobile {
            display: none;
            position: fixed;
            top: 76px; left: 0; right: 0;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            padding: 16px 20px;
            box-shadow: var(--shadow-xl);
            z-index: 999;
        }

        .navbar-mobile.active { display: block; }

        .navbar-mobile a {
            display: block;
            padding: 12px 16px;
            color: var(--text-secondary);
            font-weight: 500;
            border-radius: var(--radius-sm);
            transition: all var(--transition);
        }

        .navbar-mobile a:hover {
            background: var(--primary-50);
            color: var(--primary-dark);
        }

        /* Navbar Dropdown */
        .navbar-dropdown {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .navbar-dropdown-toggle {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
        }

        .navbar-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            min-width: 220px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-xl);
            padding: 8px;
            z-index: 1001;
        }

        .navbar-dropdown-menu::before {
            content: '';
            position: absolute;
            top: -6px;
            left: 50%;
            transform: translateX(-50%) rotate(45deg);
            width: 12px;
            height: 12px;
            background: var(--bg-card);
            border-left: 1px solid var(--border);
            border-top: 1px solid var(--border);
        }

        .navbar-dropdown:hover .navbar-dropdown-menu { display: block; }

        .navbar-dropdown-menu a {
            display: flex !important;
            align-items: center;
            gap: 10px;
            padding: 10px 14px !important;
            font-size: 0.85rem !important;
            color: var(--text-secondary) !important;
            border-radius: var(--radius-sm) !important;
            white-space: nowrap;
        }

        .navbar-dropdown-menu a:hover {
            background: var(--primary-50) !important;
            color: var(--primary-dark) !important;
        }

        .navbar-dropdown-menu a i {
            font-size: 1rem;
            color: var(--primary);
            width: 20px;
            text-align: center;
        }

        /* Mobile group */
        .navbar-mobile-group {
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            margin: 4px 0;
            padding: 4px 0;
        }

        .navbar-mobile-group-title {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px 4px;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .navbar-mobile-group a {
            padding-left: 28px !important;
            font-size: 0.88rem;
        }

        .navbar-mobile-group a i {
            margin-right: 6px;
            color: var(--primary);
        }

        /* ============================================
           CARDS BASE
           ============================================ */
        .card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            overflow: hidden;
            transition: all var(--transition);
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-light);
        }

        /* ============================================
           FOOTER (Crafto-style dark)
           ============================================ */
        .footer {
            background: var(--bg-dark);
            color: var(--text-white-muted);
            padding: 80px 0 0;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0; left: 50%;
            transform: translateX(-50%);
            width: 600px; height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(var(--primary-rgb), 0.06) 0%, transparent 70%);
            pointer-events: none;
        }

        .footer-top {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
            gap: 48px;
            padding-bottom: 48px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            position: relative;
        }

        .footer-brand-desc {
            margin-top: 16px;
            line-height: 1.8;
            font-size: 0.9rem;
        }

        .footer-social {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .footer-social a {
            width: 38px; height: 38px;
            border-radius: var(--radius-full);
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-white-muted);
            font-size: 0.95rem;
            transition: all var(--transition);
        }

        .footer-social a:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--text-white);
            transform: translateY(-2px);
        }

        .footer-heading {
            color: var(--text-white);
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 22px;
        }

        .footer-link-list { list-style: none; }
        .footer-link-list li + li { margin-top: 12px; }

        .footer-link-list a {
            font-size: 0.9rem;
            color: var(--text-white-muted);
            transition: all var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .footer-link-list a:hover {
            color: var(--primary-light);
            transform: translateX(3px);
        }

        .footer-newsletter-form {
            display: flex;
            gap: 8px;
        }

        .footer-newsletter-form input {
            flex: 1;
            padding: 11px 16px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius-full);
            color: var(--text-white);
            font-size: 0.88rem;
            font-family: inherit;
            outline: none;
            transition: all var(--transition);
        }

        .footer-newsletter-form input::placeholder { color: rgba(255,255,255,0.35); }
        .footer-newsletter-form input:focus {
            border-color: var(--primary);
            background: rgba(255,255,255,0.08);
        }

        .footer-newsletter-form button {
            padding: 11px 20px;
            background: var(--primary);
            color: var(--text-white);
            border: none;
            border-radius: var(--radius-full);
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all var(--transition);
            white-space: nowrap;
            font-family: inherit;
        }

        .footer-newsletter-form button:hover { background: var(--primary-dark); }

        .footer-bottom {
            padding: 24px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.82rem;
            color: rgba(255,255,255,0.4);
        }

        .footer-bottom-links { display: flex; gap: 24px; }
        .footer-bottom-links a {
            color: rgba(255,255,255,0.4);
            transition: color var(--transition);
        }
        .footer-bottom-links a:hover { color: var(--primary-light); }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 1024px) {
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .footer-top { grid-template-columns: 1fr 1fr; gap: 32px; }
            .section-title { font-size: 2rem; }
        }

        @media (max-width: 768px) {
            .navbar-links { display: none; }
            .navbar-toggle { display: flex; }
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .section-title { font-size: 1.7rem; }
            .section-subtitle { font-size: 0.95rem; }
            .footer-top { grid-template-columns: 1fr; gap: 32px; }
            .container { padding: 0 16px; }
            .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
        }

        /* ============================================
           ANIMATIONS
           ============================================ */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes floatSlow {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(2deg); }
        }

        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.5); opacity: 0; }
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Fallback: show all reveals if JS is disabled */
        noscript + style .reveal, .no-js .reveal {
            opacity: 1 !important;
            transform: none !important;
        }
    </style>

    <noscript><style>.reveal{opacity:1!important;transform:none!important;}</style></noscript>

    <?php echo $__env->yieldContent('styles'); ?>
</head>

<body>
    
    <nav class="navbar <?php echo $__env->yieldContent('navbar-class'); ?>" id="navbar">
        <div class="container navbar-inner">
            <a href="<?php echo e(url('/')); ?>" class="navbar-logo">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(file_exists(public_path('images/logo-pemkab.png'))): ?>
                    <img src="<?php echo e(asset('images/logo-pemkab.png')); ?>" alt="Logo Pemkab Bojonegoro">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                Boro<span class="logo-accent">tax</span>
            </a>

            <div class="navbar-links">
                <?php echo $__env->yieldContent('nav-links'); ?>
            </div>

            <button class="navbar-toggle" id="navToggle" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    
    <div class="navbar-mobile" id="navMobile">
        <?php echo $__env->yieldContent('nav-mobile-links'); ?>
    </div>

    
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div>
                    <div class="navbar-logo" style="color: white; font-size: 1.5rem;">
                        Boro<span class="logo-accent">tax</span>
                    </div>
                    <p class="footer-brand-desc">
                        Badan Pendapatan Daerah Kabupaten Bojonegoro.<br>
                        Jl. P. Mas Tumapel No. 1 Bojonegoro, Jawa Timur.
                    </p>
                    <div class="footer-social">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                        <a href="#"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="footer-heading">Layanan</h4>
                    <ul class="footer-link-list">
                        <li><a href="<?php echo e(url('/cek-billing')); ?>"><i class="bi bi-chevron-right" style="font-size:0.7rem;"></i> Cek Billing</a></li>
                        <li><a href="<?php echo e(url('/login')); ?>"><i class="bi bi-chevron-right" style="font-size:0.7rem;"></i> Portal Wajib Pajak</a></li>
                        <li><a href="<?php echo e(url('/sewa-reklame')); ?>"><i class="bi bi-chevron-right" style="font-size:0.7rem;"></i> Sewa Reklame</a></li>
                        <li><a href="<?php echo e(url('/kalkulator-sanksi')); ?>"><i class="bi bi-chevron-right" style="font-size:0.7rem;"></i> Kalkulator Sanksi</a></li>
                        <li><a href="<?php echo e(url('/kalkulator-reklame')); ?>"><i class="bi bi-chevron-right" style="font-size:0.7rem;"></i> Kalkulator Reklame</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-heading">Informasi</h4>
                    <ul class="footer-link-list">
                        <li><a href="<?php echo e(url('/produk-hukum')); ?>"><i class="bi bi-chevron-right" style="font-size:0.7rem;"></i> Produk Hukum</a></li>
                        <li><a href="<?php echo e(url('/kalkulator-air-tanah')); ?>"><i class="bi bi-chevron-right" style="font-size:0.7rem;"></i> Kalkulator Air Tanah</a></li>
                        <li><a href="<?php echo e(url('/berita')); ?>"><i class="bi bi-chevron-right" style="font-size:0.7rem;"></i> Berita Terbaru</a></li>
                        <li><a href="<?php echo e(url('/destinasi')); ?>"><i class="bi bi-chevron-right" style="font-size:0.7rem;"></i> Destinasi Wisata</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-heading">Hubungi Kami</h4>
                    <ul class="footer-link-list" style="margin-bottom: 20px;">
                        <li><a href="mailto:bapenda@bojonegorokab.go.id"><i class="bi bi-envelope"></i> bapenda@bojonegorokab.go.id</a></li>
                        <li><a href="tel:+62353881826"><i class="bi bi-telephone"></i> (0353) 881826</a></li>
                        <li><a href="https://wa.me/62085172330531" target="_blank"><i class="bi bi-whatsapp"></i> CS 1: 085172330531</a></li>
                        <li><a href="https://wa.me/62085172240531" target="_blank"><i class="bi bi-whatsapp"></i> CS 2: 085172240531</a></li>
                        <li><a href="https://wa.me/62082233099997" target="_blank"><i class="bi bi-whatsapp"></i> CS 3: 082233099997</a></li>
                    </ul>
                    <p style="font-size:0.85rem; margin-bottom: 12px; color: var(--text-white-muted);">Dapatkan info terbaru</p>
                    <div class="footer-newsletter-form">
                        <input type="email" placeholder="Email Anda">
                        <button type="button">Kirim</button>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo e(date('Y')); ?> Badan Pendapatan Daerah Kabupaten Bojonegoro. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Kebijakan Privasi</a>
                    <a href="#">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

    
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>


    
    <script>
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 40);
        });

        const toggle = document.getElementById('navToggle');
        const mobile = document.getElementById('navMobile');
        toggle.addEventListener('click', () => mobile.classList.toggle('active'));
        mobile.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => mobile.classList.remove('active'));
        });

        // Reveal-on-scroll
        const revealElements = document.querySelectorAll('.reveal');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        revealElements.forEach(el => revealObserver.observe(el));
    </script>

    <?php echo $__env->yieldContent('scripts'); ?>
</body>

</html>
<?php /**PATH F:\Worx\laragon\www\borotax\resources\views/layouts/portal-guest.blade.php ENDPATH**/ ?>