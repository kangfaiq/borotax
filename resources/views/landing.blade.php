@extends('layouts.portal-guest')

@section('title', 'Borotax - Portal Pajak Online Kabupaten Bojonegoro')

@section('nav-links')
    <a href="#layanan">Layanan</a>
    <a href="#cara-kerja">Cara Kerja</a>
    <div class="navbar-dropdown">
        <a href="#layanan-publik" class="navbar-dropdown-toggle">Layanan Publik <i class="bi bi-chevron-down" style="font-size:0.7rem;"></i></a>
        <div class="navbar-dropdown-menu">
            <a href="{{ url('/cek-billing') }}"><i class="bi bi-receipt-cutoff"></i> Cek Billing</a>
            <a href="{{ url('/histori-pajak') }}"><i class="bi bi-clock-history"></i> Histori Pajak</a>
            <a href="{{ url('/sewa-reklame') }}"><i class="bi bi-megaphone"></i> Sewa Reklame</a>
            <a href="{{ url('/kalkulator-sanksi') }}"><i class="bi bi-calculator"></i> Kalkulator Sanksi</a>
            <a href="{{ url('/produk-hukum') }}"><i class="bi bi-journal-bookmark"></i> Produk Hukum</a>
            <a href="{{ url('/kalkulator-air-tanah') }}"><i class="bi bi-droplet"></i> Kalkulator Air Tanah</a>
            <a href="{{ url('/kalkulator-reklame') }}"><i class="bi bi-easel"></i> Kalkulator Reklame</a>
        </div>
    </div>
    <a href="{{ url('/destinasi') }}">Wisata</a>
    <a href="{{ url('/berita') }}">Berita</a>
    <a href="{{ url('/login') }}" class="navbar-cta">Login Wajib Pajak</a>
@endsection

@section('nav-mobile-links')
    <a href="#layanan">Layanan</a>
    <a href="#cara-kerja">Cara Kerja</a>
    <div class="navbar-mobile-group">
        <div class="navbar-mobile-group-title"><i class="bi bi-grid-fill" style="font-size:0.8rem;"></i> Layanan Publik</div>
        <a href="{{ url('/cek-billing') }}"><i class="bi bi-receipt-cutoff"></i> Cek Billing</a>
        <a href="{{ url('/histori-pajak') }}"><i class="bi bi-clock-history"></i> Histori Pajak</a>
        <a href="{{ url('/sewa-reklame') }}"><i class="bi bi-megaphone"></i> Sewa Reklame</a>
        <a href="{{ url('/kalkulator-sanksi') }}"><i class="bi bi-calculator"></i> Kalkulator Sanksi</a>
        <a href="{{ url('/produk-hukum') }}"><i class="bi bi-journal-bookmark"></i> Produk Hukum</a>
        <a href="{{ url('/kalkulator-air-tanah') }}"><i class="bi bi-droplet"></i> Kalkulator Air Tanah</a>
        <a href="{{ url('/kalkulator-reklame') }}"><i class="bi bi-easel"></i> Kalkulator Reklame</a>
    </div>
    <a href="{{ url('/destinasi') }}">Wisata</a>
    <a href="{{ url('/berita') }}">Berita</a>
    <a href="{{ url('/login') }}">Login Wajib Pajak</a>
@endsection

@section('styles')
<style>
/* ============================================
   HERO (Crafto-style with floating elements)
   ============================================ */
