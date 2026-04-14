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

it('truncates scheduler notification body safely for large expired billing batches', function () {
    createLargeBatchBackofficeUser('admin');
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

    $notificationBody = DB::table('notifications')
        ->value('data');

    $body = (string) data_get(json_decode((string) $notificationBody, true), 'body');

    expect($body)
        ->toContain('Billing batch: 352210100000269901, 352210100000269902, 352210100000269903, 352210100000269904, 352210100000269905 (+2 lainnya).')
        ->toContain('(+1 jenis lain)')
        ->toContain('(41102): 3 billing')
        ->toContain('(41101): 2 billing')
        ->toContain('Status asal: Menunggu Pembayaran: 7 billing.')
        ->not->toContain('352210100000269906, 352210100000269907');
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