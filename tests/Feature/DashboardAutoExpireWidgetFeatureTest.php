<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Models\ActivityLog;
use App\Filament\Pages\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardAutoExpireWidgetFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_largest_auto_expire_batch_widget(): void
    {
        $viewer = $this->createAdminPanelUser('admin');

        ActivityLog::create([
            'actor_id' => $viewer->id,
            'actor_type' => 'system',
            'action' => ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES,
            'summary_count' => 9,
            'source_statuses' => ',verified,partially_paid,',
            'target_table' => 'taxes',
            'target_id' => '352210100000260801',
            'description' => 'Batch terbesar dashboard',
            'new_values' => [
                'count' => 9,
                'billing_codes' => [
                    '352210100000260801',
                    '352210100000260802',
                    '352210100000260803',
                ],
                'source_status_breakdown' => [
                    ['label' => 'Terverifikasi', 'count' => 5],
                    ['label' => 'Dibayar Sebagian', 'count' => 4],
                ],
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        ActivityLog::create([
            'actor_id' => $viewer->id,
            'actor_type' => 'system',
            'action' => ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES,
            'summary_count' => 2,
            'source_statuses' => ',pending,',
            'target_table' => 'taxes',
            'target_id' => '352210100000260804',
            'description' => 'Batch kecil dashboard',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->actingAs($viewer);

        $this->get(Dashboard::getUrl())
            ->assertOk()
            ->assertSee('Batch Auto-Expire Terbesar (7 Hari)')
            ->assertSee('9 billing')
            ->assertSee('Status asal: Terverifikasi: 5 billing; Dibayar Sebagian: 4 billing')
            ->assertSee('Batch: 352210100000260801, 352210100000260802, 352210100000260803');
    }

    private function createAdminPanelUser(string $role): User
    {
        return User::create([
            'name' => Str::headline($role) . ' Dashboard Widget',
            'nama_lengkap' => Str::headline($role) . ' Dashboard Widget',
            'email' => sprintf('%s-dashboard-widget-%s@example.test', $role, Str::random(8)),
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
        ]);
    }
}