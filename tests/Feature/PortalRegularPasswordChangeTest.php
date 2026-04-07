<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalRegularPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_dashboard_shows_last_password_change_indicator(): void
    {
        $passwordChangedAt = now()->subDay()->setTime(9, 30);

        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'portal-password-indicator@example.test',
            'password' => Hash::make('PasswordLama123'),
            'must_change_password' => false,
            'password_changed_at' => $passwordChangedAt,
        ]);

        $this->actingAs($wajibPajak->user);

        $this->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('Password terakhir')
            ->assertSee('Terakhir diubah: ' . $passwordChangedAt->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i'));
    }

    public function test_portal_dashboard_shows_stronger_warning_badge_when_password_has_never_been_changed(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'portal-password-warning@example.test',
            'password' => Hash::make('PasswordLama123'),
            'must_change_password' => false,
            'password_changed_at' => null,
        ]);

        $this->actingAs($wajibPajak->user);

        $this->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('Belum pernah diubah')
            ->assertSee('Perlu diperbarui')
            ->assertSee('Terakhir diubah: Belum pernah diubah');
    }

    public function test_authenticated_portal_user_can_open_regular_change_password_page(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'portal-password@example.test',
            'password' => Hash::make('PasswordLama123'),
            'must_change_password' => false,
            'password_changed_at' => now()->subDay(),
        ]);

        $this->actingAs($wajibPajak->user);

        $this->get(route('portal.password.edit'))
            ->assertOk()
            ->assertSee('Perbarui Password Portal')
            ->assertSee('Status Password Portal')
            ->assertSee('Password aktif');
    }

    public function test_regular_change_password_page_shows_consistent_warning_cta_when_password_has_never_been_changed(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'portal-password-edit-warning@example.test',
            'password' => Hash::make('PasswordLama123'),
            'must_change_password' => false,
            'password_changed_at' => null,
        ]);

        $this->actingAs($wajibPajak->user);

        $this->get(route('portal.password.edit'))
            ->assertOk()
            ->assertSee('Status Password Portal')
            ->assertSee('Belum pernah diubah')
            ->assertSee('Aksi disarankan')
            ->assertSee('indikator warning di portal berubah menjadi status aman');
    }

    public function test_authenticated_portal_user_can_change_password_from_regular_settings_page(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'portal-password-update@example.test',
            'password' => Hash::make('PasswordLama123'),
            'must_change_password' => false,
            'password_changed_at' => now()->subDay(),
        ]);

        $this->actingAs($wajibPajak->user);

        $this->post(route('portal.password.update'), [
            'current_password' => 'PasswordLama123',
            'password' => 'PasswordBaru456',
            'password_confirmation' => 'PasswordBaru456',
        ])
            ->assertRedirect(route('portal.password.edit'))
            ->assertSessionHas('status', 'Password berhasil diperbarui.');

        $wajibPajak->user->refresh();

        $this->assertTrue(Hash::check('PasswordBaru456', $wajibPajak->user->password));
        $this->assertFalse((bool) $wajibPajak->user->must_change_password);
        $this->assertNotNull($wajibPajak->user->password_changed_at);
    }
}