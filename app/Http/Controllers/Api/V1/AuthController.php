<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\PasswordChangeRequirement;
use App\Domain\Auth\Support\PasswordStandards;
use App\Domain\Auth\Support\SingleSessionManager;
use App\Domain\Auth\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    /**
     * Register Wajib Pajak
     * 
     * Membutuhkan verification_token dari proses OTP verification
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_token' => 'required|string',
            'nama_lengkap' => 'required|string',
            'name' => 'required|string|max:100',
            'nik' => 'required|string|size:16', // Format NIK
            'email' => 'required|email',
            'password' => PasswordStandards::rules(),
            'no_whatsapp' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'province_code' => 'required|string',
            'regency_code' => 'required|string',
            'district_code' => 'required|string',
            'village_code' => 'required|string',
            'birth_regency_code' => 'required|string',
        ], [
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        // Validasi verification token dari OTP
        $otpRecord = VerificationCode::findByVerificationToken($request->verification_token);
        if (!$otpRecord) {
            return $this->sendError('Token verifikasi tidak valid atau sudah kedaluwarsa.', [], 401);
        }

        // Cek duplikat by Hash
        $emailHash = User::generateHash($request->email);
        if (User::where('email_hash', $emailHash)->exists()) {
            return $this->sendError('Email sudah terdaftar.', [], 400);
        }

        $nikHash = User::generateHash($request->nik);
        if (User::where('nik_hash', $nikHash)->exists()) {
            return $this->sendError('NIK sudah terdaftar.', [], 400);
        }

        $user = User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'name' => $request->name,
            'nik' => $request->nik,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'no_whatsapp' => $request->no_whatsapp,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat,
            'province_code' => $request->province_code,
            'regency_code' => $request->regency_code,
            'district_code' => $request->district_code,
            'village_code' => $request->village_code,
            'birth_regency_code' => $request->birth_regency_code,
            'status' => 'verified', // Verified via OTP
            'must_change_password' => false,
        ]);

        // Invalidate verification token (one-time use)
    $otpRecord->consumeVerificationToken();

        $singleSessionResult = SingleSessionManager::startApiSession($user, $request);

        return $this->sendResponse([
            'token' => $singleSessionResult['token'],
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama_lengkap, // Decrypted
                'email' => $user->email, // Decrypted
                'nik' => $user->nik, // Decrypted
                'role' => $user->role,
            ],
            'session_notice' => $singleSessionResult['replaced_session_notice'],
            'session_context' => $singleSessionResult['active_session'],
        ], 'Registrasi berhasil.');
    }

    public function requestPasswordResetOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $email = strtolower(trim((string) $request->input('email')));

        return $this->sendPasswordResetOtpForApi($request, $email, false);
    }

    public function resendPasswordResetOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $email = strtolower(trim((string) $request->input('email')));

        return $this->sendPasswordResetOtpForApi($request, $email, true);
    }

    private function sendPasswordResetOtpForApi(Request $request, string $email, bool $isResend)
    {
        $user = $this->findPortalPasswordResetUser($email);

        if (! $user) {
            return $this->sendResponse([
                'target' => VerificationCode::maskEmail($email),
                'expires_in' => 180,
            ], 'Jika email terdaftar sebagai akun portal aktif, kode OTP reset password telah dikirim.');
        }

        $identifierHash = VerificationCode::generateHash($email);

        if (VerificationCode::hasCooldown($identifierHash, VerificationCode::TYPE_PASSWORD_RESET)) {
            return $this->sendError('Tunggu 2 menit sebelum meminta kode OTP baru.', [], 429);
        }

        if (VerificationCode::countRecentRequests($identifierHash, VerificationCode::TYPE_PASSWORD_RESET) >= 3) {
            return $this->sendError('Terlalu banyak permintaan kode reset. Coba lagi dalam 15 menit.', [], 429);
        }

        $otp = VerificationCode::createForPasswordReset($email, $request->ip());

        if (! $this->sendPasswordResetOtpViaEmail($email, $otp->code)) {
            return $this->sendError('Gagal mengirim kode OTP reset password. Silakan coba lagi.', [], 500);
        }

        return $this->sendResponse([
            'target' => VerificationCode::maskEmail($email),
            'expires_in' => 180,
        ], $isResend
            ? 'Kode OTP reset password baru telah dikirim ke email Anda.'
            : 'Kode OTP reset password telah dikirim ke email Anda.');
    }

    public function verifyPasswordResetOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $email = strtolower(trim((string) $request->input('email')));
        $otp = VerificationCode::findLatestActiveForIdentifier($email, VerificationCode::TYPE_PASSWORD_RESET);

        if (! $otp) {
            return $this->sendError('Kode OTP tidak valid atau sudah tidak aktif.', [], 400);
        }

        if ($otp->hasExceededMaxAttempts()) {
            return $this->sendError('Terlalu banyak percobaan salah. Silakan minta kode OTP baru.', [], 429);
        }

        if ($otp->isExpired()) {
            return $this->sendError('Kode OTP sudah kedaluwarsa. Silakan minta kode baru.', [], 410);
        }

        if (! $otp->verifyCode((string) $request->input('code'))) {
            $otp->incrementAttempts();
            $remainingAttempts = max($otp->fresh()->max_attempts - $otp->fresh()->attempts, 0);

            return $this->sendError(
                "Kode OTP tidak valid. Sisa percobaan: {$remainingAttempts}.",
                ['remaining_attempts' => $remainingAttempts],
                400
            );
        }

        $token = $otp->markAsVerified();

        return $this->sendResponse([
            'verification_token' => $token,
            'valid_until' => $otp->fresh()->token_expires_at->toIso8601String(),
        ], 'OTP terverifikasi. Silakan lanjutkan reset password.');
    }

    public function resetForgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_token' => 'required|string',
            'password' => PasswordStandards::rules(),
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak sesuai.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $otp = VerificationCode::findByVerificationToken($request->verification_token);

        if (! $otp || $otp->type !== VerificationCode::TYPE_PASSWORD_RESET) {
            return $this->sendError('Token verifikasi tidak valid atau sudah kedaluwarsa.', [], 401);
        }

        $user = $this->findPortalPasswordResetUser($otp->identifier);

        if (! $user) {
            return $this->sendError('Akun portal untuk email ini tidak tersedia.', [], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'must_change_password' => false,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        $otp->consumeVerificationToken();

        return $this->sendResponse([], 'Password berhasil direset. Silakan login menggunakan password baru.');
    }

    /**
     * Login User
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string', // Bisa email atau NIK
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        // Cari user by Email Hash OR NIK Hash
        $input = $request->email; // Field namanya email, tapi isinya bisa NIK
        $hash = User::generateHash($input);

        $user = User::where('email_hash', $hash)
            ->orWhere('nik_hash', $hash)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Increment failed attempts logic here if strictly needed
            return $this->sendError('Email/NIK atau password salah.', [], 401);
        }

        if ($user->isLocked()) {
            return $this->sendError('Akun terkunci sementara karena terlalu banyak percobaan login.', [], 429);
        }

        // Reset failed attempts
        $user->resetFailedAttempts();
        $user->update(['last_login_at' => now()]);

        $singleSessionResult = SingleSessionManager::startApiSession($user, $request);
        $authRequirements = PasswordChangeRequirement::forApi((bool) $user->must_change_password);

        $message = $user->must_change_password
            ? 'Login berhasil. Password harus diganti sebelum melanjutkan.'
            : 'Login berhasil.';

        return $this->sendResponse([
            'token' => $singleSessionResult['token'],
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama_lengkap,
                'email' => $user->email,
                'role' => $user->role,
                'must_change_password' => (bool) $user->must_change_password,
                'password_changed_at' => $user->password_changed_at,
            ],
            'auth_requirements' => $authRequirements,
            'session_notice' => $singleSessionResult['replaced_session_notice'],
            'session_context' => $singleSessionResult['active_session'],
        ], $message);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        SingleSessionManager::clearCurrentSession($request->user(), $request);

        return $this->sendResponse([], 'Logout berhasil.');
    }

    /**
     * Get Profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $authRequirements = PasswordChangeRequirement::forApi((bool) $user->must_change_password);

        // Decryption automatically handled by Eloquent Accessor (HasEncryptedAttributes trait)
        return $this->sendResponse([
            'id' => $user->id,
            'nik' => $user->nik,
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_whatsapp' => $user->no_whatsapp,
            'tempat_lahir' => $user->tempat_lahir,
            'tanggal_lahir' => $user->tanggal_lahir,
            'alamat' => $user->alamat,
            'province_code' => $user->province_code,
            'regency_code' => $user->regency_code,
            'district_code' => $user->district_code,
            'village_code' => $user->village_code,
            'birth_regency_code' => $user->birth_regency_code,
            'foto_url' => $user->foto_selfie_url, // Asumsi foto profil
            'role' => $user->role,
            'must_change_password' => (bool) $user->must_change_password,
            'password_changed_at' => $user->password_changed_at,
            'created_at' => $user->created_at,
            'auth_requirements' => $authRequirements,
        ], 'Data profile user.');
    }

    /**
     * Update Profile User
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'nama_lengkap' => 'sometimes|required|string',
            'email' => 'sometimes|required|email',
            'no_whatsapp' => 'sometimes|required|string',
            'tempat_lahir' => 'sometimes|required|string',
            'tanggal_lahir' => 'sometimes|required|date',
            'alamat' => 'sometimes|required|string',
            'province_code' => 'sometimes|required|string',
            'regency_code' => 'sometimes|required|string',
            'district_code' => 'sometimes|required|string',
            'village_code' => 'sometimes|required|string',
            'birth_regency_code' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        // Cek duplikat email jika email diubah
        if ($request->has('email') && $request->email !== $user->email) {
            $emailHash = User::generateHash($request->email);
            if (User::where('email_hash', $emailHash)->where('id', '!=', $user->id)->exists()) {
                return $this->sendError('Email sudah digunakan oleh user lain.', [], 400);
            }
        }

        $user->update($request->only([
            'name',
            'nama_lengkap',
            'email',
            'no_whatsapp',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat',
            'province_code',
            'regency_code',
            'district_code',
            'village_code',
            'birth_regency_code'
        ]));

        return $this->sendResponse([
            'id' => $user->id,
            'name' => $user->name,
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_whatsapp' => $user->no_whatsapp,
            'alamat' => $user->alamat,
        ], 'Profil berhasil diperbarui.');
    }

    /**
     * Update Password User
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => PasswordStandards::rules(),
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak sesuai.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Password saat ini tidak sesuai.', [], 401);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'must_change_password' => false,
        ]);

        return $this->sendResponse([], 'Password berhasil diperbarui.');
    }

    /**
     * Update PIN User
     */
    public function updatePin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'required|string|size:6|regex:/^[0-9]+$/|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $user = $request->user();
        $user->update([
            'pin' => Hash::make($request->pin),
        ]);

        return $this->sendResponse([], 'PIN berhasil diperbarui.');
    }

    /**
     * Verify PIN User
     */
    public function verifyPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $user = $request->user();

        if (!$user->pin || !Hash::check($request->pin, $user->pin)) {
            return $this->sendError('PIN yang Anda masukkan salah.', [], 401);
        }

        return $this->sendResponse([], 'PIN terverifikasi.');
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
