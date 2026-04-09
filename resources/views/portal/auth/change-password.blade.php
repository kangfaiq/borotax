@extends('layouts.portal-dashboard')

@section('title', 'Ubah Password - Borotax Portal')
@section('page-title', 'Ubah Password')

@section('styles')
<style>
    .password-page {
        max-width: 760px;
        margin: 0 auto;
    }

    .password-panel {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 28px;
        box-shadow: var(--shadow-sm);
    }

    .password-status-banner {
        margin-bottom: 18px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 16px;
        align-items: center;
        padding: 16px 18px;
        border-radius: var(--radius-lg);
        border: 1px solid rgba(var(--primary-rgb), 0.16);
        background: linear-gradient(135deg, rgba(235, 245, 250, 0.96), rgba(255, 255, 255, 0.96));
    }

    .password-status-banner.warning {
        border-color: rgba(239, 68, 68, 0.22);
        background: linear-gradient(135deg, rgba(254, 242, 242, 0.98), rgba(255, 247, 237, 0.98));
        box-shadow: 0 12px 28px rgba(239, 68, 68, 0.08);
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
        color: var(--text-tertiary);
    }

    .status-banner-value {
        font-size: 0.98rem;
        font-weight: 800;
        color: var(--text-primary);
    }

    .status-banner-help {
        font-size: 0.84rem;
        color: var(--text-secondary);
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
        border: 1px solid transparent;
    }

    .status-banner-badge.safe {
        background: rgba(var(--primary-rgb), 0.12);
        color: var(--primary-dark);
    }

    .status-banner-badge.warning {
        position: relative;
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.92), rgba(245, 158, 11, 0.95));
        border-color: rgba(255, 255, 255, 0.14);
        color: #FFF7ED;
        box-shadow: 0 10px 22px rgba(239, 68, 68, 0.2);
    }

    .status-banner-badge.warning::after {
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
        margin-bottom: 20px;
        display: flex;
        gap: 14px;
        padding: 16px 18px;
        border-radius: var(--radius-lg);
        border: 1px solid rgba(239, 68, 68, 0.16);
        background: linear-gradient(135deg, rgba(254, 242, 242, 0.98), rgba(255, 247, 237, 0.98));
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
        color: #B91C1C;
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.14), rgba(245, 158, 11, 0.18));
        position: relative;
    }

    .password-warning-icon::after {
        content: '';
        position: absolute;
        inset: -5px;
        border-radius: 50%;
        border: 2px solid rgba(239, 68, 68, 0.18);
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
        color: #B91C1C;
    }

    .password-warning-copy p {
        margin-bottom: 0;
        font-size: 0.88rem;
        color: #9A3412;
    }

    .password-panel h2 {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .password-panel p {
        color: var(--text-secondary);
        font-size: 0.92rem;
        margin-bottom: 22px;
    }

    .password-note {
        margin-bottom: 20px;
        padding: 14px 16px;
        border-radius: var(--radius-md);
        background: var(--primary-50);
        border: 1px solid rgba(var(--primary-rgb), 0.18);
        color: var(--primary-dark);
        font-size: 0.86rem;
    }

    .password-standards-card {
        margin-bottom: 20px;
        padding: 16px 18px;
        border-radius: var(--radius-lg);
        border: 1px solid rgba(var(--primary-rgb), 0.18);
        background: linear-gradient(135deg, rgba(235, 245, 250, 0.85), rgba(255, 255, 255, 0.95));
    }

    .password-standards-title {
        margin-bottom: 10px;
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--primary-dark);
    }

    .password-standards-list {
        margin: 0;
        padding-left: 18px;
        display: grid;
        gap: 8px;
        color: var(--text-secondary);
        font-size: 0.88rem;
        line-height: 1.5;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 18px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-label {
        font-size: 0.84rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .form-input {
        width: 100%;
        padding: 13px 16px;
        background: var(--bg-card);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-md);
        color: var(--text-primary);
        font-size: 0.92rem;
        font-family: inherit;
        transition: all var(--transition);
        outline: none;
    }

    .form-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.12);
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
        color: var(--text-tertiary);
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
        background: var(--error-light);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #b91c1c;
    }

    .alert-success {
        background: var(--success-light);
        border: 1px solid rgba(34, 197, 94, 0.2);
        color: #166534;
    }

    .alert ul {
        list-style: none;
        padding: 0;
    }

    .form-actions {
        margin-top: 8px;
        display: flex;
        justify-content: flex-end;
    }

    .btn-submit {
        min-width: 220px;
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
        .password-status-banner {
            grid-template-columns: 1fr;
        }

        .status-banner-badge.warning {
            animation: passwordPageFloat 2.5s ease-in-out infinite;
        }

        .password-warning-icon::after {
            animation: passwordPagePulse 1.8s ease-out infinite;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .status-banner-badge.warning,
        .password-warning-icon::after {
            animation: none !important;
        }
    }
</style>
@endsection

@section('content')
    @php
        $portalUser = auth()->user();
        $passwordChangedAt = $portalUser?->password_changed_at?->timezone(config('app.timezone'));
        $passwordChangedLabel = $passwordChangedAt
            ? $passwordChangedAt->translatedFormat('d M Y, H:i')
            : 'Belum pernah diubah';
    @endphp

    <div class="password-page">
        <div class="password-panel">
            <div class="password-status-banner {{ $passwordChangedAt ? '' : 'warning' }}">
                <div class="status-banner-copy">
                    <span class="status-banner-label">Status Password Portal</span>
                    <span class="status-banner-value">Terakhir diubah: {{ $passwordChangedLabel }}</span>
                    <span class="status-banner-help">
                        {{ $passwordChangedAt
                            ? 'Password akun sudah pernah diperbarui. Anda tetap bisa menggantinya kembali kapan saja dari halaman ini.'
                            : 'Password akun belum pernah diubah. Segera lakukan pembaruan dari form di bawah agar status keamanan akun konsisten dengan indikator di portal.' }}
                    </span>
                </div>
                <span class="status-banner-badge {{ $passwordChangedAt ? 'safe' : 'warning' }}">
                    <i class="bi {{ $passwordChangedAt ? 'bi-shield-check' : 'bi-exclamation-diamond-fill' }}"></i>
                    {{ $passwordChangedAt ? 'Password aktif' : 'Belum pernah diubah' }}
                </span>
            </div>

            @if (! $passwordChangedAt)
                <div class="password-warning-card">
                    <span class="password-warning-icon">
                        <i class="bi bi-bell-fill"></i>
                    </span>
                    <div class="password-warning-copy">
                        <span class="password-warning-title">Aksi disarankan</span>
                        <p>Perbarui password sekarang agar indikator warning di portal berubah menjadi status aman dan riwayat perubahan password langsung tercatat.</p>
                    </div>
                </div>
            @endif

            <h2>Perbarui Password Portal</h2>
            <p>Gunakan halaman ini untuk mengganti password akun portal kapan saja.</p>

            <div class="password-note">
                Untuk keamanan akun, gunakan password yang kuat dan jangan gunakan kembali password lama yang mudah ditebak.
            </div>

            @include('portal.auth.partials.password-standards')

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

            <form method="POST" action="{{ route('portal.password.update') }}">
                @csrf

                <div class="form-grid">
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
                            <input type="password" id="password" name="password" class="form-input" placeholder="Minimal 7 karakter, gunakan huruf, angka, dan simbol" required>
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
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-submit">Simpan Perubahan Password</button>
                </div>
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