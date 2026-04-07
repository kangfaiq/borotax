<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_login_redirects_new_wajib_pajak_to_forced_password_change_page(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'portal-force@example.test',
            'password' => Hash::make('@Password123'),
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);

        $response = $this->post(route('portal.login.submit'), [
            'email' => $wajibPajak->user->email,
            'password' => '@Password123',
        ]);

        $response
            ->assertRedirect(route('portal.force-password.form'))
            ->assertSessionHas('status');

        $this->assertAuthenticatedAs($wajibPajak->user);

        $this->get(route('portal.force-password.form'))
            ->assertOk()
            ->assertSee('Status Password Portal')
            ->assertSee('Belum pernah diubah')
            ->assertSee('Aksi wajib sekarang');
    }

    public function test_portal_routes_are_blocked_until_forced_password_is_changed(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'portal-blocked@example.test',
            'password' => Hash::make('@Password123'),
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);

        $this->actingAs($wajibPajak->user);

        $this->get(route('portal.dashboard'))
            ->assertRedirect(route('portal.force-password.form'));

        $this->post(route('portal.force-password.update'), [
            'current_password' => '@Password123',
            'password' => '@PasswordBaru123',
            'password_confirmation' => '@PasswordBaru123',
        ])
            ->assertRedirect(route('portal.dashboard'));

        $wajibPajak->user->refresh();

        $this->assertFalse((bool) $wajibPajak->user->must_change_password);
        $this->assertNotNull($wajibPajak->user->password_changed_at);
        $this->assertTrue(Hash::check('@PasswordBaru123', $wajibPajak->user->password));

        $this->get(route('portal.dashboard'))
            ->assertOk();
    }
}