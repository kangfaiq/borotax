<div>
<style>
        /* ===== Custom Login Styles ===== */
        body.fi-body {
            background: #f0f4f8 !important;
        }
        .dark body.fi-body {
            background: #0f172a !important;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 960px;
            width: 100%;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12), 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 440px;
            }
        }

        /* LEFT PANEL — Branding */
        .login-brand {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 40%, #3b82f6 100%);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        @media (max-width: 768px) {
            .login-brand {
                padding: 32px 28px 28px;
            }
        }

        .login-brand::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 260px;
            height: 260px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
        }
        .login-brand::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: -60px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
        }

        .login-brand .brand-logo {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
        }
        .login-brand .brand-logo img {
            width: 56px;
            height: 56px;
            object-fit: contain;
        }

        .login-brand h1 {
            font-size: 1.75rem;
            font-weight: 900;
            color: white;
            margin: 0 0 6px;
            letter-spacing: -0.02em;
            position: relative;
            z-index: 1;
        }
        .login-brand .brand-subtitle {
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.7);
            margin: 0 0 28px;
            position: relative;
            z-index: 1;
            line-height: 1.5;
        }

        /* Feature pills */
        .brand-features {
            display: flex;
            flex-direction: column;
            gap: 10px;
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 260px;
        }
        .brand-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(4px);
        }
        .brand-feature .bf-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .brand-feature .bf-icon svg {
            width: 16px;
            height: 16px;
            color: white;
        }
        .brand-feature span {
            font-size: 0.78rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }

        /* RIGHT PANEL — Form */
        .login-form-panel {
            background: white;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .dark .login-form-panel {
            background: #1e293b;
        }
        @media (max-width: 768px) {
            .login-form-panel {
                padding: 32px 28px 40px;
            }
        }

        .login-form-panel .form-header {
            margin-bottom: 28px;
        }
        .login-form-panel .form-header h2 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #111827;
            margin: 0 0 6px;
        }
        .dark .login-form-panel .form-header h2 {
            color: #f1f5f9;
        }
        .login-form-panel .form-header p {
            font-size: 0.85rem;
            color: #6b7280;
            margin: 0;
        }
        .dark .login-form-panel .form-header p {
            color: #94a3b8;
        }

        /* Override Filament form styles for polish */
        .login-form-panel .fi-input-wrp {
            border-radius: 12px !important;
        }
        .login-form-panel .fi-btn-primary {
            border-radius: 12px !important;
            padding-top: 12px !important;
            padding-bottom: 12px !important;
            font-weight: 700 !important;
            font-size: 0.9rem !important;
            letter-spacing: 0.01em;
            transition: all 0.2s ease !important;
        }
        .login-form-panel .fi-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.35) !important;
        }

        /* Footer inside form */
        .login-footer {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
        }
        .dark .login-footer {
            border-top-color: #334155;
        }
        .login-footer p {
            font-size: 0.72rem;
            color: #9ca3af;
            margin: 0;
            line-height: 1.6;
        }
        .login-footer a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }

        .login-status-alert {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(59, 130, 246, 0.10);
            border: 1px solid rgba(59, 130, 246, 0.18);
            color: #1d4ed8;
            font-size: 0.84rem;
            line-height: 1.55;
        }

        .dark .login-status-alert {
            background: rgba(96, 165, 250, 0.10);
            border-color: rgba(96, 165, 250, 0.22);
            color: #bfdbfe;
        }

        /* Animated gradient accent */
        .login-accent-bar {
            height: 4px;
            background: linear-gradient(90deg, #1d4ed8, #3b82f6, #60a5fa, #3b82f6, #1d4ed8);
            background-size: 200% 100%;
            animation: shimmer 3s ease infinite;
        }
        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Time display */
        .login-time {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 0.68rem;
            color: rgba(255,255,255,0.4);
            z-index: 1;
        }

    </style>

    <div class="login-wrapper">
        <div class="login-container">
            {{-- LEFT: Branding --}}
            <div class="login-brand">
                <div class="brand-logo">
                    <img src="{{ asset('images/logo-pemkab.png') }}" alt="Logo">
                </div>
                <h1>Borotax</h1>
                <p class="brand-subtitle">
                    Sistem Informasi Pajak Daerah<br>
                    Kabupaten Bojonegoro
                </p>

                <div class="brand-features">
                    <div class="brand-feature">
                        <div class="bf-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                        </div>
                        <span>Pengelolaan yang aman & terenkripsi</span>
                    </div>
                    <div class="brand-feature">
                        <div class="bf-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                        </div>
                        <span>Dashboard & laporan real-time</span>
                    </div>
                    <div class="brand-feature">
                        <div class="bf-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>
                        </div>
                        <span>Billing digital & pembayaran QR</span>
                    </div>
                </div>

                <div class="login-time">
                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </div>
            </div>

            {{-- RIGHT: Form --}}
            <div class="login-form-panel">
                <div class="login-accent-bar" style="margin: -48px -40px 32px; border-radius: 0;"></div>

                <div class="form-header">
                    <h2>Masuk ke Sistem</h2>
                    <p>Silakan masuk dengan akun petugas Anda</p>
                </div>

                @session('status')
                    <div class="login-status-alert">{{ $value }}</div>
                @endsession

                {{ $this->content }}

                <div class="login-footer">
                    <p>
                        &copy; {{ date('Y') }} Badan Pendapatan Daerah<br>
                        Kabupaten Bojonegoro
                    </p>
                </div>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</div>