.hero {
    min-height: 100vh;
    background: linear-gradient(160deg, #0F1724 0%, #1A2744 40%, #1E3A5F 70%, #234B6E 100%);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    padding: 120px 0 80px;
}

.hero-bg-shapes {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}

.hero-bg-shapes .shape {
    position: absolute;
    border-radius: 50%;
}

.hero-bg-shapes .shape-1 {
    width: 500px; height: 500px;
    top: -150px; right: -100px;
    background: radial-gradient(circle, rgba(108,172,207,0.12) 0%, transparent 70%);
}

.hero-bg-shapes .shape-2 {
    width: 400px; height: 400px;
    bottom: -100px; left: -80px;
    background: radial-gradient(circle, rgba(108,172,207,0.08) 0%, transparent 70%);
}

.hero-bg-shapes .shape-3 {
    width: 200px; height: 200px;
    top: 40%; left: 30%;
    background: radial-gradient(circle, rgba(249,168,38,0.06) 0%, transparent 70%);
    animation: floatSlow 8s ease-in-out infinite;
}

.hero-pattern {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
    background-size: 40px 40px;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-inner {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

.hero-left {
    animation: fadeInUp 0.8s ease-out;
}

.hero-trust-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 8px 8px 20px;
    background: rgba(255,255,255,0.08);
    border-radius: var(--radius-full);
    margin-bottom: 28px;
    border: 1px solid rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
}

.hero-trust-badge span {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text-white-muted);
}

.hero-trust-badge .badge-tag {
    background: var(--primary);
    color: var(--text-white);
    font-size: 0.72rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: var(--radius-full);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.hero h1 {
    font-size: 3.2rem;
    font-weight: 800;
    color: var(--text-white);
    line-height: 1.15;
    margin-bottom: 20px;
    letter-spacing: -0.02em;
}

.hero h1 .highlight {
    position: relative;
    display: inline-block;
    isolation: isolate;
}

.hero h1 .highlight::after {
    content: '';
    position: absolute;
    bottom: 4px; left: 0; right: 0;
    height: 14px;
    background: linear-gradient(90deg, var(--accent), rgba(var(--primary-rgb), 0.5));
    border-radius: 4px;
    z-index: -1;
    opacity: 0.6;
}

.hero-desc {
    color: var(--text-white-muted);
    font-size: 1.1rem;
    line-height: 1.8;
    margin-bottom: 36px;
    max-width: 480px;
}

.hero-buttons {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 48px;
}

.hero-mini-stats {
    display: flex;
    gap: 40px;
}

.hero-mini-stat {
    display: flex;
    align-items: center;
    gap: 12px;
}

.hero-mini-stat-icon {
    width: 44px; height: 44px;
    border-radius: var(--radius-md);
    background: rgba(var(--primary-rgb), 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: var(--primary-light);
}

.hero-mini-stat-text .num {
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--text-white);
    line-height: 1.2;
}

.hero-mini-stat-text .label {
    font-size: 0.78rem;
    color: var(--text-white-muted);
}

/* Hero right – floating cards */
.hero-right {
    position: relative;
    height: 480px;
    animation: fadeIn 1s ease-out 0.3s both;
}

.hero-float-card {
    position: absolute;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: var(--radius-xl);
    padding: 28px 24px;
    color: var(--text-white);
    transition: all var(--transition);
}

.hero-float-card:hover {
    background: rgba(255,255,255,0.15);
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.hero-float-card .card-icon {
    width: 48px; height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin-bottom: 14px;
}

.hero-float-card h3 {
    font-size: 0.95rem;
    font-weight: 700;
    margin-bottom: 6px;
}

.hero-float-card p {
    font-size: 0.82rem;
    color: var(--text-white-muted);
    line-height: 1.5;
}

.hero-card-1 {
    top: 0; left: 20px;
    width: 220px;
    animation: float 6s ease-in-out infinite;
}

.hero-card-2 {
    top: 30px; right: 0;
    width: 240px;
    animation: float 6s ease-in-out infinite 1s;
}

.hero-card-3 {
    bottom: 60px; left: 40px;
    width: 230px;
    animation: float 6s ease-in-out infinite 2s;
}

.hero-card-4 {
    bottom: 20px; right: 20px;
    width: 200px;
    animation: float 6s ease-in-out infinite 0.5s;
}

.hero-card-1 .card-icon { background: rgba(108,172,207,0.2); }
.hero-card-2 .card-icon { background: rgba(34,197,94,0.2); }
.hero-card-3 .card-icon { background: rgba(249,168,38,0.2); }
.hero-card-4 .card-icon { background: rgba(59,130,246,0.2); }

/* ============================================
   MARQUEE BANNER (Crafto-style)
   ============================================ */
.marquee-banner {
    background: var(--primary);
    padding: 14px 0;
    overflow: hidden;
    position: relative;
}

.marquee-track {
    display: flex;
    gap: 60px;
    animation: marquee 30s linear infinite;
    white-space: nowrap;
}

.marquee-item {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--text-white);
    font-weight: 600;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.marquee-item i {
    font-size: 1.1rem;
}

.marquee-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    flex-shrink: 0;
}

@keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

/* ============================================
   FEATURES/LAYANAN (Crafto-style icon cards)
   ============================================ */
.features-section {
    padding: 100px 0;
    background: var(--bg-body);
}

.feature-card {
    background: var(--bg-card);
    border-radius: var(--radius-xl);
    border: 1px solid var(--border);
    padding: 36px 28px;
    text-align: center;
    transition: all var(--transition);
    position: relative;
    overflow: hidden;
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--primary);
    transform: scaleX(0);
    transition: transform var(--transition);
}

.feature-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-xl);
    border-color: transparent;
}

.feature-card:hover::before {
    transform: scaleX(1);
}

.feature-icon-wrap {
    width: 68px; height: 68px;
    border-radius: var(--radius-lg);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    margin-bottom: 20px;
    transition: all var(--transition);
}

.feature-card:hover .feature-icon-wrap {
    transform: scale(1.08);
}

.feature-icon-wrap.teal {
    background: var(--primary-50);
    color: var(--primary-dark);
}

.feature-icon-wrap.green {
    background: var(--success-light);
    color: var(--success);
}

.feature-icon-wrap.orange {
    background: var(--warning-light);
    color: var(--warning);
}

.feature-icon-wrap.blue {
    background: var(--info-light);
    color: var(--info);
}

.feature-card h3 {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 10px;
}

.feature-card p {
    font-size: 0.9rem;
    color: var(--text-secondary);
    line-height: 1.7;
}

.feature-card .feature-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 16px;
    color: var(--primary-dark);
    font-weight: 600;
    font-size: 0.85rem;
    transition: all var(--transition);
}

.feature-card .feature-link i {
    transition: transform var(--transition);
}

.feature-card .feature-link:hover {
    color: var(--primary-darker);
}

.feature-card .feature-link:hover i {
    transform: translateX(4px);
}

/* ============================================
   HOW IT WORKS (Crafto-style numbered steps)
   ============================================ */
.how-it-works {
    padding: 100px 0;
    background: var(--bg-surface);
    position: relative;
    overflow: hidden;
}

.how-it-works::before {
    content: '';
    position: absolute;
    top: 50%; right: -200px;
    width: 500px; height: 500px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(var(--primary-rgb), 0.06) 0%, transparent 70%);
    transform: translateY(-50%);
}

.steps-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
    position: relative;
}

.step-card {
    position: relative;
    background: var(--bg-card);
    border-radius: var(--radius-xl);
    padding: 40px 32px;
    border: 1px solid var(--border);
    transition: all var(--transition);
    text-align: center;
}

.step-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-xl);
    border-color: transparent;
}

.step-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 56px; height: 56px;
    border-radius: var(--radius-full);
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: var(--text-white);
    font-size: 1.2rem;
    font-weight: 800;
    margin-bottom: 24px;
    box-shadow: var(--shadow-primary);
    position: relative;
}

.step-number::after {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: var(--radius-full);
    border: 2px dashed rgba(var(--primary-rgb), 0.3);
}

.step-card h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 10px;
}

.step-card p {
    font-size: 0.9rem;
    color: var(--text-secondary);
    line-height: 1.7;
}

