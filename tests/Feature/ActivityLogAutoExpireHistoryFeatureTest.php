<?php

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Models\ActivityLog;
use App\Filament\Resources\ActivityLogResource;
use App\Filament\Resources\ActivityLogResource\Pages\ListActivityLogs;
use App\Filament\Resources\ActivityLogResource\Pages\ListAutoExpireActivityLogs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows auto-expire history from the backoffice activity log filter', function () {
    $actor = createActivityLogBackofficeUser('admin');
    $viewer = createActivityLogBackofficeUser('petugas');

    ActivityLog::create([
        'actor_id' => $actor->id,
        'actor_type' => 'system',
        'action' => ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES,
        'summary_count' => 2,
        'target_table' => 'taxes',
        'target_id' => '352210100000260401',
        'description' => 'Auto-expire batch test',
        'new_values' => [
            'count' => 2,
            'billing_codes' => ['352210100000260401', '352210100000260402'],
            'source_status_breakdown' => [
                ['label' => 'Menunggu Pembayaran', 'count' => 1],
                ['label' => 'Terverifikasi', 'count' => 1],
            ],
            'jenis_pajak_breakdown' => [
                ['label' => 'PBJT atas Makanan dan/atau Minuman (41102)', 'count' => 2],
            ],
        ],
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
    ]);

    ActivityLog::create([
        'actor_id' => $actor->id,
        'actor_type' => 'admin',
        'action' => 'UPDATE_USER',
        'target_table' => 'users',
        'target_id' => $actor->id,
        'description' => 'Unrelated activity test',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
    ]);

    $this->actingAs($viewer);

    $this->get(ActivityLogResource::getUrl('index'))
        ->assertOk()
        ->assertSee('Histori Auto-Expire');

    $this->get(ActivityLogResource::getAutoExpireHistoryUrl())
        ->assertOk()
        ->assertSee('Histori Auto-Expire')
        ->assertSee('Auto-expire batch test')
        ->assertSee('352210100000260401, 352210100000260402')
        ->assertSee('Menunggu Pembayaran: 1 billing; Terverifikasi: 1 billing')
        ->assertDontSee('Unrelated activity test');
});

it('supports quick date filters on the activity log page', function () {
    $viewer = createActivityLogBackofficeUser('admin');

    $todayLog = ActivityLog::create([
        'actor_id' => $viewer->id,
        'actor_type' => 'system',
        'action' => ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES,
        'target_table' => 'taxes',
        'target_id' => '352210100000260501',
        'description' => 'Auto-expire hari ini',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
    ]);

    $sevenDayLog = ActivityLog::create([
        'actor_id' => $viewer->id,
        'actor_type' => 'system',
        'action' => ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES,
        'target_table' => 'taxes',
        'target_id' => '352210100000260502',
        'description' => 'Auto-expire tujuh hari',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
    ]);

    $oldLog = ActivityLog::create([
        'actor_id' => $viewer->id,
        'actor_type' => 'system',
        'action' => ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES,
        'target_table' => 'taxes',
        'target_id' => '352210100000260503',
        'description' => 'Auto-expire lama',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
    ]);

    $sevenDayLog->forceFill([
        'created_at' => now()->subDays(6),
        'updated_at' => now()->subDays(6),
    ])->saveQuietly();

    $oldLog->forceFill([
        'created_at' => now()->subDays(35),
        'updated_at' => now()->subDays(35),
    ])->saveQuietly();

    $this->actingAs($viewer);

    Livewire::test(ListActivityLogs::class)
        ->assertTableFilterExists('quick_date_range')
        ->filterTable('quick_date_range', ActivityLogResource::QUICK_DATE_TODAY)
        ->assertCanSeeTableRecords([$todayLog])
        ->assertCanNotSeeTableRecords([$sevenDayLog, $oldLog])
        ->filterTable('quick_date_range', ActivityLogResource::QUICK_DATE_LAST_7_DAYS)
        ->assertCanSeeTableRecords([$todayLog, $sevenDayLog])
        ->assertCanNotSeeTableRecords([$oldLog])
        ->filterTable('quick_date_range', ActivityLogResource::QUICK_DATE_LAST_30_DAYS)
        ->assertCanSeeTableRecords([$todayLog, $sevenDayLog])
        ->assertCanNotSeeTableRecords([$oldLog]);
});

it('supports source status filters on the activity log page', function () {
    $viewer = createActivityLogBackofficeUser('admin');

    $verifiedLog = ActivityLog::create([
        'actor_id' => $viewer->id,
        'actor_type' => 'system',
        'action' => ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES,
        'summary_count' => 3,
        'source_statuses' => ',verified,',
        'target_table' => 'taxes',
        'target_id' => '352210100000260701',
        'description' => 'Auto-expire verified',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
    ]);

    $partialLog = ActivityLog::create([
        'actor_id' => $viewer->id,
        'actor_type' => 'system',
        'action' => ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES,
        'summary_count' => 2,
        'source_statuses' => ',partially_paid,',
        'target_table' => 'taxes',
        'target_id' => '352210100000260702',
        'description' => 'Auto-expire partial',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
    ]);

    $pendingLog = ActivityLog::create([
        'actor_id' => $viewer->id,
        'actor_type' => 'system',
        'action' => ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES,
        'summary_count' => 1,
        'source_statuses' => ',pending,',
        'target_table' => 'taxes',
        'target_id' => '352210100000260703',
        'description' => 'Auto-expire pending',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
    ]);

    $this->actingAs($viewer);

    Livewire::test(ListActivityLogs::class)
        ->assertTableFilterExists('source_status')
        ->filterTable('source_status', ActivityLogResource::SOURCE_STATUS_VERIFIED)
        ->assertCanSeeTableRecords([$verifiedLog])
        ->assertCanNotSeeTableRecords([$partialLog, $pendingLog])
        ->filterTable('source_status', ActivityLogResource::SOURCE_STATUS_PARTIALLY_PAID)
        ->assertCanSeeTableRecords([$partialLog])
        ->assertCanNotSeeTableRecords([$verifiedLog, $pendingLog]);
});

it('sorts auto-expire history by largest batch first', function () {
    $viewer = createActivityLogBackofficeUser('admin');

    $smallerBatch = ActivityLog::create([
        'actor_id' => $viewer->id,
        'actor_type' => 'system',
        'action' => ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES,
        'summary_count' => 2,
        'target_table' => 'taxes',
        'target_id' => '352210100000260601',
        'description' => 'Batch kecil',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
    ]);

    $biggerBatch = ActivityLog::create([
        'actor_id' => $viewer->id,
        'actor_type' => 'system',
        'action' => ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES,
        'summary_count' => 8,
        'target_table' => 'taxes',
        'target_id' => '352210100000260602',
        'description' => 'Batch besar',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
    ]);

    $this->actingAs($viewer);

    Livewire::test(ListAutoExpireActivityLogs::class)
        ->assertCanSeeTableRecords([$biggerBatch, $smallerBatch], inOrder: true);
});

function createActivityLogBackofficeUser(string $role): User
{
    return User::create([
        'name' => Str::headline($role) . ' Activity Log',
        'nama_lengkap' => Str::headline($role) . ' Activity Log',
        'email' => sprintf('%s-activity-log-%s@example.test', $role, Str::random(8)),
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}