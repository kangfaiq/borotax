<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_force_password_flag_for_user_that_must_change_password(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'api-force@example.test',
            'password' => Hash::make('@Password123'),
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);

        $this->postJson('/api/v1/login', [
            'email' => $wajibPajak->user->email,
            'password' => '@Password123',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.must_change_password', true)
            ->assertJsonPath('data.auth_requirements.must_change_password', true)
            ->assertJsonPath('data.auth_requirements.error_code', 'PASSWORD_CHANGE_REQUIRED')
            ->assertJsonPath('data.auth_requirements.required_action.screen', 'force_change_password')
            ->assertJsonPath('data.auth_requirements.allowed_actions.1.endpoint', '/api/v1/update-password')
            ->assertJsonPath('message', 'Login berhasil. Password harus diganti sebelum melanjutkan.');
    }

    public function test_api_protected_routes_are_blocked_until_password_is_changed(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'api-blocked@example.test',
            'password' => Hash::make('@Password123'),
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);

        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => $wajibPajak->user->email,
            'password' => '@Password123',
        ])->assertOk();

        $token = $loginResponse->json('data.token');
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        $this->getJson('/api/v1/profile', $headers)
            ->assertOk()
            ->assertJsonPath('data.must_change_password', true)
            ->assertJsonPath('data.auth_requirements.error_code', 'PASSWORD_CHANGE_REQUIRED');

        $this->getJson('/api/v1/notifications/unread-count', $headers)
            ->assertStatus(423)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data.must_change_password', true)
            ->assertJsonPath('data.auth_requirements.error_code', 'PASSWORD_CHANGE_REQUIRED')
            ->assertJsonPath('data.auth_requirements.allowed_actions.0.endpoint', '/api/v1/profile');

        $this->postJson('/api/v1/update-password', [
            'current_password' => '@Password123',
            'password' => '@PasswordBaru123',
            'password_confirmation' => '@PasswordBaru123',
        ], $headers)
            ->assertOk();

        $wajibPajak->user->refresh();

        $this->assertFalse((bool) $wajibPajak->user->must_change_password);
        $this->assertNotNull($wajibPajak->user->password_changed_at);

        $this->getJson('/api/v1/notifications/unread-count', $headers)
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_api_update_password_rejects_password_that_does_not_meet_standard(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'api-weak-password@example.test',
            'password' => Hash::make('@Password123'),
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);

        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => $wajibPajak->user->email,
            'password' => '@Password123',
        ])->assertOk();

        $headers = [
            'Authorization' => 'Bearer ' . $loginResponse->json('data.token'),
            'Accept' => 'application/json',
        ];

        $this->postJson('/api/v1/update-password', [
            'current_password' => '@Password123',
            'password' => 'abcdefg',
            'password_confirmation' => 'abcdefg',
        ], $headers)
            ->assertStatus(400)
            ->assertJsonPath('message', 'Validation Error')
            ->assertJsonPath('data.password.0', 'Password harus mengandung minimal satu huruf kapital (A-Z).')
            ->assertJsonPath('data.password.1', 'Password harus mengandung minimal satu angka (0-9).')
            ->assertJsonPath('data.password.2', 'Password harus mengandung minimal satu tanda baca atau karakter non-alphabetic seperti !, @, #, $, %, ^.');
    }

    public function test_api_register_rejects_password_that_does_not_meet_standard(): void
    {
        $this->postJson('/api/v1/register', [
            'verification_token' => 'dummy-token',
            'nama_lengkap' => 'Portal Tester',
            'name' => 'Portal Tester',
            'nik' => '3579010101010001',
            'email' => 'portal-register@example.test',
            'password' => 'abcdefg',
            'password_confirmation' => 'abcdefg',
            'no_whatsapp' => '081234567890',
            'tempat_lahir' => 'Bojonegoro',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Veteran No. 1',
            'province_code' => '35',
            'regency_code' => '35.22',
            'district_code' => '35.22.01',
            'village_code' => '35.22.01.2001',
            'birth_regency_code' => '35.22',
        ])
            ->assertStatus(400)
            ->assertJsonPath('message', 'Validation Error')
            ->assertJsonPath('data.password.0', 'Password harus mengandung minimal satu huruf kapital (A-Z).')
            ->assertJsonPath('data.password.1', 'Password harus mengandung minimal satu angka (0-9).')
            ->assertJsonPath('data.password.2', 'Password harus mengandung minimal satu tanda baca atau karakter non-alphabetic seperti !, @, #, $, %, ^.');
    }
}