<?php

namespace App\Http\Controllers\Web;

use App\Domain\Auth\Support\SingleSessionManager;
use App\Domain\Auth\Support\PasswordStandards;
use App\Http\Controllers\Controller;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WebAuthController extends Controller
{
    private const PASSWORD_RESET_EMAIL_KEY = 'portal_password_reset_email';
    private const PASSWORD_RESET_TOKEN_KEY = 'portal_password_reset_token';

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

    public function showForgotPasswordRequest()
    {
        return view('portal.auth.forgot-password');
    }

    public function requestPasswordResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
        ]);

        $email = strtolower(trim((string) $request->input('email')));

        return $this->sendPasswordResetOtpForPortal($request, $email, false);
    }

    public function resendPasswordResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
        ]);

        $email = strtolower(trim((string) $request->input('email')));

        return $this->sendPasswordResetOtpForPortal($request, $email, true);
    }

    private function sendPasswordResetOtpForPortal(Request $request, string $email, bool $isResend)
    {
        $request->session()->forget(self::PASSWORD_RESET_TOKEN_KEY);
        $request->session()->put(self::PASSWORD_RESET_EMAIL_KEY, $email);
        $errorRedirect = $isResend
            ? redirect()->route('portal.password.forgot.verify')
            : back();

        $user = $this->findPortalPasswordResetUser($email);

        if (! $user) {
            return redirect()->route('portal.password.forgot.verify')
                ->with('status', 'Jika email terdaftar sebagai akun portal aktif, kode OTP reset password telah dikirim.');
        }

        $identifierHash = VerificationCode::generateHash($email);

        if (VerificationCode::hasCooldown($identifierHash, VerificationCode::TYPE_PASSWORD_RESET)) {
                        return $errorRedirect
                ->withInput()
                ->withErrors(['email' => 'Tunggu 2 menit sebelum meminta kode OTP baru.']);
        }

        if (VerificationCode::countRecentRequests($identifierHash, VerificationCode::TYPE_PASSWORD_RESET) >= 3) {
                        return $errorRedirect
                ->withInput()
                ->withErrors(['email' => 'Terlalu banyak permintaan kode reset. Coba lagi dalam 15 menit.']);
        }

        $otp = VerificationCode::createForPasswordReset($email, $request->ip());

        if (! $this->sendPasswordResetOtpViaEmail($email, $otp->code)) {
                        return $errorRedirect
                ->withInput()
                ->withErrors(['email' => 'Gagal mengirim kode OTP reset password. Silakan coba lagi.']);
        }

        return redirect()->route('portal.password.forgot.verify')
            ->with('status', $isResend
                ? 'Kode OTP reset password baru telah dikirim ke email Anda.'
                : 'Kode OTP reset password telah dikirim ke email Anda.');
    }

    public function showForgotPasswordVerify(Request $request)
    {
        return view('portal.auth.verify-password-reset-otp', [
            'email' => old('email', $request->session()->get(self::PASSWORD_RESET_EMAIL_KEY)),
        ]);
    }

    public function verifyPasswordResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
            'code.required' => 'Kode OTP wajib diisi.',
            'code.digits' => 'Kode OTP harus terdiri dari 6 digit.',
        ]);

        $email = strtolower(trim((string) $request->input('email')));
        $code = (string) $request->input('code');

        $request->session()->put(self::PASSWORD_RESET_EMAIL_KEY, $email);

        $otp = VerificationCode::findLatestActiveForIdentifier($email, VerificationCode::TYPE_PASSWORD_RESET);

        if (! $otp) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['code' => 'Kode OTP tidak valid atau sudah tidak aktif.']);
        }

        if ($otp->hasExceededMaxAttempts()) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['code' => 'Terlalu banyak percobaan salah. Silakan minta kode OTP baru.']);
        }

        if ($otp->isExpired()) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['code' => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.']);
        }

        if (! $otp->verifyCode($code)) {
            $otp->incrementAttempts();
            $remainingAttempts = max($otp->fresh()->max_attempts - $otp->fresh()->attempts, 0);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['code' => "Kode OTP tidak valid. Sisa percobaan: {$remainingAttempts}."]);
        }

        $token = $otp->markAsVerified();

        $request->session()->put(self::PASSWORD_RESET_TOKEN_KEY, $token);

        return redirect()->route('portal.password.forgot.reset')
            ->with('status', 'OTP terverifikasi. Silakan buat password baru.');
    }

    public function showForgotPasswordReset(Request $request)
    {
        $otp = $this->getVerifiedPasswordResetOtp($request);

        if (! $otp) {
            return redirect()->route('portal.password.forgot.form')
                ->withErrors(['email' => 'Sesi reset password tidak valid atau sudah kedaluwarsa.']);
        }

        return view('portal.auth.reset-password', [
            'maskedEmail' => VerificationCode::maskEmail($otp->identifier),
        ]);
    }

    public function resetForgotPassword(Request $request)
    {
        $request->validate([
            'password' => PasswordStandards::rules(),
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak sesuai.',
        ]);

        $otp = $this->getVerifiedPasswordResetOtp($request);

        if (! $otp) {
            return redirect()->route('portal.password.forgot.form')
                ->withErrors(['email' => 'Sesi reset password tidak valid atau sudah kedaluwarsa.']);
        }

        $user = $this->findPortalPasswordResetUser($otp->identifier);

        if (! $user) {
            $this->clearPasswordResetSession($request);

            return redirect()->route('portal.password.forgot.form')
                ->withErrors(['email' => 'Akun portal untuk email ini tidak tersedia.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'must_change_password' => false,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        $otp->consumeVerificationToken();
        $this->clearPasswordResetSession($request);

        return redirect()->route('portal.login')
            ->with('status', 'Password berhasil direset. Silakan login menggunakan password baru.');
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

    private function findPortalPasswordResetUser(string $email): ?User
    {
        $user = User::where('email_hash', User::generateHash($email))->first();

        if (! $user) {
            return null;
        }

        if (! in_array($user->status, ['verified', 'active'], true)) {
            return null;
        }

        if (in_array($user->role, ['admin', 'verifikator', 'petugas'], true)) {
            return null;
        }

        if (! $user->wajibPajak || blank($user->email)) {
            return null;
        }

        return $user;
    }

    private function getVerifiedPasswordResetOtp(Request $request): ?VerificationCode
    {
        $token = $request->session()->get(self::PASSWORD_RESET_TOKEN_KEY);

        if (! $token) {
            return null;
        }

        $otp = VerificationCode::findByVerificationToken($token);

        if (! $otp || $otp->type !== VerificationCode::TYPE_PASSWORD_RESET) {
            $this->clearPasswordResetSession($request);

            return null;
        }

        return $otp;
    }

    private function clearPasswordResetSession(Request $request): void
    {
        $request->session()->forget([
            self::PASSWORD_RESET_EMAIL_KEY,
            self::PASSWORD_RESET_TOKEN_KEY,
        ]);
    }

    private function sendPasswordResetOtpViaEmail(string $email, string $code): bool
    {
        try {
            Mail::raw(
                "Kode OTP reset password Borotax Anda: {$code}\n\nKode ini berlaku selama 3 menit.\nJangan bagikan kode ini kepada siapapun.",
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('OTP Reset Password Borotax');
                }
            );

            return true;
        } catch (\Throwable $exception) {
            Log::error('Failed to send password reset OTP email', [
                'email' => $email,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
