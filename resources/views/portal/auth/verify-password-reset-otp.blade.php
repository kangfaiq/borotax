@extends('layouts.portal-guest')

@section('title', 'Verifikasi OTP Reset Password - Borotax Portal Wajib Pajak')

@section('nav-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ route('portal.password.forgot.form') }}">Lupa Password</a>
@endsection

@section('nav-mobile-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ route('portal.password.forgot.form') }}">Lupa Password</a>
@endsection

@section('styles')
    <style>
        .auth-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 20px 60px;
            background:
                radial-gradient(circle at right top, rgba(249, 168, 38, 0.18), transparent 30%),
                linear-gradient(145deg, #162133 0%, #243854 56%, #4A8BAE 100%);
        }

        .auth-card {
            width: 100%;
            max-width: 520px;
            padding: 34px 30px;
            border-radius: var(--radius-xl);
            background: rgba(10, 18, 34, 0.42);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow: 0 24px 48px rgba(15, 23, 36, 0.28);
        }

        .auth-eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 7px 14px;
            margin-bottom: 18px;
            border-radius: var(--radius-full);
            background: rgba(249, 168, 38, 0.12);
            color: #FCD34D;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .auth-card h1 {
            margin-bottom: 10px;
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--text-white);
            line-height: 1.2;
        }

        .auth-card p {
            margin-bottom: 22px;
            color: rgba(255, 255, 255, 0.72);
            line-height: 1.7;
            font-size: 0.92rem;
        }

        .alert {
            padding: 12px 14px;
            border-radius: var(--radius-md);
            margin-bottom: 18px;
            font-size: 0.85rem;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.14);
            border: 1px solid rgba(248, 113, 113, 0.34);
            color: #FECACA;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.14);
            border: 1px solid rgba(134, 239, 172, 0.28);
            color: #DCFCE7;
        }

        .alert ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.84rem;
            font-weight: 700;
        }

        .field input {
            width: 100%;
            padding: 13px 15px;
            border-radius: var(--radius-md);
            border: 1.5px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-white);
            font: inherit;
            outline: none;
            transition: all var(--transition);
        }

        .field input:focus {
            border-color: rgba(252, 211, 77, 0.88);
            box-shadow: 0 0 0 3px rgba(249, 168, 38, 0.16);
        }

        .field input::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .otp-input {
            letter-spacing: 0.42em;
            text-align: center;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .otp-note {
            margin-top: -8px;
            margin-bottom: 18px;
            color: rgba(255, 255, 255, 0.58);
            font-size: 0.82rem;
        }

        .auth-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .auth-actions .btn {
            min-width: 180px;
        }

        .auth-inline-form {
            margin: 0;
        }

        .auth-inline-form .btn {
            min-width: 180px;
        }

        .auth-meta {
            margin-top: 22px;
            display: flex;
            justify-content: space-between;
            gap: 14px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.84rem;
        }

        .auth-meta a {
            color: #FCD34D;
            font-weight: 700;
        }

        @media (max-width: 540px) {
            .auth-card {
                padding: 28px 22px;
            }

            .auth-actions .btn {
                width: 100%;
            }

            .auth-meta {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')
    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-eyebrow">Verifikasi OTP</div>
            <h1>Masukkan kode OTP reset password</h1>
            <p>Periksa inbox email Anda, lalu masukkan OTP 6 digit untuk membuka langkah pembuatan password baru.</p>

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

            <form method="POST" action="{{ route('portal.password.forgot.verify.submit') }}">
                @csrf

                <div class="field">
                    <label for="email">Email akun portal</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}" placeholder="contoh@domain.com" required autofocus>
                </div>

                <div class="field">
                    <label for="code">Kode OTP 6 digit</label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}" class="otp-input" inputmode="numeric" maxlength="6" placeholder="000000" required>
                </div>

                <div class="otp-note">Kode berlaku 3 menit. Jika salah 3 kali, Anda harus meminta kode baru.</div>

                <div class="auth-actions">
                    <button type="submit" class="btn btn-primary">Verifikasi OTP</button>
                </div>
            </form>

            <form method="POST" action="{{ route('portal.password.forgot.resend') }}" class="auth-inline-form">
                @csrf
                <input type="hidden" name="email" value="{{ old('email', $email) }}">
                <div class="auth-actions" style="margin-top: 12px;">
                    <button type="submit" class="btn btn-outline-white">Kirim ulang OTP</button>
                </div>
            </form>

            <div class="auth-meta">
                <span>Gunakan email yang sama dengan email akun portal aktif.</span>
                <a href="{{ route('portal.login') }}">Kembali ke login</a>
            </div>
        </div>
    </div>
@endsection