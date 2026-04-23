<?php

use App\Domain\Auth\Models\VerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates password reset otp with longer expiry than registration otp', function () {
    $otp = VerificationCode::createForPasswordReset('portal-reset@example.test', '127.0.0.1');

    expect($otp->type)->toBe(VerificationCode::TYPE_PASSWORD_RESET)
        ->and($otp->identifier)->toBe('portal-reset@example.test')
        ->and($otp->max_attempts)->toBe(3)
        ->and($otp->sent_at->diffInSeconds($otp->expires_at))->toEqual(180.0);
});

it('invalidates older active otp when issuing a new password reset code', function () {
    $firstOtp = VerificationCode::createForPasswordReset('portal-reset@example.test');
    $secondOtp = VerificationCode::createForPasswordReset('portal-reset@example.test');

    expect($firstOtp->fresh()->is_used)->toBeTrue()
        ->and($secondOtp->fresh()->is_used)->toBeFalse()
        ->and(VerificationCode::findLatestActiveForIdentifier('portal-reset@example.test', VerificationCode::TYPE_PASSWORD_RESET)?->id)
        ->toBe($secondOtp->id);
});
