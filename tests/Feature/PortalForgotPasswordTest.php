<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\VerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PortalForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_user_can_request_password_reset_otp(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'portal-forgot@example.test',
            'password' => Hash::make('@Password123'),
        ]);

        Mail::shouldReceive('raw')->once()->andReturnNull();

        $this->post(route('portal.password.forgot.send'), [
            'email' => $wajibPajak->user->email,
        ])
            ->assertRedirect(route('portal.password.forgot.verify'))
            ->assertSessionHas('status')
            ->assertSessionHas('portal_password_reset_email', $wajibPajak->user->email);

        $this->assertDatabaseHas('verification_codes', [
            'identifier' => $wajibPajak->user->email,
            'type' => VerificationCode::TYPE_PASSWORD_RESET,
            'is_used' => false,
        ]);
    }

    public function test_portal_user_can_verify_otp_and_reset_password(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'portal-reset@example.test',
            'password' => Hash::make('@Password123'),
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);

        $otp = VerificationCode::createForPasswordReset($wajibPajak->user->email);

        $this->withSession([
            'portal_password_reset_email' => $wajibPajak->user->email,
        ])->post(route('portal.password.forgot.verify.submit'), [
            'email' => $wajibPajak->user->email,
            'code' => $otp->code,
        ])
            ->assertRedirect(route('portal.password.forgot.reset'))
            ->assertSessionHas('portal_password_reset_token');

        $otp->refresh();

        $this->withSession([
            'portal_password_reset_email' => $wajibPajak->user->email,
            'portal_password_reset_token' => $otp->verification_token,
        ])->post(route('portal.password.forgot.reset.update'), [
            'password' => '@PasswordBaru123!',
            'password_confirmation' => '@PasswordBaru123!',
        ])
            ->assertRedirect(route('portal.login'))
            ->assertSessionHas('status');

        $wajibPajak->user->refresh();
        $otp->refresh();

        $this->assertTrue(Hash::check('@PasswordBaru123!', $wajibPajak->user->password));
        $this->assertFalse((bool) $wajibPajak->user->must_change_password);
        $this->assertNotNull($wajibPajak->user->password_changed_at);
        $this->assertNull($otp->verification_token);
    }

    public function test_portal_user_can_resend_password_reset_otp_from_verification_page(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'portal-resend@example.test',
            'password' => Hash::make('@Password123'),
        ]);

        $firstOtp = VerificationCode::createForPasswordReset($wajibPajak->user->email);

        VerificationCode::where('id', $firstOtp->id)->update([
            'created_at' => now()->subMinutes(3),
            'sent_at' => now()->subMinutes(3),
        ]);

        Mail::shouldReceive('raw')->once()->andReturnNull();

        $this->withSession([
            'portal_password_reset_email' => $wajibPajak->user->email,
        ])->post(route('portal.password.forgot.resend'), [
            'email' => $wajibPajak->user->email,
        ])
            ->assertRedirect(route('portal.password.forgot.verify'))
            ->assertSessionHas('status', 'Kode OTP reset password baru telah dikirim ke email Anda.');

        $firstOtp->refresh();
        $latestOtp = VerificationCode::findLatestActiveForIdentifier($wajibPajak->user->email, VerificationCode::TYPE_PASSWORD_RESET);

        $this->assertTrue($firstOtp->is_used);
        $this->assertNotNull($latestOtp);
        $this->assertNotTrue($latestOtp->is($firstOtp));
    }
}