<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class OtpController extends BaseController
{
    /**
     * Request OTP untuk registrasi
     * 
     * User mengirimkan email + no_whatsapp
     * Sistem generate OTP dan kirim via email
     */
    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'no_whatsapp' => 'required|string|min:10|max:15',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        // Cek apakah email sudah terdaftar
        $emailHash = User::generateHash($request->email);
        if (User::where('email_hash', $emailHash)->exists()) {
            return $this->sendError('Email sudah terdaftar.', [], 422);
        }

        $identifierHash = VerificationCode::generateHash($request->email);

        // Cek cooldown (2 menit antar request)
        if (VerificationCode::hasCooldown($identifierHash)) {
            return $this->sendError(
                'Terlalu cepat. Tunggu 2 menit sebelum meminta kode baru.',
                [],
                429
            );
        }

        // Cek rate limit (max 3 request per 15 menit)
        if (VerificationCode::countRecentRequests($identifierHash) >= 3) {
            return $this->sendError(
                'Terlalu banyak permintaan. Coba lagi dalam 15 menit.',
                [],
                429
            );
        }

        // Generate OTP
        $otp = VerificationCode::createForRegistration(
            $request->email,
            $request->ip()
        );

        // Kirim OTP via email
        $sent = $this->sendOtpViaEmail($request->email, $otp->code);

        if (!$sent) {
            return $this->sendError('Gagal mengirim kode OTP. Silakan coba lagi.', [], 500);
        }

        // Mask email untuk response
        $maskedEmail = VerificationCode::maskEmail($request->email);

        return $this->sendResponse([
            'otp_id' => $otp->id,
            'target' => $maskedEmail,
            'expires_in' => 30, // 30 detik
        ], 'Kode OTP telah dikirim ke email Anda.');
    }

    /**
     * Verify OTP
     * 
     * User mengirimkan otp_id + kode OTP
     * Jika valid, return verification_token untuk melanjutkan registrasi
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp_id' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $otp = VerificationCode::find($request->otp_id);

        if (!$otp || $otp->is_used) {
            return $this->sendError('Kode OTP tidak ditemukan.', [], 404);
        }

        // Cek max attempts (3x gagal = block)
        if ($otp->hasExceededMaxAttempts()) {
            return $this->sendError(
                'Terlalu banyak percobaan salah. Silakan minta kode OTP baru.',
                [],
                429
            );
        }

        // Cek expired
        if ($otp->isExpired()) {
            return $this->sendError(
                'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.',
                [],
                410
            );
        }

        // Verifikasi kode
        if (!$otp->verifyCode($request->code)) {
            $otp->incrementAttempts();
            $remainingAttempts = $otp->max_attempts - $otp->fresh()->attempts;

            return $this->sendError(
                "Kode OTP tidak valid. Sisa percobaan: {$remainingAttempts}.",
                ['remaining_attempts' => $remainingAttempts],
                400
            );
        }

        // OTP valid - generate verification token
        $token = $otp->markAsVerified();

        return $this->sendResponse([
            'verification_token' => $token,
            'valid_until' => $otp->fresh()->token_expires_at->toIso8601String(),
        ], 'OTP terverifikasi. Silakan lanjutkan registrasi.');
    }

    /**
     * Resend OTP
     * 
     * Kirim ulang OTP ke email yang sama
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $previousOtp = VerificationCode::find($request->otp_id);

        if (!$previousOtp) {
            return $this->sendError('OTP sebelumnya tidak ditemukan.', [], 404);
        }

        $email = $previousOtp->identifier;
        $identifierHash = VerificationCode::generateHash($email);

        // Cek cooldown (2 menit)
        if (VerificationCode::hasCooldown($identifierHash)) {
            return $this->sendError(
                'Tunggu 2 menit sebelum meminta kode baru.',
                [],
                429
            );
        }

        // Cek rate limit
        if (VerificationCode::countRecentRequests($identifierHash) >= 3) {
            return $this->sendError(
                'Batas maksimal pengiriman ulang tercapai. Coba lagi dalam 15 menit.',
                [],
                429
            );
        }

        // Invalidate OTP lama
        $previousOtp->update(['is_used' => true]);

        // Generate OTP baru
        $otp = VerificationCode::createForRegistration(
            $email,
            $request->ip()
        );

        // Kirim OTP baru via email
        $sent = $this->sendOtpViaEmail($email, $otp->code);

        if (!$sent) {
            return $this->sendError('Gagal mengirim kode OTP. Silakan coba lagi.', [], 500);
        }

        $maskedEmail = VerificationCode::maskEmail($email);

        return $this->sendResponse([
            'otp_id' => $otp->id,
            'target' => $maskedEmail,
            'expires_in' => 30,
        ], 'Kode OTP baru telah dikirim ke email Anda.');
    }

    /**
     * Kirim OTP via Email
     */
    private function sendOtpViaEmail(string $email, string $code): bool
    {
        try {
            Mail::raw(
                "Kode verifikasi Borotax Anda: {$code}\n\nKode ini berlaku selama 30 detik.\nJangan bagikan kode ini kepada siapapun.",
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Kode Verifikasi Borotax');
                }
            );

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send OTP email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
