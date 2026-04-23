@extends('layouts.portal-guest')

@section('title', 'Lupa Password - Borotax Portal Wajib Pajak')

@section('nav-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ route('portal.login') }}">Login</a>
@endsection

@section('nav-mobile-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ route('portal.login') }}">Login</a>
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
                radial-gradient(circle at top left, rgba(108, 172, 207, 0.18), transparent 32%),
                linear-gradient(145deg, var(--secondary-dark) 0%, #1F2D46 52%, #4A8BAE 100%);
        }

        .auth-card {
            width: 100%;
            max-width: 500px;
            padding: 34px 30px;
            border-radius: var(--radius-xl);
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 24px 48px rgba(15, 23, 36, 0.28);
        }

        .auth-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px;
            margin-bottom: 18px;
            border-radius: var(--radius-full);
            background: rgba(255, 255, 255, 0.1);
            color: var(--primary-light);
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .auth-card h1 {
            margin-bottom: 10px;
            font-size: 1.9rem;
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
            border-color: rgba(168, 212, 232, 0.9);
            box-shadow: 0 0 0 3px rgba(108, 172, 207, 0.16);
        }

        .field input::placeholder {
            color: rgba(255, 255, 255, 0.35);
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

        .auth-meta {
            margin-top: 22px;
            display: flex;
            justify-content: space-between;
            gap: 14px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.84rem;
        }

        .auth-meta a {
            color: var(--primary-light);
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
            <div class="auth-eyebrow">Reset Password Portal</div>
            <h1>Minta kode OTP reset password</h1>
            <p>Masukkan email akun portal wajib pajak Anda. Jika akun aktif ditemukan, sistem akan mengirim OTP 6 digit ke email tersebut.</p>

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

            <form method="POST" action="{{ route('portal.password.forgot.send') }}">
                @csrf

                <div class="field">
                    <label for="email">Email akun portal</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="contoh@domain.com" required autofocus>
                </div>

                <div class="auth-actions">
                    <button type="submit" class="btn btn-primary">Kirim kode OTP</button>
                    <a href="{{ route('portal.login') }}" class="btn btn-outline-white">Kembali ke login</a>
                </div>
            </form>

            <div class="auth-meta">
                <span>OTP berlaku 3 menit dan maksimal 3 percobaan verifikasi.</span>
                <a href="{{ route('portal.password.forgot.verify') }}">Saya sudah punya kode</a>
            </div>
        </div>
    </div>
@endsection