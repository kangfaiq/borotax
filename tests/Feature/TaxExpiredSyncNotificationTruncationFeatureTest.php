<?php

use App\Domain\Auth\Models\User;
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

it('sends one expired billing notification per tax type', function () {
    $admin = createLargeBatchBackofficeUser('admin');
    createLargeBatchBackofficeUser('verifikator');
    createLargeBatchBackofficeUser('petugas');

    $wajibPajak = $this->createApprovedWajibPajakFixture();

    $taxObjects = [
        '41102' => $this->createTaxObjectFixture($wajibPajak, '41102'),
        '41101' => $this->createTaxObjectFixture($wajibPajak, '41101'),
        '41103' => $this->createTaxObjectFixture($wajibPajak, '41103'),
        '41107' => $this->createTaxObjectFixture($wajibPajak, '41107'),
    ];

    $fixtures = [
        ['kode' => '41102', 'billing_code' => '352210100000269901', 'bulan' => 1],
        ['kode' => '41102', 'billing_code' => '352210100000269902', 'bulan' => 2],
        ['kode' => '41102', 'billing_code' => '352210100000269903', 'bulan' => 3],
        ['kode' => '41101', 'billing_code' => '352210100000269904', 'bulan' => 4],
        ['kode' => '41101', 'billing_code' => '352210100000269905', 'bulan' => 5],
        ['kode' => '41103', 'billing_code' => '352210100000269906', 'bulan' => 6],
        ['kode' => '41107', 'billing_code' => '352210100000269907', 'bulan' => 7],
    ];

    foreach ($fixtures as $fixture) {
        $this->createTaxFixture($taxObjects[$fixture['kode']], $wajibPajak->user, [
            'status' => TaxStatus::Pending,
            'billing_code' => $fixture['billing_code'],
            'payment_expired_at' => now()->subDays(2),
            'masa_pajak_bulan' => $fixture['bulan'],
            'masa_pajak_tahun' => 2031,
        ]);
    }

    $this->artisan('tax:sync-expired-statuses')->assertSuccessful();

    $notifications = DB::table('notifications')
        ->where('notifiable_id', $admin->id)
        ->pluck('data')
        ->map(fn (string $payload): array => json_decode($payload, true));

    expect($notifications)->toHaveCount(4);

    $titles = $notifications->pluck('title')->values()->all();
    $bodies = $notifications->pluck('body')->values()->all();
    $actionUrls = $notifications
        ->map(fn (array $payload): ?string => data_get($payload, 'actions.0.url'))
        ->filter()
        ->values()
        ->all();

    expect(collect($titles)->contains(fn (string $title): bool => str_contains($title, '41102')))->toBeTrue();
    expect(collect($titles)->contains(fn (string $title): bool => str_contains($title, '41101')))->toBeTrue();
    expect(collect($titles)->contains(fn (string $title): bool => str_contains($title, '41103')))->toBeTrue();
    expect(collect($titles)->contains(fn (string $title): bool => str_contains($title, '41107')))->toBeTrue();

    expect(collect($bodies)->contains(fn (string $body): bool => str_contains($body, '3 billing') && str_contains($body, '41102')))->toBeTrue();
    expect(collect($bodies)->contains(fn (string $body): bool => str_contains($body, '2 billing') && str_contains($body, '41101')))->toBeTrue();
    expect(collect($bodies)->contains(fn (string $body): bool => str_contains($body, '1 billing') && str_contains($body, '41103')))->toBeTrue();
    expect(collect($bodies)->contains(fn (string $body): bool => str_contains($body, '+1 jenis lain')))->toBeTrue();

    expect($actionUrls)->each->toBe(
        \App\Filament\Resources\ActivityLogResource::getAutoExpireHistoryUrl(),
    );
});

function createLargeBatchBackofficeUser(string $role): User
{
    return User::create([
        'name' => Str::headline($role) . ' Large Batch',
        'nama_lengkap' => Str::headline($role) . ' Large Batch',
        'email' => sprintf('%s-large-batch-%s@example.test', $role, Str::random(8)),
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}