/* Connector lines between steps */
.step-connector {
    position: absolute;
    top: 60px;
    left: calc(33.33% - 16px);
    width: calc(33.33% + 32px);
    height: 2px;
    background: repeating-linear-gradient(90deg, var(--primary-light), var(--primary-light) 6px, transparent 6px, transparent 12px);
    z-index: 0;
}

.step-connector-2 {
    left: calc(66.66% - 16px);
}

/* ============================================
   SHOWCASE SECTIONS (Crafto alternating style)
   ============================================ */
.showcase-section {
    padding: 100px 0;
}

.showcase-section:nth-child(even) {
    background: var(--bg-surface);
}

.showcase-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

.showcase-grid.reverse {
    direction: rtl;
}

.showcase-grid.reverse > * {
    direction: ltr;
}

.showcase-visual {
    position: relative;
    border-radius: var(--radius-2xl);
    overflow: hidden;
}

.showcase-visual-card {
    background: linear-gradient(135deg, #1A2744 0%, #234B6E 100%);
    border-radius: var(--radius-2xl);
    padding: 48px 40px;
    position: relative;
    overflow: hidden;
    min-height: 380px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.showcase-visual-card::before {
    content: '';
    position: absolute;
    top: -50px; right: -50px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(var(--primary-rgb), 0.2), transparent 70%);
}

.showcase-visual-card .visual-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    filter: drop-shadow(0 4px 16px rgba(0,0,0,0.2));
}

.showcase-visual-card h4 {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text-white);
    margin-bottom: 8px;
}

.showcase-visual-card p {
    color: var(--text-white-muted);
    font-size: 0.9rem;
    max-width: 280px;
}

.showcase-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    background: var(--primary-50);
    color: var(--primary-dark);
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: var(--radius-full);
    margin-bottom: 16px;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.showcase-title {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 16px;
    line-height: 1.25;
    letter-spacing: -0.01em;
}

.showcase-desc {
    font-size: 1rem;
    color: var(--text-secondary);
    line-height: 1.8;
    margin-bottom: 28px;
}

.showcase-features-list {
    list-style: none;
    margin-bottom: 32px;
}

.showcase-features-list li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 8px 0;
    font-size: 0.92rem;
    color: var(--text-secondary);
}

.showcase-features-list li i {
    color: var(--success);
    font-size: 1.1rem;
    margin-top: 2px;
    flex-shrink: 0;
}

/* ============================================
   STATS COUNTER (Crafto animated style)
   ============================================ */
.stats-section {
    padding: 80px 0;
    background: linear-gradient(160deg, #0F1724 0%, #1A2744 50%, #1E3A5F 100%);
    position: relative;
    overflow: hidden;
}

.stats-section::before {
    content: '';
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 800px; height: 800px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(var(--primary-rgb), 0.05) 0%, transparent 70%);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 32px;
    text-align: center;
    position: relative;
}

.stat-item {
    padding: 32px 16px;
    position: relative;
}

.stat-item::after {
    content: '';
    position: absolute;
    top: 20%; right: 0;
    height: 60%;
    width: 1px;
    background: rgba(255,255,255,0.1);
}

.stat-item:last-child::after { display: none; }

.stat-icon {
    width: 56px; height: 56px;
    border-radius: var(--radius-lg);
    background: rgba(var(--primary-rgb), 0.15);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: var(--primary-light);
    margin-bottom: 16px;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-white);
    line-height: 1;
    margin-bottom: 6px;
}

.stat-label {
    font-size: 0.88rem;
    color: var(--text-white-muted);
}

/* ============================================
   BILLING CHECK (Crafto-style card)
   ============================================ */
.billing-section {
    padding: 100px 0;
    background: var(--bg-body);
}

.billing-card {
    background: var(--bg-card);
    border-radius: var(--radius-2xl);
    border: 1px solid var(--border);
    padding: 56px 48px;
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 48px;
    align-items: center;
    box-shadow: var(--shadow-card);
    position: relative;
    overflow: hidden;
}

.billing-card::before {
    content: '';
    position: absolute;
    top: -80px; right: -80px;
    width: 250px; height: 250px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(var(--primary-rgb), 0.06) 0%, transparent 70%);
}

.billing-card h2 {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 12px;
    letter-spacing: -0.01em;
}

.billing-card > div:first-child p {
    color: var(--text-secondary);
    font-size: 0.95rem;
    line-height: 1.7;
}

.billing-trust {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    padding: 12px 16px;
    background: var(--success-light);
    border-radius: var(--radius-md);
}

.billing-trust i {
    color: var(--success);
    font-size: 1.2rem;
}

.billing-trust span {
    font-size: 0.85rem;
    color: var(--success);
    font-weight: 600;
}

.billing-form-wrap {
    background: var(--bg-surface);
    border-radius: var(--radius-xl);
    padding: 32px;
    border: 1px solid var(--border-light);
}

.billing-form-wrap label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 10px;
}

.billing-form-inline {
    display: flex;
    gap: 10px;
}

.billing-form-inline input {
    flex: 1;
    padding: 14px 18px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-full);
    font-size: 0.92rem;
    font-family: inherit;
    outline: none;
    transition: all var(--transition);
    background: var(--bg-card);
}

.billing-form-inline input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.12);
}

.billing-form-inline button {
    padding: 14px 28px;
    background: var(--primary);
    color: var(--text-white);
    border: none;
    border-radius: var(--radius-full);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all var(--transition);
    font-family: inherit;
    white-space: nowrap;
}

.billing-form-inline button:hover {
    background: var(--primary-dark);
    box-shadow: var(--shadow-primary);
}

/* ============================================
   NEWS (Crafto modern cards)
   ============================================ */
.news-section {
    padding: 100px 0;
    background: var(--bg-surface);
}

.news-card {
    background: var(--bg-card);
    border-radius: var(--radius-xl);
    border: 1px solid var(--border);
    overflow: hidden;
    transition: all var(--transition);
}

