@extends('layouts.portal-guest')

@section('title', 'Ganti Password Awal - Borotax Portal Wajib Pajak')

@section('nav-links')
    <form method="POST" action="{{ route('portal.logout') }}">
        @csrf
        <button type="submit" style="background:none;border:none;color:inherit;font:inherit;cursor:pointer;">Keluar</button>
    </form>
@endsection

@section('nav-mobile-links')
    <form method="POST" action="{{ route('portal.logout') }}">
        @csrf
        <button type="submit" style="background:none;border:none;color:inherit;font:inherit;cursor:pointer;">Keluar</button>
    </form>
@endsection

@section('styles')
    <style>
        .password-change-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 20px 60px;
            background: linear-gradient(160deg, var(--secondary-dark) 0%, var(--secondary) 50%, #3A5068 100%);
            position: relative;
            overflow: hidden;
        }

        .password-change-pattern {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .password-change-card {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 520px;
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xl);
            padding: 36px 32px;
        }

        .password-status-banner {
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            align-items: center;
            padding: 16px 18px;
            border-radius: var(--radius-lg);
            border: 1px solid rgba(239, 68, 68, 0.2);
            background: linear-gradient(135deg, rgba(127, 29, 29, 0.28), rgba(194, 65, 12, 0.18));
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.18);
        }

        .status-banner-copy {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 0;
        }

        .status-banner-label {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.5);
        }

        .status-banner-value {
            font-size: 0.98rem;
            font-weight: 800;
            color: #FFFFFF;
        }

        .status-banner-help {
            font-size: 0.86rem;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.5;
        }

        .status-banner-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: max-content;
            padding: 9px 12px;
            border-radius: var(--radius-full);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            position: relative;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.92), rgba(245, 158, 11, 0.95));
            border: 1px solid rgba(255, 255, 255, 0.14);
            color: #FFF7ED;
            box-shadow: 0 10px 22px rgba(239, 68, 68, 0.22);
        }

        .status-banner-badge::after {
            content: '';
            position: absolute;
            top: -4px;
            right: -4px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #FFFFFF;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.32);
        }

        .password-warning-card {
            margin-bottom: 22px;
            display: flex;
            gap: 14px;
            padding: 16px 18px;
            border-radius: var(--radius-lg);
            border: 1px solid rgba(245, 158, 11, 0.22);
            background: linear-gradient(135deg, rgba(120, 53, 15, 0.24), rgba(127, 29, 29, 0.16));
        }

        .password-warning-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #FDE68A;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.18), rgba(245, 158, 11, 0.24));
            position: relative;
        }

        .password-warning-icon::after {
            content: '';
            position: absolute;
            inset: -5px;
            border-radius: 50%;
            border: 2px solid rgba(245, 158, 11, 0.2);
            opacity: 0;
        }

        .password-warning-copy {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .password-warning-title {
            font-size: 0.84rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #FDE68A;
        }

        .password-warning-copy p {
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.72);
        }

        .password-change-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .password-change-header .logo {
            font-size: 1.8rem;
            font-weight: 900;
            color: var(--text-white);
            margin-bottom: 8px;
        }

        .password-change-header .logo .accent {
            color: var(--primary-light);
        }

        .password-change-header h1 {
            color: var(--text-white);
            font-size: 1.35rem;
            margin-bottom: 10px;
        }

        .password-change-header p,
        .password-change-note {
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .password-change-note {
            margin-bottom: 22px;
            padding: 12px 14px;
            border-radius: var(--radius-md);
            background: rgba(108, 172, 207, 0.12);
            border: 1px solid rgba(108, 172, 207, 0.25);
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 7px;
        }

        .form-input {
            width: 100%;
            padding: 13px 16px;
            background: rgba(255, 255, 255, 0.07);
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            border-radius: var(--radius-md);
            color: var(--text-white);
            font-size: 0.92rem;
            font-family: inherit;
            transition: all var(--transition);
            outline: none;
        }

        .form-input:focus {
            border-color: var(--primary);
            background: rgba(108, 172, 207, 0.08);
            box-shadow: 0 0 0 3px rgba(108, 172, 207, 0.15);
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .input-pw-wrap {
            position: relative;
        }

        .input-pw-wrap .form-input {
            padding-right: 44px;
        }

        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.4);
            font-size: 1rem;
            padding: 4px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 18px;
            font-size: 0.85rem;
        }

        .alert-danger {
            background: rgba(239, 83, 80, 0.12);
            border: 1px solid rgba(239, 83, 80, 0.25);
            color: #FCA5A5;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.12);
            border: 1px solid rgba(76, 175, 80, 0.25);
            color: #86EFAC;
        }

        .alert ul {
            list-style: none;
            padding: 0;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            font-size: 0.95rem;
            border-radius: var(--radius-md);
        }

        @keyframes passwordPagePulse {
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

        @keyframes passwordPageFloat {
            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-2px);
            }
        }

        @media (max-width: 640px) {
            .password-change-card {
                padding: 28px 22px;
            }

            .password-status-banner {
                grid-template-columns: 1fr;
            }

            .status-banner-badge {
                animation: passwordPageFloat 2.5s ease-in-out infinite;
            }

            .password-warning-icon::after {
                animation: passwordPagePulse 1.8s ease-out infinite;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .status-banner-badge,
            .password-warning-icon::after {
                animation: none !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="password-change-wrapper">
        <div class="password-change-pattern"></div>

        <div class="password-change-card">
            <div class="password-status-banner">
                <div class="status-banner-copy">
                    <span class="status-banner-label">Status Password Portal</span>
                    <span class="status-banner-value">Terakhir diubah: Belum pernah diubah</span>
                    <span class="status-banner-help">Akun ini masih memakai password awal atau password hasil reset. Anda harus memperbaruinya sekarang agar akses portal dibuka penuh.</span>
                </div>
                <span class="status-banner-badge">
                    <i class="bi bi-exclamation-diamond-fill"></i>
                    Belum pernah diubah
                </span>
            </div>

            <div class="password-warning-card">
                <span class="password-warning-icon">
                    <i class="bi bi-bell-fill"></i>
                </span>
                <div class="password-warning-copy">
                    <span class="password-warning-title">Aksi wajib sekarang</span>
                    <p>Perbarui password dari form di bawah agar indikator warning berubah menjadi status aman dan seluruh fitur portal dapat digunakan kembali.</p>
                </div>
            </div>

            <div class="password-change-header">
                <div class="logo">Boro<span class="accent">tax</span></div>
                <h1>Ganti Password Awal</h1>
                <p>Untuk keamanan akun, Anda harus mengganti password awal sebelum melanjutkan.</p>
            </div>

            <div class="password-change-note">
                Akun yang baru dibuat atau baru di-reset hanya dapat mengakses portal setelah password diganti.
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @session('status')
                <div class="alert alert-success">{{ $value }}</div>
            @endsession

            <form method="POST" action="{{ route('portal.force-password.update') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="current_password">Password Saat Ini</label>
                    <div class="input-pw-wrap">
                        <input type="password" id="current_password" name="current_password" class="form-input" placeholder="Masukkan password saat ini" required>
                        <button type="button" class="toggle-pw" onclick="togglePassword('current_password', this)">👁️</button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password Baru</label>
                    <div class="input-pw-wrap">
                        <input type="password" id="password" name="password" class="form-input" placeholder="Minimal 8 karakter" required>
                        <button type="button" class="toggle-pw" onclick="togglePassword('password', this)">👁️</button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label>
                    <div class="input-pw-wrap">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Ulangi password baru" required>
                        <button type="button" class="toggle-pw" onclick="togglePassword('password_confirmation', this)">👁️</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-submit">Simpan Password Baru</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function togglePassword(id, button) {
            const input = document.getElementById(id);

            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = '🙈';
            } else {
                input.type = 'password';
                button.textContent = '👁️';
            }
        }
    </script>
@endsection