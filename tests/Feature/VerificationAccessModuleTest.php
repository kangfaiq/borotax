<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Shared\Models\DataChangeRequest;
use App\Domain\Tax\Models\PembetulanRequest;
use App\Domain\Tax\Models\Tax;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\TaxStatus;
use App\Filament\Resources\DataChangeRequestResource;
use App\Filament\Resources\DataChangeRequestResource\Pages\ListDataChangeRequests;
use App\Filament\Resources\PembetulanRequestResource;
use App\Filament\Resources\PembetulanRequestResource\Pages\ListPembetulanRequests;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VerificationAccessModuleTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('roleProvider')]
    public function test_data_change_and_pembetulan_verification_access_follow_role_rules(
        string $role,
        bool $canAccessVerificationModules
    ): void {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $user = $this->createAdminPanelUser($role);
        $dataChangeRequest = $this->createDataChangeRequest();
        $pembetulanRequest = $this->createPembetulanRequest();

        $this->actingAs($user);

        $dataChangeIndex = $this->get(DataChangeRequestResource::getUrl('index'));
        $this->assertAccessExpectation($dataChangeIndex->getStatusCode(), $canAccessVerificationModules, "data change request index for {$role}");

        $pembetulanIndex = $this->get(PembetulanRequestResource::getUrl('index'));
        $this->assertAccessExpectation($pembetulanIndex->getStatusCode(), $canAccessVerificationModules, "pembetulan request index for {$role}");

        if (! $canAccessVerificationModules) {
            return;
        }

        Livewire::test(ListDataChangeRequests::class)
            ->assertCanSeeTableRecords([$dataChangeRequest])
            ->assertTableActionVisible('approve', $dataChangeRequest)
            ->assertTableActionVisible('reject', $dataChangeRequest);

        Livewire::test(ListPembetulanRequests::class)
            ->assertCanSeeTableRecords([$pembetulanRequest])
            ->assertTableActionVisible('proses', $pembetulanRequest)
            ->assertTableActionVisible('selesai', $pembetulanRequest)
            ->assertTableActionVisible('tolak', $pembetulanRequest);
    }

    public static function roleProvider(): array
    {
        return [
            'admin' => ['admin', true],
            'verifikator' => ['verifikator', true],
            'petugas' => ['petugas', false],
        ];
    }

    private function createDataChangeRequest(): DataChangeRequest
    {
        $petugas = $this->createAdminPanelUser('petugas');
        $wajibPajak = $this->createApprovedWajibPajak();

        return DataChangeRequest::create([
            'entity_type' => 'wajib_pajak',
            'entity_id' => $wajibPajak->id,
            'field_changes' => [
                'alamat' => [
                    'old' => 'Jl. Veteran No. 12',
                    'new' => 'Jl. Diponegoro No. 8',
                ],
            ],
            'alasan_perubahan' => 'Koreksi alamat wajib pajak.',
            'status' => 'pending',
            'requested_by' => $petugas->id,
        ]);
    }

    private function createPembetulanRequest(): PembetulanRequest
    {
        $wajibPajak = $this->createApprovedWajibPajak();
        $jenisPajak = JenisPajak::where('kode', '41101')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();

        $tax = Tax::create([
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'user_id' => $wajibPajak->user_id,
            'amount' => 100000,
            'omzet' => 1000000,
            'tarif_persentase' => 10,
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000260001',
            'payment_expired_at' => now()->addDays(30),
            'masa_pajak_bulan' => 2,
            'masa_pajak_tahun' => 2026,
            'pembetulan_ke' => 0,
        ]);

        return PembetulanRequest::create([
            'tax_id' => $tax->id,
            'user_id' => $wajibPajak->user_id,
            'alasan' => 'Omzet yang dilaporkan perlu dikoreksi.',
            'omzet_baru' => 900000,
            'status' => 'pending',
        ]);
    }

    private function createApprovedWajibPajak(): WajibPajak
    {
        $nik = str_pad((string) random_int(0, 9999999999999999), 16, '0', STR_PAD_LEFT);
        $npwpd = 'P1' . str_pad((string) random_int(1, 99999999999), 11, '0', STR_PAD_LEFT);

        $user = User::create([
            'name' => 'Portal User ' . Str::random(6),
            'email' => sprintf('portal-%s@example.test', Str::random(8)),
            'password' => Hash::make('password'),
            'nik' => $nik,
            'nama_lengkap' => 'Budi Lama',
            'alamat' => 'Jl. Veteran No. 12',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        return WajibPajak::create([
            'user_id' => $user->id,
            'nik' => $nik,
            'nama_lengkap' => 'Budi Lama',
            'alamat' => 'Jl. Veteran No. 12',
            'asal_wilayah' => 'bojonegoro',
            'tipe_wajib_pajak' => 'perorangan',
            'status' => 'disetujui',
            'npwpd' => $npwpd,
            'tanggal_daftar' => now()->subDays(5),
            'tanggal_verifikasi' => now()->subDays(4),
        ]);
    }

    private function createAdminPanelUser(string $role): User
    {
        return User::create([
            'name' => Str::headline($role) . ' User',
            'nama_lengkap' => Str::headline($role) . ' User',
            'email' => sprintf('%s-%s@example.test', $role, Str::random(8)),
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => 'verified',
            'email_verified_at' => now(),
            'navigation_mode' => 'topbar',
        ]);
    }

    private function assertAccessExpectation(int $statusCode, bool $isAllowed, string $context): void
    {
        if ($isAllowed) {
            $this->assertSame(200, $statusCode, "Expected 200 for {$context}, got {$statusCode}.");

            return;
        }

        $this->assertContains($statusCode, [403, 404], "Expected 403/404 for {$context}, got {$statusCode}.");
    }
}