.news-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-xl);
    border-color: transparent;
}

.news-card-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    background: linear-gradient(135deg, var(--primary-50) 0%, var(--primary-lighter) 100%);
}

.news-card-body {
    padding: 24px;
}

.news-card-date {
    font-size: 0.78rem;
    color: var(--text-tertiary);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.news-card h3 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 10px;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.news-card p {
    font-size: 0.88rem;
    color: var(--text-secondary);
    line-height: 1.7;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ============================================
   DESTINATIONS (Crafto card style)
   ============================================ */
.dest-section {
    padding: 100px 0;
    background: var(--bg-body);
}

.dest-card {
    background: var(--bg-card);
    border-radius: var(--radius-xl);
    border: 1px solid var(--border);
    overflow: hidden;
    transition: all var(--transition);
    position: relative;
}

.dest-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-xl);
    border-color: transparent;
}

.dest-card-img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    background: linear-gradient(135deg, var(--primary-50) 0%, var(--primary-lighter) 100%);
}

.dest-card-body {
    padding: 20px 22px;
}

.dest-card h3 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 6px;
}

.dest-card p {
    font-size: 0.85rem;
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 10px;
}

.dest-card .dest-badge {
    position: absolute;
    top: 14px; right: 14px;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(10px);
    color: var(--text-white);
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: var(--radius-full);
}

