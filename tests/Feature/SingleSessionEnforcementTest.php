<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\SingleSessionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class SingleSessionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_login_rotates_active_session_and_keeps_session_marker_for_current_browser(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'single-session-portal@example.test',
            'password' => Hash::make('PasswordPortal123!'),
            'must_change_password' => false,
        ]);

        $user = $wajibPajak->user;
        $user->createToken('OldMobileToken', ['*', SingleSessionManager::tokenAbility('legacy-session')]);

        $this->post(route('portal.login.submit'), [
            'email' => $user->email,
            'password' => 'PasswordPortal123!',
        ])
            ->assertRedirect(route('portal.dashboard'));

        $user->refresh();

        $this->assertNotNull($user->active_session_id);
        $this->assertSame('portal_web', $user->active_session_channel);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertSame($user->active_session_id, session(SingleSessionManager::PORTAL_SESSION_KEY));
    }

    public function test_portal_login_shows_notice_when_previous_session_is_replaced(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'single-session-portal-notice@example.test',
            'password' => Hash::make('PasswordPortal123!'),
            'must_change_password' => false,
        ]);

        $user = $wajibPajak->user;
        $user->update([
            'active_session_id' => (string) Str::uuid(),
            'active_session_channel' => 'mobile_api',
            'active_session_ip' => '10.10.10.5',
            'active_session_user_agent' => 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/135.0.0.0 Mobile Safari/537.36',
            'active_session_started_at' => now()->subMinutes(5),
        ]);

        $this->followingRedirects()->post(route('portal.login.submit'), [
            'email' => $user->email,
            'password' => 'PasswordPortal123!',
        ])
            ->assertOk()
            ->assertSee('Akun ini sebelumnya masih aktif di mobile api')
            ->assertSee('Chrome / Android')
            ->assertSee('10.10.10.5');
    }

    public function test_stale_portal_session_is_logged_out_when_account_is_used_elsewhere(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'single-session-stale-portal@example.test',
            'password' => Hash::make('PasswordPortal123!'),
            'must_change_password' => false,
        ]);

        $user = $wajibPajak->user;
        $user->update([
            'active_session_id' => (string) Str::uuid(),
            'active_session_channel' => 'admin_panel',
            'active_session_ip' => '192.168.1.20',
            'active_session_user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/135.0.0.0 Safari/537.36',
            'active_session_started_at' => now()->subMinutes(3),
        ]);

        $this->actingAs($user)
            ->withSession([SingleSessionManager::PORTAL_SESSION_KEY => (string) Str::uuid()])
            ->followingRedirects()
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('Sesi Anda berakhir karena akun ini login kembali di backoffice admin')
            ->assertSee('Chrome / Windows')
            ->assertSee('192.168.1.20');

        $this->assertGuest('portal');
    }

    public function test_stale_admin_session_is_redirected_to_admin_login(): void
    {
        $admin = $this->createBackofficeUser('admin', 'single-session-admin@example.test');
        $admin->update([
            'active_session_id' => (string) Str::uuid(),
            'active_session_channel' => 'portal_web',
            'active_session_ip' => '172.16.0.4',
            'active_session_user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/135.0.0.0 Safari/537.36',
            'active_session_started_at' => now()->subMinutes(4),
        ]);

        $this->actingAs($admin)
            ->withSession([SingleSessionManager::BACKOFFICE_SESSION_KEY => (string) Str::uuid()])
            ->followingRedirects()
            ->get('/admin')
            ->assertOk()
            ->assertSee('Sesi Anda berakhir karena akun ini login kembali di portal web')
            ->assertSee('Chrome / Windows')
            ->assertSee('172.16.0.4');

        $this->assertGuest('web');
    }

    public function test_portal_and_backoffice_can_stay_logged_in_in_the_same_browser_session(): void
    {
        $admin = $this->createBackofficeUser('admin', 'split-guard-admin@example.test');
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'split-guard-portal@example.test',
            'password' => Hash::make('PasswordPortal123!'),
            'must_change_password' => false,
        ]);

        $this->actingAs($admin, 'web');

        $this->followingRedirects()->get('/admin')->assertOk();
        $this->get(route('portal.login'))->assertOk();

        $this->post(route('portal.login.submit'), [
            'email' => $wajibPajak->user->email,
            'password' => 'PasswordPortal123!',
        ])->assertRedirect(route('portal.dashboard'));

        $this->assertAuthenticatedAs($admin, 'web');
        $this->assertAuthenticatedAs($wajibPajak->user, 'portal');

        $this->followingRedirects()->get('/admin')->assertOk();
        $this->get(route('portal.dashboard'))->assertOk();
    }

    public function test_portal_logout_keeps_backoffice_session_active(): void
    {
        $admin = $this->createBackofficeUser('admin', 'split-guard-logout-admin@example.test');
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'split-guard-logout-portal@example.test',
            'password' => Hash::make('PasswordPortal123!'),
            'must_change_password' => false,
        ]);

        $this->actingAs($admin, 'web');

        $this->post(route('portal.login.submit'), [
            'email' => $wajibPajak->user->email,
            'password' => 'PasswordPortal123!',
        ])->assertRedirect(route('portal.dashboard'));

        $this->post(route('portal.logout'))
            ->assertRedirect(route('portal.login'));

        $this->assertGuest('portal');
        $this->assertAuthenticatedAs($admin, 'web');
        $this->followingRedirects()->get('/admin')->assertOk();
    }

    public function test_backoffice_logout_keeps_portal_session_active(): void
    {
        $admin = $this->createBackofficeUser('admin', 'split-guard-filament-admin@example.test');
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'split-guard-filament-portal@example.test',
            'password' => Hash::make('PasswordPortal123!'),
            'must_change_password' => false,
        ]);

        $this->actingAs($admin, 'web');

        $this->post(route('portal.login.submit'), [
            'email' => $wajibPajak->user->email,
            'password' => 'PasswordPortal123!',
        ])->assertRedirect(route('portal.dashboard'));

        $this->post(route('filament.admin.auth.logout'))
            ->assertRedirect(route('filament.admin.auth.login'));

        $this->assertGuest('web');
        $this->assertAuthenticatedAs($wajibPajak->user, 'portal');
        $this->get(route('portal.dashboard'))->assertOk();
    }

    public function test_backoffice_roles_still_share_a_single_browser_session(): void
    {
        $admin = $this->createBackofficeUser('admin', 'split-guard-single-backoffice-admin@example.test');
        $petugas = $this->createBackofficeUser('petugas', 'split-guard-single-backoffice-petugas@example.test');

        $this->actingAs($admin, 'web');
        $this->assertAuthenticatedAs($admin, 'web');

        $this->actingAs($petugas, 'web');

        $this->assertAuthenticatedAs($petugas, 'web');
        $this->assertNotSame($admin->id, auth('web')->id());
    }

    public function test_api_login_returns_notice_and_latest_token_can_access_profile(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'single-session-api@example.test',
            'password' => Hash::make('PasswordApi123!'),
            'must_change_password' => false,
        ]);

        $loginResponseA = $this->postJson('/api/v1/login', [
            'email' => $wajibPajak->user->email,
            'password' => 'PasswordApi123!',
        ])->assertOk();

        $oldToken = $loginResponseA->json('data.token');

        $loginResponseB = $this->postJson('/api/v1/login', [
            'email' => $wajibPajak->user->email,
            'password' => 'PasswordApi123!',
        ])->assertOk();

        $newToken = $loginResponseB->json('data.token');

        $wajibPajak->user->refresh();
        $oldAccessToken = PersonalAccessToken::findToken($oldToken);
        $newAccessToken = PersonalAccessToken::findToken($newToken);

        $this->assertDatabaseCount('personal_access_tokens', 2);
        $this->assertNotSame($oldToken, $newToken);
        $this->assertNotNull($wajibPajak->user->active_session_id);
        $this->assertSame('mobile_api', $wajibPajak->user->active_session_channel);
        $this->assertSame('mobile_api', $loginResponseB->json('data.session_context.channel'));
        $this->assertStringContainsString('Akun ini sebelumnya masih aktif di mobile api', (string) $loginResponseB->json('data.session_notice'));
        $this->assertNotNull($oldAccessToken);
        $this->assertNotNull($newAccessToken);
        $this->assertNotSame($oldAccessToken->abilities, $newAccessToken->abilities);
        $this->assertContains(SingleSessionManager::tokenAbility($wajibPajak->user->active_session_id), $newAccessToken->abilities);
        $this->assertNotContains(SingleSessionManager::tokenAbility($wajibPajak->user->active_session_id), $oldAccessToken->abilities);

        $this->getJson('/api/v1/profile', [
            'Authorization' => 'Bearer ' . $newToken,
            'Accept' => 'application/json',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_previous_api_token_returns_informative_single_session_error_after_new_login(): void
    {
        $wajibPajak = $this->createApprovedWajibPajakFixture([], [
            'email' => 'single-session-api-stale@example.test',
            'password' => Hash::make('PasswordApi123!'),
            'must_change_password' => false,
        ]);

        $oldToken = $this->postJson('/api/v1/login', [
            'email' => $wajibPajak->user->email,
            'password' => 'PasswordApi123!',
        ])->assertOk()->json('data.token');

        $this->postJson('/api/v1/login', [
            'email' => $wajibPajak->user->email,
            'password' => 'PasswordApi123!',
        ])->assertOk();

        $this->getJson('/api/v1/profile', [
            'Authorization' => 'Bearer ' . $oldToken,
            'Accept' => 'application/json',
        ])
            ->assertStatus(401)
            ->assertJsonPath('data.session_context.channel', 'mobile_api')
            ->assertJsonPath('data.session_context.channel_label', 'Mobile API');
    }

    private function createBackofficeUser(string $role, string $email): User
    {
        return User::create([
            'name' => Str::headline($role) . ' User',
            'nama_lengkap' => Str::headline($role) . ' User',
            'email' => $email,
            'password' => Hash::make('PasswordAdmin123!'),
            'role' => $role,
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
        ]);
    }
}