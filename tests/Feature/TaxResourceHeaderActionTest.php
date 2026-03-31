<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\TaxStatus;
use App\Filament\Resources\TaxResource;
use App\Filament\Resources\TaxResource\Pages\ListTaxes;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TaxResourceHeaderActionTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('backofficeRoleProvider')]
    public function test_tax_resource_header_actions_are_visible_for_backoffice_roles(string $role): void
    {
        $tax = $this->createTaxForReport();
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        $indexResponse = $this->get(TaxResource::getUrl('index'));

        $this->assertSame(200, $indexResponse->getStatusCode());

        Livewire::test(ListTaxes::class)
            ->assertCanSeeTableRecords([$tax])
            ->assertTableActionVisible('copy')
            ->assertTableActionVisible('export');
    }

    #[DataProvider('backofficeRoleProvider')]
    public function test_tax_resource_export_action_downloads_expected_csv_for_backoffice_roles(string $role): void
    {
        $tax = $this->createTaxForReport();
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        Livewire::test(ListTaxes::class)
            ->callTableAction('export')
            ->assertFileDownloaded('laporan-pendapatan-' . now()->format('Y-m-d') . '.csv')
            ->tap(function ($component) use ($tax): void {
                $content = base64_decode(data_get($component->effects, 'download.content', ''), true);

                $this->assertIsString($content);
                $this->assertStringContainsString('"Tanggal Transaksi","Kode Pembayaran Aktif",Pembetulan,"Objek Pajak","Masa Pajak","Jumlah Pajak",Status,"Metode Bayar","Tanggal Bayar","Jatuh Tempo"', $content);
                $this->assertStringContainsString($tax->billing_code, $content);
                $this->assertStringContainsString('Menunggu Pembayaran', $content);
                $this->assertStringContainsString('Tahun 2026', $content);
            });
    }

    #[DataProvider('backofficeRoleProvider')]
    public function test_tax_resource_copy_action_builds_clipboard_payload_for_backoffice_roles(string $role): void
    {
        $tax = $this->createTaxForReport();
        $user = $this->createAdminPanelUser($role);

        $this->actingAs($user);

        Livewire::test(ListTaxes::class)
            ->callTableAction('copy')
            ->assertNoFileDownloaded()
            ->tap(function ($component) use ($tax): void {
                $expression = data_get($component->effects, 'xjs.0.expression', '');

                $this->assertIsString($expression);
                $this->assertStringContainsString($tax->billing_code, $expression);
                $this->assertStringContainsString('Menunggu Pembayaran', $expression);
                $this->assertStringContainsString('navigator.clipboard.writeText', $expression);
            });
    }

    public static function backofficeRoleProvider(): array
    {
        return [
            'admin' => ['admin'],
            'verifikator' => ['verifikator'],
            'petugas' => ['petugas'],
        ];
    }

    private function createTaxForReport(): Tax
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $portalUser = User::create([
            'name' => 'Portal User ' . Str::random(6),
            'email' => sprintf('portal-%s@example.test', Str::random(8)),
            'password' => Hash::make('password'),
            'nik' => '3522011234567890',
            'nama_lengkap' => 'Portal User',
            'alamat' => 'Jl. Veteran No. 12',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        $wajibPajak = WajibPajak::create([
            'user_id' => $portalUser->id,
            'nik' => '3522011234567890',
            'nama_lengkap' => 'Portal User',
            'alamat' => 'Jl. Veteran No. 12',
            'asal_wilayah' => 'bojonegoro',
            'tipe_wajib_pajak' => 'perorangan',
            'status' => 'disetujui',
            'npwpd' => 'P100000000001',
            'tanggal_daftar' => now()->subDays(5),
            'tanggal_verifikasi' => now()->subDays(4),
        ]);

        $jenisPajak = JenisPajak::where('kode', '41101')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();

        $taxObject = TaxObject::create([
            'nik' => $wajibPajak->nik,
            'nama_objek_pajak' => 'Objek Hotel Uji',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => $wajibPajak->npwpd,
            'nopd' => 1101,
            'alamat_objek' => 'Jl. Ahmad Yani No. 1',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'tarif_persen' => 10,
            'tanggal_daftar' => now()->toDateString(),
            'is_active' => true,
            'is_opd' => false,
            'is_insidentil' => false,
        ]);

        return Tax::create([
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'tax_object_id' => $taxObject->id,
            'user_id' => $portalUser->id,
            'amount' => '123456',
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000260001',
            'payment_channel' => 'QRIS',
            'payment_expired_at' => now()->addDays(7),
            'masa_pajak_tahun' => 2026,
            'pembetulan_ke' => 0,
            'billing_sequence' => 1,
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
}