.dest-card .dest-cat-badge {
    position: absolute;
    top: 14px; left: 14px;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: var(--radius-full);
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.dest-cat-badge.cat-wisata { background: #16a34a; }
.dest-cat-badge.cat-kuliner { background: #ea580c; }
.dest-cat-badge.cat-hotel { background: #2563eb; }
.dest-cat-badge.cat-oleh-oleh { background: #7c3aed; }
.dest-cat-badge.cat-hiburan { background: #dc2626; }

.dest-card-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
}
.dest-card-rating {
    color: #f59e0b;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 3px;
}
.dest-card-rating i { font-size: 0.75rem; }
.dest-card-reviews {
    color: var(--text-tertiary);
}

.dest-card a {
    text-decoration: none;
    color: inherit;
    display: block;
}

.dest-view-all {
    text-align: center;
    margin-top: 32px;
}
.dest-view-all a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: var(--primary);
    color: #fff;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: var(--radius-lg);
    text-decoration: none;
    transition: all var(--transition);
}
.dest-view-all a:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* ============================================
   CTA DOWNLOAD (Crafto gradient style)
   ============================================ */
.cta-section {
    padding: 100px 0;
    background: var(--bg-surface);
}

.cta-card {
    background: linear-gradient(135deg, #1A2744 0%, #0F1724 50%, #1E3A5F 100%);
    border-radius: var(--radius-2xl);
    padding: 72px 56px;
    position: relative;
    overflow: hidden;
    text-align: center;
}

.cta-card::before {
    content: '';
    position: absolute;
    top: -100px; right: -100px;
    width: 350px; height: 350px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(var(--primary-rgb), 0.15), transparent 70%);
}

.cta-card::after {
    content: '';
    position: absolute;
    bottom: -80px; left: -80px;
    width: 280px; height: 280px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(249,168,38,0.08), transparent 70%);
}

.cta-card > * { position: relative; z-index: 2; }

.cta-card .cta-icon {
    width: 80px; height: 80px;
    border-radius: var(--radius-xl);
    background: rgba(var(--primary-rgb), 0.15);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 28px;
    color: var(--primary-light);
}

.cta-card h2 {
    font-size: 2.2rem;
    font-weight: 800;
    color: var(--text-white);
    margin-bottom: 14px;
    letter-spacing: -0.02em;
}

.cta-card p {
    color: var(--text-white-muted);
    font-size: 1.05rem;
    margin-bottom: 36px;
    max-width: 520px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.7;
}

.cta-buttons {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
}

.cta-store-btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 14px 28px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: var(--radius-lg);
    color: var(--text-white);
    transition: all var(--transition);
    text-align: left;
}

.cta-store-btn:hover {
    background: rgba(255,255,255,0.18);
    transform: translateY(-3px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.2);
}

.cta-store-btn i {
    font-size: 1.8rem;
}

.cta-store-btn .store-text {
    display: flex;
    flex-direction: column;
}

.cta-store-btn .store-text small {
    font-size: 0.72rem;
    color: var(--text-white-muted);
    font-weight: 400;
}

.cta-store-btn .store-text strong {
    font-size: 0.95rem;
    font-weight: 700;
}

/* ============================================
   RESPONSIVE OVERRIDES
   ============================================ */
@media (max-width: 1024px) {
    .hero-inner { gap: 40px; }
    .hero h1 { font-size: 2.6rem; }
    .showcase-grid { gap: 40px; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .stat-item:nth-child(2)::after { display: none; }
    .billing-card { grid-template-columns: 1fr; }
    .steps-grid { grid-template-columns: repeat(3, 1fr); gap: 20px; }
    .step-connector { display: none; }
}

@media (max-width: 768px) {
    .hero { padding: 140px 0 60px; min-height: auto; }
    .hero-inner { grid-template-columns: 1fr; gap: 40px; text-align: center; }
    .hero h1 { font-size: 2.1rem; }
    .hero-desc { max-width: 100%; margin-left: auto; margin-right: auto; }
    .hero-buttons { justify-content: center; }
    .hero-mini-stats { justify-content: center; gap: 24px; flex-wrap: wrap; }
    .hero-right { height: 360px; }
    .hero-card-1 { top: 0; left: 0; width: 48%; }
    .hero-card-2 { top: 10px; right: 0; width: 48%; }
    .hero-card-3 { bottom: 40px; left: 0; width: 48%; }
    .hero-card-4 { bottom: 0; right: 0; width: 48%; }
    .steps-grid { grid-template-columns: 1fr; }
    .step-connector { display: none; }
    .showcase-grid, .showcase-grid.reverse { grid-template-columns: 1fr; direction: ltr; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .billing-card { padding: 32px 24px; }
    .billing-form-inline { flex-direction: column; }
    .cta-card { padding: 48px 24px; }
    .cta-card h2 { font-size: 1.6rem; }
    .marquee-banner { padding: 12px 0; }
}

@media (max-width: 480px) {
    .hero h1 { font-size: 1.8rem; }
    .hero-right { height: 300px; }
    .hero-float-card { padding: 18px 16px; }
    .hero-float-card h3 { font-size: 0.85rem; }
}

/* ============================================
   STAGGER REVEAL DELAYS (CSS-powered)
   ============================================ */
.grid-4 > .reveal:nth-child(1) { transition-delay: 0s; }
.grid-4 > .reveal:nth-child(2) { transition-delay: 0.1s; }
.grid-4 > .reveal:nth-child(3) { transition-delay: 0.2s; }
.grid-4 > .reveal:nth-child(4) { transition-delay: 0.3s; }

.grid-3 > .reveal:nth-child(1) { transition-delay: 0s; }
.grid-3 > .reveal:nth-child(2) { transition-delay: 0.12s; }
.grid-3 > .reveal:nth-child(3) { transition-delay: 0.24s; }

.steps-grid > .step-card:nth-child(3) { transition-delay: 0s; }
.steps-grid > .step-card:nth-child(4) { transition-delay: 0.15s; }
.steps-grid > .step-card:nth-child(5) { transition-delay: 0.3s; }

.stats-grid > .reveal:nth-child(1) { transition-delay: 0s; }
.stats-grid > .reveal:nth-child(2) { transition-delay: 0.1s; }
.stats-grid > .reveal:nth-child(3) { transition-delay: 0.2s; }
.stats-grid > .reveal:nth-child(4) { transition-delay: 0.3s; }
</style>
@endsection

@section('content')
    {{-- ======== HERO (Crafto-style) ======== --}}
    <section class="hero">
        <div class="hero-bg-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
        <div class="hero-pattern"></div>

        <div class="container hero-content">
            <div class="hero-inner">
                <div class="hero-left">
                    <div class="hero-trust-badge">
                        <span>Portal Pajak Online Resmi Kab. Bojonegoro</span>
                        <span class="badge-tag">Resmi</span>
                    </div>

                    <h1>Pajak Online<br><span class="highlight">Kabupaten Bojonegoro</span></h1>

                    <p class="hero-desc">
                        Layanan pajak daerah digital yang memudahkan masyarakat dalam pelaporan
                        dan pembayaran pajak secara transparan dan real-time.
                    </p>

                    <div class="hero-buttons">
                        <a href="{{ url('/login') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Masuk Portal
                        </a>
                        <a href="#layanan" class="btn btn-outline-white btn-lg">
                            Jelajahi Layanan
                        </a>
                    </div>

                    <div class="hero-mini-stats">
                        <div class="hero-mini-stat">
                            <div class="hero-mini-stat-icon"><i class="bi bi-people-fill"></i></div>
                            <div class="hero-mini-stat-text">
                                <div class="num" id="stat-wp">{{ $totalWp ?? 0 }}+</div>
                                <div class="label">Wajib Pajak</div>
                            </div>
                        </div>
                        <div class="hero-mini-stat">
                            <div class="hero-mini-stat-icon"><i class="bi bi-shield-check"></i></div>
                            <div class="hero-mini-stat-text">
                                <div class="num">24/7</div>
                                <div class="label">Akses Online</div>
                            </div>
                        </div>
                        <div class="hero-mini-stat">
                            <div class="hero-mini-stat-icon"><i class="bi bi-star-fill"></i></div>
                            <div class="hero-mini-stat-text">
                                <div class="num">4.8</div>
                                <div class="label">Rating App</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hero-right">
                    <div class="hero-float-card hero-card-1">
                        <div class="card-icon"><i class="bi bi-receipt-cutoff"></i></div>
                        <h3>Self Assessment</h3>
                        <p>Lapor pajak hotel, restoran & hiburan</p>
                    </div>
                    <div class="hero-float-card hero-card-2">
                        <div class="card-icon"><i class="bi bi-droplet-fill"></i></div>
                        <h3>Pajak Air Tanah</h3>
                        <p>Kelola objek pajak & meter bulanan</p>
                    </div>
                    <div class="hero-float-card hero-card-3">
                        <div class="card-icon"><i class="bi bi-megaphone-fill"></i></div>
                        <h3>Pajak Reklame</h3>
                        <p>Ajukan perpanjangan izin reklame</p>
                    </div>
                    <div class="hero-float-card hero-card-4">
                        <div class="card-icon"><i class="bi bi-gift-fill"></i></div>
                        <h3>Gebyar Pajak</h3>
                        <p>Undian sadar pajak</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ======== MARQUEE BANNER ======== --}}
    <div class="marquee-banner">
        <div class="marquee-track">
            <span class="marquee-item"><i class="bi bi-shield-fill-check"></i> Aman & Terpercaya</span>
            <span class="marquee-dot"></span>
            <span class="marquee-item"><i class="bi bi-lightning-fill"></i> Proses Cepat</span>
            <span class="marquee-dot"></span>
            <span class="marquee-item"><i class="bi bi-phone-fill"></i> Akses Mobile</span>
            <span class="marquee-dot"></span>
            <span class="marquee-item"><i class="bi bi-clock-fill"></i> 24 Jam Non-Stop</span>
            <span class="marquee-dot"></span>
            <span class="marquee-item"><i class="bi bi-graph-up-arrow"></i> Real-time Monitoring</span>
            <span class="marquee-dot"></span>
            <span class="marquee-item"><i class="bi bi-patch-check-fill"></i> Resmi Pemkab Bojonegoro</span>
            <span class="marquee-dot"></span>
            {{-- Duplicate for seamless loop --}}
            <span class="marquee-item"><i class="bi bi-shield-fill-check"></i> Aman & Terpercaya</span>
            <span class="marquee-dot"></span>
            <span class="marquee-item"><i class="bi bi-lightning-fill"></i> Proses Cepat</span>
            <span class="marquee-dot"></span>
            <span class="marquee-item"><i class="bi bi-phone-fill"></i> Akses Mobile</span>
            <span class="marquee-dot"></span>
            <span class="marquee-item"><i class="bi bi-clock-fill"></i> 24 Jam Non-Stop</span>
            <span class="marquee-dot"></span>
            <span class="marquee-item"><i class="bi bi-graph-up-arrow"></i> Real-time Monitoring</span>
            <span class="marquee-dot"></span>
            <span class="marquee-item"><i class="bi bi-patch-check-fill"></i> Resmi Pemkab Bojonegoro</span>
            <span class="marquee-dot"></span>
        </div>
    </div>

    {{-- ======== FEATURES / LAYANAN ======== --}}
    <section class="features-section" id="layanan">
        <div class="container">
            <div class="section-header reveal">
                <div class="section-badge"><i class="bi bi-grid-fill"></i> Layanan Kami</div>
                <h2 class="section-title">Layanan Pajak Daerah Digital</h2>
                <p class="section-subtitle">Akses berbagai layanan pajak daerah secara digital, cepat, transparan, dan akuntabel.</p>
            </div>
            <div class="grid-4">
                <div class="feature-card reveal">
                    <div class="feature-icon-wrap teal"><i class="bi bi-receipt-cutoff"></i></div>
                    <h3>Self Assessment</h3>
                    <p>Lapor dan bayar pajak hotel, restoran, hiburan, dan parkir secara mandiri.</p>
                    <a href="{{ url('/login') }}" class="feature-link">Selengkapnya <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon-wrap blue"><i class="bi bi-droplet-fill"></i></div>
                    <h3>Pajak Air Tanah</h3>
                    <p>Kelola objek pajak air tanah dan laporkan pemakaian meter bulanan.</p>
                    <a href="{{ url('/login') }}" class="feature-link">Selengkapnya <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon-wrap orange"><i class="bi bi-megaphone-fill"></i></div>
                    <h3>Pajak Reklame</h3>
                    <p>Ajukan perpanjangan izin reklame dan pantau status permohonan Anda.</p>
                    <a href="{{ url('/login') }}" class="feature-link">Selengkapnya <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon-wrap green"><i class="bi bi-gift-fill"></i></div>
                    <h3>Gebyar Pajak</h3>
                    <p>Upload nota pajak dan dapatkan kupon undian Gebyar Sadar Pajak.</p>
                    <a href="{{ url('/login') }}" class="feature-link">Selengkapnya <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    {{-- ======== HOW IT WORKS ======== --}}
    <section class="how-it-works" id="cara-kerja">
        <div class="container">
            <div class="section-header reveal">
                <div class="section-badge"><i class="bi bi-signpost-split-fill"></i> Cara Kerja</div>
                <h2 class="section-title">Mudah, Cepat & Transparan</h2>
                <p class="section-subtitle">Tiga langkah sederhana untuk mengakses layanan pajak daerah Bojonegoro.</p>
            </div>

            <div class="steps-grid" style="position:relative;">
                <div class="step-connector"></div>
                <div class="step-connector step-connector-2"></div>

                <div class="step-card reveal">
                    <div class="step-number">01</div>
                    <h3>Daftar & Login</h3>
                    <p>Buat akun wajib pajak melalui aplikasi mobile atau portal web, lalu verifikasi data Anda.</p>
                </div>

                <div class="step-card reveal">
                    <div class="step-number">02</div>
                    <h3>Lapor & Bayar</h3>
                    <p>Laporkan objek pajak, isi detail pemakaian, dan dapatkan kode billing untuk pembayaran.</p>
                </div>

                <div class="step-card reveal">
                    <div class="step-number">03</div>
                    <h3>Pantau & Arsipkan</h3>
                    <p>Pantau status pembayaran secara real-time dan simpan bukti pembayaran digital.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ======== SHOWCASE: Billing Management ======== --}}
    <section class="showcase-section">
        <div class="container">
            <div class="showcase-grid reveal">
                <div class="showcase-visual">
                    <div class="showcase-visual-card">
                        <div class="visual-icon"><i class="bi bi-speedometer2" style="color: var(--primary-light);"></i></div>
                        <h4>Dashboard Monitoring</h4>
                        <p>Pantau seluruh data pajak Anda dalam satu tampilan terintegrasi</p>
                    </div>
                </div>
                <div>
                    <div class="showcase-badge"><i class="bi bi-bar-chart-fill"></i> Dashboard Data</div>
                    <h2 class="showcase-title">Kelola Data Pajak dengan Mudah</h2>
                    <p class="showcase-desc">Dashboard terintegrasi memudahkan Anda memantau seluruh objek pajak, status pembayaran, dan riwayat transaksi dalam satu platform.</p>
                    <ul class="showcase-features-list">
                        <li><i class="bi bi-check-circle-fill"></i> Lapor pajak hotel, restoran, hiburan & parkir mandiri</li>
                        <li><i class="bi bi-check-circle-fill"></i> Monitoring pemakaian meter air tanah real-time</li>
                        <li><i class="bi bi-check-circle-fill"></i> Cetak SKPD dan bukti pembayaran digital</li>
                        <li><i class="bi bi-check-circle-fill"></i> Notifikasi jatuh tempo otomatis</li>
                    </ul>
                    <a href="{{ url('/login') }}" class="btn btn-primary">Masuk Dashboard <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    {{-- ======== SHOWCASE 2: Billing Check (reversed) ======== --}}
    <section class="showcase-section" style="background: var(--bg-surface);">
        <div class="container">
            <div class="showcase-grid reverse reveal">
                <div class="showcase-visual">
                    <div class="showcase-visual-card" style="background: linear-gradient(135deg, #0C4A6E 0%, #164E63 100%);">
                        <div class="visual-icon"><i class="bi bi-qr-code-scan" style="color: #67E8F9;"></i></div>
                        <h4>Cek Billing Instan</h4>
                        <p>Lihat detail tagihan & status pembayaran kapan saja</p>
                    </div>
                </div>
                <div>
                    <div class="showcase-badge"><i class="bi bi-search"></i> Cek Status</div>
                    <h2 class="showcase-title">Cek Billing Pembayaran Pajak</h2>
                    <p class="showcase-desc">Masukkan kode billing untuk melihat detail tagihan pajak dan status pembayaran Anda secara real-time.</p>
                    <ul class="showcase-features-list">
                        <li><i class="bi bi-check-circle-fill"></i> Cek status billing tanpa perlu login</li>
                        <li><i class="bi bi-check-circle-fill"></i> Detail tagihan lengkap & transparan</li>
                        <li><i class="bi bi-check-circle-fill"></i> Informasi jatuh tempo & denda</li>
                        <li><i class="bi bi-check-circle-fill"></i> Download bukti pembayaran PDF</li>
                    </ul>
                    <a href="{{ url('/cek-billing') }}" class="btn btn-primary">Cek Billing Sekarang <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    {{-- ======== LAYANAN PUBLIK ======== --}}
    <section class="features-section" id="layanan-publik" style="background: var(--bg-surface);">
        <div class="container">
            <div class="section-header reveal">
                <div class="section-badge"><i class="bi bi-globe2"></i> Akses Publik</div>
                <h2 class="section-title">Layanan Publik</h2>
                <p class="section-subtitle">Akses informasi dan kalkulator pajak tanpa perlu login. Tersedia untuk semua masyarakat.</p>
            </div>
            <div class="grid-3" style="max-width: 960px; margin: 0 auto;">
                <div class="feature-card reveal">
                    <div class="feature-icon-wrap teal"><i class="bi bi-receipt-cutoff"></i></div>
                    <h3>Cek Billing</h3>
                    <p>Periksa status tagihan dan pembayaran pajak daerah cukup dengan kode billing.</p>
                    <a href="{{ url('/cek-billing') }}" class="feature-link">Cek Billing <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon-wrap blue"><i class="bi bi-clock-history"></i></div>
                    <h3>Histori Pajak</h3>
                    <p>Lihat riwayat dokumen pajak per wajib pajak untuk satu tahun pajak tanpa login.</p>
                    <a href="{{ url('/histori-pajak') }}" class="feature-link">Lihat Histori <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon-wrap orange"><i class="bi bi-megaphone-fill"></i></div>
                    <h3>Sewa Reklame</h3>
                    <p>Lihat daftar titik reklame milik pemerintah yang tersedia untuk disewa.</p>
                    <a href="{{ url('/sewa-reklame') }}" class="feature-link">Lihat Aset <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon-wrap teal"><i class="bi bi-calculator-fill"></i></div>
                    <h3>Kalkulator Sanksi</h3>
                    <p>Hitung estimasi denda keterlambatan pembayaran pajak daerah.</p>
                    <a href="{{ url('/kalkulator-sanksi') }}" class="feature-link">Hitung Sekarang <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon-wrap blue"><i class="bi bi-journal-bookmark-fill"></i></div>
                    <h3>Produk Hukum</h3>
                    <p>Akses peraturan dan dasar hukum pajak daerah yang berlaku.</p>
                    <a href="{{ url('/produk-hukum') }}" class="feature-link">Lihat Dokumen <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon-wrap green"><i class="bi bi-droplet-fill"></i></div>
                    <h3>Kalkulator Air Tanah</h3>
                    <p>Simulasi perhitungan pajak air tanah berdasarkan volume pemakaian.</p>
                    <a href="{{ url('/kalkulator-air-tanah') }}" class="feature-link">Hitung Sekarang <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon-wrap purple" style="background: rgba(139,92,246,0.1);"><i class="bi bi-easel-fill" style="color: #8B5CF6;"></i></div>
                    <h3>Kalkulator Reklame</h3>
                    <p>Simulasi perhitungan pajak reklame sesuai jenis, lokasi, dan ukuran.</p>
                    <a href="{{ url('/kalkulator-reklame') }}" class="feature-link">Hitung Sekarang <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    {{-- ======== STATS COUNTER ======== --}}
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item reveal">
                    <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                    <div class="stat-number" data-target="{{ $totalWp ?? 150 }}">0</div>
                    <div class="stat-label">Wajib Pajak Terdaftar</div>
                </div>
                <div class="stat-item reveal">
                    <div class="stat-icon"><i class="bi bi-building"></i></div>
                    <div class="stat-number">6</div>
                    <div class="stat-label">Jenis Pajak Daerah</div>
                </div>
                <div class="stat-item reveal">
                    <div class="stat-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <div class="stat-number" data-target="{{ $totalDestinations ?? 12 }}">0</div>
                    <div class="stat-label">Destinasi Wisata</div>
                </div>
                <div class="stat-item reveal">
                    <div class="stat-icon"><i class="bi bi-pin-map-fill"></i></div>
                    <div class="stat-number">28</div>
                    <div class="stat-label">Kecamatan</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ======== BILLING CHECK SECTION ======== --}}
    <section class="billing-section" id="cek-billing">
        <div class="container">
            <div class="billing-card reveal">
                <div>
                    <h2><i class="bi bi-search" style="color: var(--primary); margin-right: 8px;"></i>Cek Status Billing</h2>
                    <p>Masukkan kode billing untuk melihat detail tagihan pajak dan status pembayaran Anda secara real-time. Tidak perlu login untuk mengecek billing.</p>
                    <div class="billing-trust">
                        <i class="bi bi-shield-fill-check"></i>
                        <span>Data aman & terenkripsi</span>
                    </div>
                </div>
                <div class="billing-form-wrap">
                    <label><i class="bi bi-upc-scan"></i> Kode Billing</label>
                    <form action="{{ url('/cek-billing') }}" method="GET">
                        <div class="billing-form-inline">
                            <input type="text" name="code" placeholder="Masukkan kode billing Anda" maxlength="20" required>
                            <button type="submit"><i class="bi bi-search"></i> Cek</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- ======== NEWS ======== --}}
    @if(isset($news) && $news->count() > 0)
        <section class="news-section" id="news">
            <div class="container">
                <div class="section-header reveal">
                    <div class="section-badge"><i class="bi bi-newspaper"></i> Informasi</div>
                    <h2 class="section-title">Berita Terbaru</h2>
                    <p class="section-subtitle">Informasi dan berita terkini seputar perpajakan daerah Kabupaten Bojonegoro.</p>
                </div>
                <div class="grid-3">
                    @foreach($news as $item)
                        <a href="{{ route('publik.berita.show', $item) }}" class="news-card reveal" style="text-decoration:none;color:inherit;">
                            @if($item->image_url)
                                <img src="{{ asset($item->image_url) }}" alt="{{ $item->title }}" class="news-card-img">
                            @else
                                <div class="news-card-img"
                                    style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:var(--primary);">
                                    <i class="bi bi-newspaper"></i>
                                </div>
                            @endif
                            <div class="news-card-body">
                                <div class="news-card-date">
                                    <i class="bi bi-calendar3"></i>
                                    {{ \Carbon\Carbon::parse($item->published_at ?? $item->created_at)->format('d M Y') }}
                                </div>
                                <h3>{{ $item->title }}</h3>
                                <p>{{ str()->limit(strip_tags($item->content), 100) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ======== DESTINATIONS ======== --}}
    @if(isset($destinations) && $destinations->count() > 0)
        <section class="dest-section" id="destinations">
            <div class="container">
                <div class="section-header reveal">
                    <div class="section-badge"><i class="bi bi-compass-fill"></i> Jelajahi</div>
                    <h2 class="section-title">Destinasi Wisata Bojonegoro</h2>
                    <p class="section-subtitle">Temukan destinasi wisata menarik di Kabupaten Bojonegoro.</p>
                </div>
                <div class="grid-3">
                    @foreach($destinations as $dest)
                        <div class="dest-card reveal">
                            <a href="{{ route('publik.destinasi.show', $dest) }}">
                                @if($dest->image_url)
                                    <img src="{{ asset($dest->image_url) }}" alt="{{ $dest->name }}" class="dest-card-img">
                                @else
                                    <div class="dest-card-img"
                                        style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:var(--primary);">
                                        <i class="bi bi-image-fill"></i>
                                    </div>
                                @endif
                                <span class="dest-cat-badge cat-{{ $dest->category }}">{{ $dest->category_label }}</span>
                                <span class="dest-badge"><i class="bi bi-geo-alt-fill"></i> Bojonegoro</span>
                                <div class="dest-card-body">
                                    <h3>{{ $dest->name }}</h3>
                                    <p>{{ str()->limit($dest->description, 80) }}</p>
                                    <div class="dest-card-meta">
                                        <span class="dest-card-rating"><i class="bi bi-star-fill"></i> {{ number_format($dest->rating, 1) }}</span>
                                        <span class="dest-card-reviews">({{ $dest->review_count }} ulasan)</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="dest-view-all reveal">
                    <a href="{{ url('/destinasi') }}"><i class="bi bi-compass"></i> Lihat Semua Destinasi</a>
                </div>
            </div>
        </section>
    @endif

    {{-- ======== CTA DOWNLOAD ======== --}}
    <section class="cta-section">
        <div class="container">
            <div class="cta-card reveal">
                <div class="cta-icon"><i class="bi bi-phone-fill"></i></div>
                <h2>Download Aplikasi Borotax</h2>
                <p>Akses layanan pajak Bojonegoro langsung dari smartphone Anda. Daftar, bayar, dan pantau pajak kapan saja, di mana saja.</p>
                <div class="cta-buttons">
                    <a href="#" class="cta-store-btn">
                        <i class="bi bi-google-play"></i>
                        <span class="store-text">
                            <small>Download di</small>
                            <strong>Google Play</strong>
                        </span>
                    </a>
                    <a href="#" class="cta-store-btn">
                        <i class="bi bi-apple"></i>
                        <span class="store-text">
                            <small>Download di</small>
                            <strong>App Store</strong>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
<script>
    // Animated counter
    function animateCounter(el, target, suffix = '') {
        if (!el) return;
        let current = 0;
        const step = Math.max(1, Math.ceil(target / 60));
        const timer = setInterval(() => {
            current += step;
            if (current >= target) { current = target; clearInterval(timer); }
            el.textContent = current.toLocaleString('id-ID') + suffix;
        }, 25);
    }

    // Stats counter animation
    let statsAnimated = false;
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !statsAnimated) {
                statsAnimated = true;
                document.querySelectorAll('.stat-number[data-target]').forEach(el => {
                    const target = parseInt(el.dataset.target);
                    animateCounter(el, target, '+');
                });
            }
        });
    }, { threshold: 0.3 });

    const statsEl = document.querySelector('.stats-section');
    if (statsEl) statsObserver.observe(statsEl);

    // Stagger reveal is handled by CSS transition-delay
    // (see .grid-4 > .reveal:nth-child, .grid-3 > .reveal:nth-child, etc.)
</script>
@endsection
