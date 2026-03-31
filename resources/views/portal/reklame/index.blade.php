@extends('layouts.portal-dashboard')

@section('title', 'Reklame - Borotax Portal')
@section('page-title', 'Layanan Pajak Reklame')

@section('styles')
<style>
    /* Header banner */
    .rkl-header {
        background: linear-gradient(140deg, #FF7043 0%, #E64A19 100%);
        border-radius: var(--radius-xl);
        padding: 28px 32px;
        color: #fff;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .rkl-header::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -3%;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(255,255,255,0.12), transparent 70%);
    }

    .rkl-header-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-lg);
        background: rgba(255,255,255,0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
        position: relative;
    }

    .rkl-header-content { position: relative; }

    .rkl-header h2 {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .rkl-header p {
        color: rgba(255,255,255,0.7);
        font-size: 0.85rem;
    }

    /* Info cards row */
    .info-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 32px;
    }

    .info-card {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .info-card .ic-icon {
        width: 46px;
        height: 46px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .info-card .ic-icon.primary  { background: #FBE9E7; color: #BF360C; }
    .info-card .ic-icon.success  { background: #E8F5E9; color: #2E7D32; }
    .info-card .ic-icon.warning  { background: #FFF8E1; color: #F57F17; }
    .info-card .ic-icon.info     { background: #E3F2FD; color: #1565C0; }

    .info-card .ic-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
    }

    .info-card .ic-label {
        font-size: 0.78rem;
        color: var(--text-tertiary);
        margin-top: 2px;
    }

    /* Section title */
    .section-label {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .section-sublabel {
        font-size: 0.82rem;
        color: var(--text-tertiary);
        margin-bottom: 18px;
    }

    /* Menu cards */
    .menu-grid {
        display: grid;
        gap: 14px;
    }

    .menu-card {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        padding: 22px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        text-decoration: none;
        color: inherit;
        transition: all var(--transition);
        position: relative;
    }

    .menu-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .menu-card .mc-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .menu-card .mc-icon.reklame { background: #FBE9E7; color: #BF360C; }
    .menu-card .mc-icon.skpd    { background: #E8F5E9; color: #2E7D32; }

    .menu-card .mc-info { flex: 1; min-width: 0; }

    .menu-card .mc-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .menu-card .mc-desc {
        font-size: 0.78rem;
        color: var(--text-tertiary);
        line-height: 1.5;
    }

    .menu-card .mc-badge {
        background: var(--primary-50);
        color: var(--primary-dark);
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: var(--radius-full);
        white-space: nowrap;
    }

    .menu-card .mc-arrow {
        color: var(--border);
        font-size: 1rem;
        transition: all var(--transition);
        position: absolute;
        right: 24px;
        top: 22px;
    }

    .menu-card:hover .mc-arrow { color: var(--primary); transform: translateX(3px); }

    /* Info box */
    .info-box {
        margin-top: 32px;
        background: #FBE9E7;
        border: 1px solid #FFCCBC;
        border-radius: var(--radius-lg);
        padding: 20px;
    }

    .info-box .ib-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.88rem;
        font-weight: 700;
        color: #BF360C;
        margin-bottom: 10px;
    }

    .info-box .ib-steps {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-box .ib-steps li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 0.82rem;
        color: #334155;
        margin-bottom: 8px;
        line-height: 1.5;
    }

    .info-box .ib-steps li:last-child { margin-bottom: 0; }

    .info-box .step-num {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #BF360C;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
    }

    @media (max-width: 768px) {
        .info-cards { grid-template-columns: 1fr 1fr; }
        .rkl-header { padding: 22px 20px; }
    }

    @media (max-width: 480px) {
        .info-cards { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
    {{-- Header --}}
    <div class="rkl-header">
        <div class="rkl-header-icon"><i class="bi bi-signpost-2-fill"></i></div>
        <div class="rkl-header-content">
            <h2>Layanan Pajak Reklame</h2>
            <p>Kelola objek reklame, perpanjangan, dan dokumen SKPD Anda</p>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="info-cards">
        <div class="info-card">
            <div class="ic-icon primary"><i class="bi bi-signpost-2"></i></div>
            <div>
                <div class="ic-value">{{ $totalObjek }}</div>
                <div class="ic-label">Total Objek</div>
            </div>
        </div>
        <div class="info-card">
            <div class="ic-icon success"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="ic-value">{{ $objekAktif }}</div>
                <div class="ic-label">Aktif</div>
            </div>
        </div>
        <div class="info-card">
            <div class="ic-icon warning"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="ic-value">{{ $objekKadaluarsa }}</div>
                <div class="ic-label">Kadaluarsa</div>
            </div>
        </div>
        <div class="info-card">
            <div class="ic-icon info"><i class="bi bi-file-earmark-check"></i></div>
            <div>
                <div class="ic-value">{{ $skpdCount }}</div>
                <div class="ic-label">SKPD Terbit</div>
            </div>
        </div>
    </div>

    {{-- Menu Section --}}
    <div class="section-label">Menu Layanan</div>
    <div class="section-sublabel">Pilih layanan yang ingin Anda akses</div>

    <div class="menu-grid">
        <a href="{{ route('portal.reklame.objects') }}" class="menu-card">
            <i class="bi bi-arrow-right mc-arrow"></i>
            <div class="mc-icon reklame"><i class="bi bi-signpost-2-fill"></i></div>
            <div class="mc-info">
                <div class="mc-title">Objek Reklame Saya</div>
                <div class="mc-desc">Lihat daftar objek reklame dan ajukan perpanjangan</div>
            </div>
            <span class="mc-badge">{{ $totalObjek }} objek</span>
        </a>

        <a href="{{ route('portal.reklame.skpd-list') }}" class="menu-card">
            <i class="bi bi-arrow-right mc-arrow"></i>
            <div class="mc-icon skpd"><i class="bi bi-file-earmark-text-fill"></i></div>
            <div class="mc-info">
                <div class="mc-title">Dokumen SKPD</div>
                <div class="mc-desc">Lihat dokumen SKPD Pajak Reklame dan kode billing</div>
            </div>
            <span class="mc-badge">{{ $skpdCount }} SKPD</span>
        </a>
    </div>

    {{-- Info Box --}}
    <div class="info-box">
        <div class="ib-title">
            <i class="bi bi-info-circle"></i>
            Alur Layanan Pajak Reklame
        </div>
        <ul class="ib-steps">
            <li>
                <span class="step-num">1</span>
                Pendaftaran objek reklame baru dilakukan melalui aplikasi mobile <strong>Borotax</strong> atau kantor Bapenda
            </li>
            <li>
                <span class="step-num">2</span>
                Ajukan perpanjangan masa berlaku reklame melalui halaman detail objek di portal ini atau di aplikasi mobile
            </li>
            <li>
                <span class="step-num">3</span>
                Pengajuan akan diverifikasi petugas dan SKPD akan diterbitkan setelah disetujui
            </li>
            <li>
                <span class="step-num">4</span>
                Lakukan pembayaran sesuai kode billing yang tertera pada dokumen SKPD
            </li>
        </ul>
    </div>
@endsection
