@extends('layouts.portal-guest')

@section('title', 'Atur Password Baru - Borotax Portal Wajib Pajak')

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
                radial-gradient(circle at top right, rgba(34, 197, 94, 0.16), transparent 26%),
                linear-gradient(145deg, #122235 0%, #18344D 50%, #376F85 100%);
        }

        .auth-card {
            width: 100%;
            max-width: 540px;
            padding: 34px 30px;
            border-radius: var(--radius-xl);
            background: rgba(255, 255, 255, 0.08);
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
            background: rgba(34, 197, 94, 0.12);
            color: #BBF7D0;
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
            margin-bottom: 18px;
            color: rgba(255, 255, 255, 0.72);
            line-height: 1.7;
            font-size: 0.92rem;
        }

        .auth-badge {
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px 14px;
            border-radius: var(--radius-full);
            background: rgba(255, 255, 255, 0.08);
            color: #DCFCE7;
            font-size: 0.82rem;
            font-weight: 700;
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
            border-color: rgba(134, 239, 172, 0.86);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.16);
        }

        .field input::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .standards {
            margin-bottom: 20px;
            padding: 16px 18px;
            border-radius: var(--radius-lg);
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .standards-title {
            margin-bottom: 10px;
            color: #DCFCE7;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .standards ul {
            margin: 0;
            padding-left: 18px;
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.86rem;
            line-height: 1.6;
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

        @media (max-width: 540px) {
            .auth-card {
                padding: 28px 22px;
            }

            .auth-actions .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-eyebrow">Password Baru</div>
            <h1>Atur password baru portal</h1>
            <p>OTP Anda sudah terverifikasi. Buat password baru untuk akun portal wajib pajak dengan email {{ $maskedEmail }}.</p>

            <div class="auth-badge">Sesi reset aktif selama token verifikasi masih berlaku.</div>

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

            <div class="standards">
                <div class="standards-title">Standar Password</div>
                <ul>
                    <li>Panjang minimal password adalah tujuh (7) karakter.</li>
                    <li>Terdiri dari minimal satu (1) karakter berupa huruf kapital (A-Z).</li>
                    <li>Terdiri dari minimal satu (1) angka (0-9).</li>
                    <li>Terdiri dari minimal satu tanda baca atau karakter non-alphabetic seperti !, @, #, $, %, ^.</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('portal.password.forgot.reset.update') }}">
                @csrf

                <div class="field">
                    <label for="password">Password baru</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password baru" required autofocus>
                </div>

                <div class="field">
                    <label for="password_confirmation">Konfirmasi password baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru" required>
                </div>

                <div class="auth-actions">
                    <button type="submit" class="btn btn-primary">Simpan password baru</button>
                    <a href="{{ route('portal.login') }}" class="btn btn-outline-white">Kembali ke login</a>
                </div>
            </form>
        </div>
    </div>
@endsection