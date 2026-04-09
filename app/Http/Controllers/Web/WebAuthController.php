<?php

namespace App\Http\Controllers\Web;

use App\Domain\Auth\Support\SingleSessionManager;
use App\Domain\Auth\Support\PasswordStandards;
use App\Http\Controllers\Controller;
use App\Domain\Auth\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (auth('portal')->check()) {
            if (auth('portal')->user()->must_change_password) {
                return redirect()->route('portal.force-password.form');
            }

            return redirect()->route('portal.dashboard');
        }

        return view('portal.auth.login');
    }

    /**
     * Handle login request.
     * Supports login with email OR NIK.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required' => 'Email atau NIK wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginInput = $request->input('email');
        $password = $request->input('password');

        // Find user by email_hash or nik_hash
        $inputHash = hash('sha256', strtolower(trim($loginInput)));

        $user = User::where('email_hash', $inputHash)
            ->orWhere('nik_hash', $inputHash)
            ->first();

        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email/NIK atau password salah.']);
        }

        // Check if account is locked
        if ($user->locked_until && now()->lt($user->locked_until)) {
            $remaining = now()->diffInMinutes($user->locked_until);
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => "Akun terkunci. Coba lagi dalam {$remaining} menit."]);
        }

        // Verify password
        if (!Hash::check($password, $user->password)) {
            // Increment failed attempts
            $user->increment('failed_login_attempts');

            // Lock after 5 failed attempts
            if ($user->failed_login_attempts >= 5) {
                $user->update(['locked_until' => now()->addMinutes(15)]);
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Terlalu banyak percobaan gagal. Akun terkunci selama 15 menit.']);
            }

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email/NIK atau password salah.']);
        }

        // Check if user is verified/active
        if (!in_array($user->status, ['verified', 'active'])) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Akun Anda belum diverifikasi. Silakan tunggu verifikasi dari admin.']);
        }

        // Cek jika user adalah admin/verifikator/petugas — arahkan ke /admin
        if (in_array($user->role, ['admin', 'verifikator', 'petugas'])) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Login Admin/Petugas berada di halaman ' . url('/admin') . '. Silakan login di halaman tersebut.']);
        }

        // Cek apakah user sudah terdaftar sebagai Wajib Pajak
        if (!$user->wajibPajak) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Anda belum terdaftar sebagai Wajib Pajak. Silakan daftarkan diri melalui aplikasi mobile Borotax atau melalui petugas.']);
        }

        // Reset failed attempts and login
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ]);

        auth('portal')->login($user, $request->boolean('remember'));

        $request->session()->regenerate();
        $singleSessionResult = SingleSessionManager::startWebSession($user, $password, $request, 'portal_web', 'portal');

        if ($user->must_change_password) {
            $redirect = redirect()->route('portal.force-password.form')
                ->with('status', 'Password awal harus diganti sebelum Anda dapat menggunakan portal.');

            if ($singleSessionResult['replaced_session_notice']) {
                $redirect->with('session_notice', $singleSessionResult['replaced_session_notice']);
            }

            return $redirect;
        }

        $redirect = redirect()->intended(route('portal.dashboard'));

        if ($singleSessionResult['replaced_session_notice']) {
            $redirect->with('session_notice', $singleSessionResult['replaced_session_notice']);
        }

        return $redirect;
    }

    /**
     * Show the forced password change form.
     */
    public function showForceChangePassword()
    {
        if (! auth('portal')->check()) {
            return redirect()->route('portal.login');
        }

        if (! auth('portal')->user()->must_change_password) {
            return redirect()->route('portal.dashboard');
        }

        return view('portal.auth.force-change-password');
    }

    /**
     * Update password for first-login flow.
     */
    public function updateForceChangePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => PasswordStandards::rules(),
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak sesuai.',
        ]);

        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return redirect()->route('portal.dashboard');
        }

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withInput($request->except(['current_password', 'password', 'password_confirmation']))
                ->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'must_change_password' => false,
        ]);

        return redirect()->route('portal.dashboard')
            ->with('status', 'Password berhasil diperbarui. Anda sekarang dapat menggunakan portal.');
    }

    /**
     * Show regular password change form.
     */
    public function showChangePassword()
    {
        return view('portal.auth.change-password');
    }

    /**
     * Update password from portal settings page.
     */
    public function updateChangePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => PasswordStandards::rules(),
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak sesuai.',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withInput($request->except(['current_password', 'password', 'password_confirmation']))
                ->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'must_change_password' => false,
        ]);

        return redirect()->route('portal.password.edit')
            ->with('status', 'Password berhasil diperbarui.');
    }

    /**
     * Logout and redirect to login.
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            SingleSessionManager::clearCurrentSession($request->user(), $request);
        }

        auth('portal')->logout();
        $request->session()->migrate(true);
        $request->session()->regenerateToken();

        return redirect()->route('portal.login')
            ->with('status', 'Anda telah berhasil keluar.');
    }
}
