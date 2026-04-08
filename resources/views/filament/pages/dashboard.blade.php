<x-filament-panels::page>
    <style>
        /* ===== DASHBOARD STYLES ===== */
        .dash-hero {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
            border-radius: 16px;
            padding: 28px 32px;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .dash-hero::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }
        .dash-hero::after {
            content: '';
            position: absolute;
            bottom: -40%;
            right: 15%;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .dash-hero h2 {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0 0 4px;
            position: relative;
            z-index: 1;
        }
        .dash-hero p {
            font-size: 0.9rem;
            opacity: 0.85;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        .dash-hero .hero-date {
            font-size: 0.78rem;
            opacity: 0.65;
            margin-top: 8px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        @media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } }

        .stat-card {
            background: white;
            border-radius: 14px;
            padding: 20px 22px;
            position: relative;
            overflow: hidden;
            transition: all 0.25s ease;
            border: 1px solid #e5e7eb;
        }
        .dark .stat-card {
            background: #1f2937;
            border-color: #374151;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        .dark .stat-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        }
        .stat-card .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }
        .stat-card .stat-icon svg {
            width: 22px;
            height: 22px;
        }
        .stat-card .stat-label {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .dark .stat-card .stat-label { color: #9ca3af; }
        .stat-card .stat-value {
            font-size: 1.6rem;
            font-weight: 800;
            color: #111827;
            line-height: 1.2;
        }
        .dark .stat-card .stat-value { color: #f3f4f6; }
        .stat-card .stat-trend {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 0.72rem;
            font-weight: 700;
            margin-top: 6px;
            padding: 2px 8px;
            border-radius: 20px;
        }
        .stat-trend.up { background: #dcfce7; color: #16a34a; }
        .stat-trend.down { background: #fee2e2; color: #dc2626; }
        .stat-trend.neutral { background: #f3f4f6; color: #6b7280; }
        .dark .stat-trend.up { background: rgba(22,163,106,0.15); }
        .dark .stat-trend.down { background: rgba(220,38,38,0.15); }
        .dark .stat-trend.neutral { background: rgba(107,114,128,0.15); }

        .stat-card .stat-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        @media (max-width: 1024px) { .quick-actions { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .quick-actions { grid-template-columns: 1fr; } }

        .qa-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid #e5e7eb;
            background: white;
        }
        .dark .qa-btn {
            background: #1f2937;
            border-color: #374151;
        }
        .qa-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .dark .qa-btn:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .qa-btn .qa-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .qa-btn .qa-icon svg { width: 20px; height: 20px; }
        .qa-btn .qa-text {
            font-size: 0.82rem;
            font-weight: 700;
            color: #374151;
        }
        .dark .qa-btn .qa-text { color: #e5e7eb; }
        .qa-btn .qa-desc {
            font-size: 0.7rem;
            color: #9ca3af;
            margin-top: 1px;
        }

        /* Verification badges */
        .verif-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        @media (max-width: 1024px) { .verif-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .verif-grid { grid-template-columns: 1fr; } }

        .verif-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-radius: 12px;
            background: white;
            border: 1px solid #e5e7eb;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .dark .verif-card {
            background: #1f2937;
            border-color: #374151;
        }
        .verif-card:hover {
            border-color: #3b82f6;
            transform: translateY(-1px);
        }
        .verif-card .verif-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .verif-card .verif-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .verif-card .verif-dot.pulse {
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .verif-card .verif-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
        }
        .dark .verif-card .verif-label { color: #e5e7eb; }
        .verif-card .verif-badge {
            min-width: 28px;
            height: 28px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 800;
            padding: 0 10px;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        @media (max-width: 1024px) { .content-grid { grid-template-columns: 1fr; } }

        .content-card {
            background: white;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }
        .dark .content-card {
            background: #1f2937;
            border-color: #374151;
        }
        .content-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .dark .content-card-header { border-bottom-color: #374151; }
        .content-card-header h3 {
            font-size: 0.92rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }
        .dark .content-card-header h3 { color: #f3f4f6; }
        .content-card-body { padding: 16px 20px; }

        /* Transaction list */
        .tx-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .dark .tx-item { border-bottom-color: #374151; }
        .tx-item:last-child { border-bottom: none; }
        .tx-item .tx-info { display: flex; align-items: center; gap: 10px; }
        .tx-item .tx-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .tx-item .tx-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
        }
        .dark .tx-item .tx-label { color: #e5e7eb; }
        .tx-item .tx-sub {
            font-size: 0.7rem;
            color: #9ca3af;
        }
        .tx-item .tx-amount {
            font-size: 0.82rem;
            font-weight: 700;
            color: #111827;
            text-align: right;
        }
        .dark .tx-item .tx-amount { color: #f3f4f6; }
        .tx-item .tx-date {
            font-size: 0.68rem;
            color: #9ca3af;
            text-align: right;
        }

        /* Chart container */
        .chart-container {
            position: relative;
            height: 260px;
            width: 100%;
        }

        /* Jenis pajak bars */
        .jp-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }
        .jp-item:last-child { margin-bottom: 0; }
        .jp-item .jp-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #374151;
            min-width: 120px;
            flex-shrink: 0;
        }
        .dark .jp-item .jp-label { color: #e5e7eb; }
        .jp-item .jp-bar-track {
            flex: 1;
            height: 10px;
            border-radius: 5px;
            background: #f3f4f6;
            overflow: hidden;
        }
        .dark .jp-item .jp-bar-track { background: #374151; }
        .jp-item .jp-bar-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 1s ease;
        }
        .jp-item .jp-value {
            font-size: 0.75rem;
            font-weight: 700;
            color: #6b7280;
            min-width: 90px;
            text-align: right;
        }
        .dark .jp-item .jp-value { color: #9ca3af; }

        /* Section title */
        .section-title {
            font-size: 0.88rem;
            font-weight: 800;
            color: #374151;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .dark .section-title { color: #e5e7eb; }
        .section-title .section-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #3b82f6;
        }

        /* Full width card */
        .full-card {
            grid-column: 1 / -1;
        }
    </style>

    {{-- HERO GREETING --}}
    <div class="dash-hero">
        <h2>{{ $greeting }}, {{ $userName }}!</h2>
        <p>Berikut ringkasan aktivitas sistem pajak daerah Kabupaten Bojonegoro hari ini.</p>
        <p class="hero-date">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- STAT CARDS --}}
    <div class="stats-grid">
        {{-- Pendapatan --}}
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(16,185,129,0.12);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="stat-label">Pendapatan Bulan Ini</div>
            <div class="stat-value">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</div>
            <span class="stat-trend {{ $pendapatanTrend >= 0 ? 'up' : 'down' }}">
                @if($pendapatanTrend >= 0) &#9650; @else &#9660; @endif
                {{ abs($pendapatanTrend) }}%
            </span>
            <div class="stat-bar" style="background: linear-gradient(90deg, #10b981, #34d399);"></div>
        </div>

        {{-- Total WP --}}
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(59,130,246,0.12);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
            </div>
            <div class="stat-label">Total Wajib Pajak</div>
            <div class="stat-value">{{ number_format($totalWP) }}</div>
            <span class="stat-trend {{ $wpTrend >= 0 ? 'up' : ($wpTrend < 0 ? 'down' : 'neutral') }}">
                @if($wpTrend >= 0) &#9650; +{{ $wpBulanIni }} @else &#9660; {{ $wpBulanIni }} @endif bulan ini
            </span>
            <div class="stat-bar" style="background: linear-gradient(90deg, #3b82f6, #60a5fa);"></div>
        </div>

        {{-- Billing Pending --}}
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(245,158,11,0.12);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#f59e0b"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="stat-label">Billing Pending</div>
            <div class="stat-value">{{ number_format($billingPending) }}</div>
            <span class="stat-trend neutral">Menunggu pembayaran</span>
            <div class="stat-bar" style="background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
        </div>

        {{-- Transaksi Bulan Ini --}}
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(99,102,241,0.12);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#6366f1"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
            </div>
            <div class="stat-label">Transaksi Bulan Ini</div>
            <div class="stat-value">{{ number_format($transaksiBulanIni) }}</div>
            <span class="stat-trend neutral">Lunas + terverifikasi</span>
            <div class="stat-bar" style="background: linear-gradient(90deg, #6366f1, #818cf8);"></div>
        </div>
    </div>

    {{-- SECTION: Perlu Verifikasi --}}
    @php
        $totalMenunggu = $wpMenunggu + $reklameMenunggu + $pembetulanMenunggu + $skpdReklameMenunggu + $skpdAirMenunggu;
    @endphp
    @if($totalMenunggu > 0)
        <div class="section-title">
            <span class="section-dot" style="background: #f59e0b;"></span>
            Perlu Tindakan ({{ $totalMenunggu }} menunggu)
        </div>
        <div class="verif-grid">
            @if($wpMenunggu > 0)
                <a href="{{ App\Filament\Resources\WajibPajakResource::getUrl('index') }}" class="verif-card">
                    <div class="verif-info">
                        <div class="verif-dot pulse" style="background: #f59e0b;"></div>
                        <span class="verif-label">Verifikasi WP</span>
                    </div>
                    <span class="verif-badge" style="background: #fef3c7; color: #b45309;">{{ $wpMenunggu }}</span>
                </a>
            @endif
            @if($reklameMenunggu > 0)
                <a href="{{ App\Filament\Resources\ReklameRequestResource::getUrl('index') }}" class="verif-card">
                    <div class="verif-info">
                        <div class="verif-dot pulse" style="background: #8b5cf6;"></div>
                        <span class="verif-label">Pengajuan Reklame Portal</span>
                    </div>
                    <span class="verif-badge" style="background: #ede9fe; color: #6d28d9;">{{ $reklameMenunggu }}</span>
                </a>
            @endif
            @if($pembetulanMenunggu > 0)
                <a href="{{ App\Filament\Resources\PembetulanRequestResource::getUrl('index') }}" class="verif-card">
                    <div class="verif-info">
                        <div class="verif-dot pulse" style="background: #ef4444;"></div>
                        <span class="verif-label">Permintaan Pembetulan</span>
                    </div>
                    <span class="verif-badge" style="background: #fee2e2; color: #dc2626;">{{ $pembetulanMenunggu }}</span>
                </a>
            @endif
            @if($skpdReklameMenunggu > 0)
                <a href="{{ App\Filament\Resources\SkpdReklameResource::getUrl('index') }}" class="verif-card">
                    <div class="verif-info">
                        <div class="verif-dot pulse" style="background: #f97316;"></div>
                        <span class="verif-label">SKPD Reklame</span>
                    </div>
                    <span class="verif-badge" style="background: #ffedd5; color: #c2410c;">{{ $skpdReklameMenunggu }}</span>
                </a>
            @endif
            @if($skpdAirMenunggu > 0)
                <a href="{{ App\Filament\Resources\SkpdAirTanahResource::getUrl('index') }}" class="verif-card">
                    <div class="verif-info">
                        <div class="verif-dot pulse" style="background: #14b8a6;"></div>
                        <span class="verif-label">SKPD Air Tanah</span>
                    </div>
                    <span class="verif-badge" style="background: #ccfbf1; color: #0d9488;">{{ $skpdAirMenunggu }}</span>
                </a>
            @endif
        </div>
    @endif

    {{-- QUICK ACTIONS --}}
    <div class="section-title">
        <span class="section-dot"></span>
        Aksi Cepat
    </div>
    <div class="quick-actions">
        {{-- Fitur Buat Billing Self Assessment dinonaktifkan sementara --}}
        <a href="{{ App\Filament\Resources\TaxResource::getUrl('index') }}" class="qa-btn">
            <div class="qa-icon" style="background: rgba(59,130,246,0.12);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
            </div>
            <div>
                <div class="qa-text">Laporan Pendapatan</div>
                <div class="qa-desc">Lihat semua transaksi</div>
            </div>
        </a>
        <a href="{{ App\Filament\Resources\WajibPajakResource::getUrl('index') }}" class="qa-btn">
            <div class="qa-icon" style="background: rgba(139,92,246,0.12);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#8b5cf6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" /></svg>
            </div>
            <div>
                <div class="qa-text">Wajib Pajak</div>
                <div class="qa-desc">Kelola data WP</div>
            </div>
        </a>
    </div>

    {{-- CONTENT: Charts + Recent Transactions --}}
    <div class="content-grid">
        {{-- Trend Chart --}}
        <div class="content-card">
            <div class="content-card-header">
                <h3>Trend Pendapatan (6 Bulan)</h3>
            </div>
            <div class="content-card-body">
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Per Jenis Pajak --}}
        <div class="content-card">
            <div class="content-card-header">
                <h3>Pendapatan per Jenis Pajak</h3>
                <span style="font-size: 0.7rem; color: #9ca3af;">Bulan ini</span>
            </div>
            <div class="content-card-body">
                @if($perJenis->count() > 0)
                    @php
                        $maxVal = $perJenis->max() ?: 1;
                        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'];
                    @endphp
                    @foreach($perJenis as $nama => $jumlah)
                        <div class="jp-item">
                            <span class="jp-label">{{ $nama }}</span>
                            <div class="jp-bar-track">
                                <div class="jp-bar-fill" style="width: {{ ($jumlah / $maxVal) * 100 }}%; background: {{ $colors[$loop->index % count($colors)] }};"></div>
                            </div>
                            <span class="jp-value">Rp {{ number_format($jumlah, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                @else
                    <div style="text-align: center; padding: 40px 0; color: #9ca3af;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 40px; height: 40px; margin: 0 auto 8px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        <p style="font-size: 0.82rem;">Belum ada data bulan ini</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="content-card full-card">
            <div class="content-card-header">
                <h3>Transaksi Terbaru</h3>
                <a href="{{ App\Filament\Resources\TaxResource::getUrl('index') }}" style="font-size: 0.75rem; color: #3b82f6; text-decoration: none; font-weight: 600;">Lihat Semua &rarr;</a>
            </div>
            <div class="content-card-body" style="padding: 10px 20px;">
                @forelse($transaksiTerbaru as $tx)
                    <div class="tx-item">
                        <div class="tx-info">
                            <div class="tx-status-dot" style="background: {{ match($tx->status) { App\Enums\TaxStatus::Paid => '#10b981', App\Enums\TaxStatus::Pending, App\Enums\TaxStatus::Verified => '#f59e0b', default => '#9ca3af' } }};"></div>
                            <div>
                                <div class="tx-label">{{ $tx->jenisPajak->nama ?? '-' }}</div>
                                <div class="tx-sub">{{ $tx->billing_code }} &bull; {{ $tx->user->nama_lengkap ?? $tx->user->name ?? '-' }}</div>
                            </div>
                        </div>
                        <div>
                            <div class="tx-amount">Rp {{ number_format((float) $tx->amount, 0, ',', '.') }}</div>
                            <div class="tx-date">{{ $tx->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 30px 0; color: #9ca3af; font-size: 0.82rem;">
                        Belum ada transaksi
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
            const textColor = isDark ? '#9ca3af' : '#6b7280';

            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Pendapatan',
                        data: @json($chartValues),
                        borderColor: '#3b82f6',
                        backgroundColor: (ctx) => {
                            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 260);
                            gradient.addColorStop(0, 'rgba(59,130,246,0.25)');
                            gradient.addColorStop(1, 'rgba(59,130,246,0.01)');
                            return gradient;
                        },
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: isDark ? '#1f2937' : '#fff',
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark ? '#374151' : '#111827',
                            titleFont: { size: 12, weight: '600' },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: (ctx) => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.parsed.y)
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor, drawBorder: false },
                            ticks: {
                                color: textColor,
                                font: { size: 11 },
                                callback: (v) => v >= 1000000 ? (v / 1000000).toFixed(0) + ' jt' : v >= 1000 ? (v / 1000).toFixed(0) + ' rb' : v
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: textColor, font: { size: 11 } }
                        }
                    },
                    interaction: { intersect: false, mode: 'index' }
                }
            });
        });
    </script>
</x-filament-panels::page>
