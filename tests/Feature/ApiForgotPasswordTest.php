<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\VerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApiForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_can_request_password_reset_otp_for_portal_user(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'api-forgot@example.test',
            'password' => Hash::make('@Password123'),
        ]);

        Mail::shouldReceive('raw')->once()->andReturnNull();

        $this->postJson('/api/v1/auth/forgot-password/request', [
            'email' => $wajibPajak->user->email,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.target', VerificationCode::maskEmail($wajibPajak->user->email))
            ->assertJsonPath('data.expires_in', 180)
            ->assertJsonPath('message', 'Kode OTP reset password telah dikirim ke email Anda.');

        $this->assertDatabaseHas('verification_codes', [
            'identifier' => $wajibPajak->user->email,
            'type' => VerificationCode::TYPE_PASSWORD_RESET,
            'is_used' => false,
        ]);
    }

    public function test_api_can_verify_password_reset_otp_and_update_password(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'api-reset@example.test',
            'password' => Hash::make('@Password123'),
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);

        $otp = VerificationCode::createForPasswordReset($wajibPajak->user->email);

        $verifyResponse = $this->postJson('/api/v1/auth/forgot-password/verify-otp', [
            'email' => $wajibPajak->user->email,
            'code' => $otp->code,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'OTP terverifikasi. Silakan lanjutkan reset password.');

        $this->postJson('/api/v1/auth/forgot-password/reset', [
            'verification_token' => $verifyResponse->json('data.verification_token'),
            'password' => '@PasswordBaru123!',
            'password_confirmation' => '@PasswordBaru123!',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Password berhasil direset. Silakan login menggunakan password baru.');

        $wajibPajak->user->refresh();
        $otp->refresh();

        $this->assertTrue(Hash::check('@PasswordBaru123!', $wajibPajak->user->password));
        $this->assertFalse((bool) $wajibPajak->user->must_change_password);
        $this->assertNotNull($wajibPajak->user->password_changed_at);
        $this->assertNull($otp->verification_token);
    }

    public function test_api_can_resend_password_reset_otp_via_explicit_endpoint(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'api-resend@example.test',
            'password' => Hash::make('@Password123'),
        ]);

        $firstOtp = VerificationCode::createForPasswordReset($wajibPajak->user->email);

        VerificationCode::where('id', $firstOtp->id)->update([
            'created_at' => now()->subMinutes(3),
            'sent_at' => now()->subMinutes(3),
        ]);

        Mail::shouldReceive('raw')->once()->andReturnNull();

        $this->postJson('/api/v1/auth/forgot-password/resend-otp', [
            'email' => $wajibPajak->user->email,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.target', VerificationCode::maskEmail($wajibPajak->user->email))
            ->assertJsonPath('data.expires_in', 180)
            ->assertJsonPath('message', 'Kode OTP reset password baru telah dikirim ke email Anda.');

        $firstOtp->refresh();
        $latestOtp = VerificationCode::findLatestActiveForIdentifier($wajibPajak->user->email, VerificationCode::TYPE_PASSWORD_RESET);

        $this->assertTrue($firstOtp->is_used);
        $this->assertNotNull($latestOtp);
        $this->assertNotTrue($latestOtp->is($firstOtp));
    }
}