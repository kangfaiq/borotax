<?php

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Models\ActivityLog;
use App\Filament\Resources\ActivityLogResource;
use App\Enums\TaxStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    test()->seed([
        Database\Seeders\JenisPajakSeeder::class,
        Database\Seeders\SubJenisPajakSeeder::class,
    ]);
});

it('syncs overdue unpaid billing statuses through the scheduled command', function () {
    createExpiredSyncBackofficeUser('admin');
    createExpiredSyncBackofficeUser('verifikator');
    createExpiredSyncBackofficeUser('petugas');

    $wajibPajak = $this->createApprovedWajibPajakFixture();
    $taxObject = $this->createTaxObjectFixture($wajibPajak, '41102');

    $pendingTax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
        'status' => TaxStatus::Pending,
        'billing_code' => '352210100000269821',
        'payment_expired_at' => now()->subDays(3),
        'masa_pajak_bulan' => 1,
        'masa_pajak_tahun' => 2030,
    ]);

    $verifiedTax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
        'status' => TaxStatus::Verified,
        'billing_code' => '352210100000269822',
        'payment_expired_at' => now()->subDays(1),
        'masa_pajak_bulan' => 2,
        'masa_pajak_tahun' => 2030,
    ]);

    $paidTax = $this->createTaxFixture($taxObject, $wajibPajak->user, [
        'status' => TaxStatus::Paid,
        'billing_code' => '352210100000269823',
        'paid_at' => now()->subDay(),
        'payment_expired_at' => now()->subDays(5),
        'masa_pajak_bulan' => 3,
        'masa_pajak_tahun' => 2030,
    ]);

    $this->artisan('tax:sync-expired-statuses')
        ->expectsOutput('Selesai. 2 billing overdue disinkronkan menjadi lewat jatuh tempo.')
        ->assertSuccessful();

    expect($pendingTax->fresh()->status)->toBe(TaxStatus::Expired)
        ->and($verifiedTax->fresh()->status)->toBe(TaxStatus::Expired)
        ->and($paidTax->fresh()->status)->toBe(TaxStatus::Paid);

    expect(ActivityLog::where('action', 'SYNC_EXPIRED_TAX_STATUSES')->count())->toBe(1)
        ->and(ActivityLog::where('action', 'SYNC_EXPIRED_TAX_STATUSES')->first()?->actor_type)->toBe('system');

    $this->assertDatabaseCount('notifications', 3);

    $notificationPayloads = DB::table('notifications')
        ->pluck('data')
        ->map(fn (string $data): array => json_decode($data, true))
        ->all();

    $notificationBodies = array_map(
        fn (array $data): string => (string) data_get($data, 'body'),
        $notificationPayloads,
    );

    expect($notificationBodies)->each->toContain('Billing batch: 352210100000269821, 352210100000269822')
        ->and($notificationBodies[0])->toContain('Ringkasan per jenis pajak:')
        ->and($notificationBodies[0])->toContain('(41102): 2 billing')
        ->and($notificationBodies[0])->toContain('PBJT')
        ->and($notificationBodies[0])->toContain('Status asal:')
        ->and($notificationBodies[0])->toContain('Menunggu Pembayaran: 1 billing')
        ->and($notificationBodies[0])->toContain('Terverifikasi: 1 billing');

    expect(data_get($notificationPayloads[0], 'actions.0.label'))->toBe('Lihat Histori Auto-Expire')
        ->and(data_get($notificationPayloads[0], 'actions.0.url'))->toBe(ActivityLogResource::getAutoExpireHistoryUrl());

    $activityLog = ActivityLog::where('action', ActivityLog::ACTION_SYNC_EXPIRED_TAX_STATUSES)->first();

    expect($activityLog?->summary_count)->toBe(2)
        ->and($activityLog?->hasSourceStatus(TaxStatus::Pending->value))->toBeTrue()
        ->and($activityLog?->hasSourceStatus(TaxStatus::Verified->value))->toBeTrue()
        ->and($activityLog?->hasSourceStatus(TaxStatus::PartiallyPaid->value))->toBeFalse()
        ->and($activityLog?->source_statuses)->toContain(',pending,')
        ->and($activityLog?->source_statuses)->toContain(',verified,')
        ->and(data_get($activityLog?->new_values, 'source_status_breakdown.0.count'))->toBe(1);
});

function createExpiredSyncBackofficeUser(string $role): User
{
    return User::create([
        'name' => Str::headline($role) . ' Expired Sync',
        'nama_lengkap' => Str::headline($role) . ' Expired Sync',
        'email' => sprintf('%s-sync-%s@example.test', $role, Str::random(8)),
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}