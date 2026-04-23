@extends('layouts.portal-guest')

@section('title', 'Login - Borotax Portal Wajib Pajak')

@section('nav-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
@endsection

@section('nav-mobile-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
@endsection

@section('styles')
    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 20px 60px;
            background: linear-gradient(160deg, var(--secondary-dark) 0%, var(--secondary) 50%, #3A5068 100%);
            position: relative;
            overflow: hidden;
        }

        .login-wrapper::before {
            content: '';
            position: absolute;
            top: 15%;
            left: -8%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(108, 172, 207, 0.12) 0%, transparent 70%);
            animation: float 8s ease-in-out infinite;
        }

        .login-wrapper::after {
            content: '';
            position: absolute;
            bottom: 10%;
            right: -5%;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(108, 172, 207, 0.08) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite reverse;
        }

        .login-pattern {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .login-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 420px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-header .logo {
            font-size: 1.8rem;
            font-weight: 900;
            color: var(--text-white);
            margin-bottom: 8px;
        }

        .login-header .logo .accent {
            color: var(--primary-light);
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.55);
            font-size: 0.9rem;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xl);
            padding: 36px 32px;
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

        .form-checkbox-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .form-checkbox-row label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.55);
            font-size: 0.85rem;
            cursor: pointer;
        }

        .forgot-link {
            color: var(--primary-light);
            font-size: 0.84rem;
            font-weight: 700;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .form-checkbox-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            font-size: 0.95rem;
            border-radius: var(--radius-md);
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
        }

        .login-footer p {
            color: rgba(255, 255, 255, 0.45);
            font-size: 0.85rem;
        }

        .login-footer a {
            color: var(--primary-light);
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        /* Alerts */
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

        /* Password toggle */
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

        @media (max-width: 480px) {
            .login-card {
                padding: 28px 20px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="login-wrapper">
        <div class="login-pattern"></div>
        <div class="login-container">
            <div class="login-header">
                <div class="logo">Boro<span class="accent">tax</span></div>
                <p>Portal Wajib Pajak Kabupaten Bojonegoro</p>
            </div>

            <div class="login-card">
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

                <form method="POST" action="{{ route('portal.login.submit') }}">
                    @csrf

                    <div class="form-group">
                        <label class="form-label" for="email">Email atau NIK</label>
                        <input type="text" id="email" name="email" class="form-input" placeholder="Masukkan email atau NIK"
                            value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-pw-wrap">
                            <input type="password" id="password" name="password" class="form-input"
                                placeholder="Masukkan password" required>
                            <button type="button" class="toggle-pw" onclick="togglePassword()">👁️</button>
                        </div>
                    </div>

                    <div class="form-checkbox-row">
                        <label>
                            <input type="checkbox" name="remember">
                            Ingat saya
                        </label>
                        <a href="{{ route('portal.password.forgot.form') }}" class="forgot-link">Lupa password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                            <polyline points="10 17 15 12 10 7" />
                            <line x1="15" y1="12" x2="3" y2="12" />
                        </svg>
                        Masuk
                    </button>
                </form>
            </div>

            <div class="login-footer">
                <p>Belum punya akun? <a href="#">Download Aplikasi Mobile</a></p>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const btn = document.querySelector('.toggle-pw');
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁️';
            }
        }
    </script>
@endsection