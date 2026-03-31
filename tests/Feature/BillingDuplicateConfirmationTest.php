<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\TaxStatus;
use App\Filament\Pages\BuatBillingSelfAssessment;
use Carbon\Carbon;
use Database\Seeders\JenisPajakSeeder;
use Database\Seeders\SubJenisPajakSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class BillingDuplicateConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_replacement_confirmation_shows_existing_billing_period_and_amount(): void
    {
        $this->seed([
            JenisPajakSeeder::class,
            SubJenisPajakSeeder::class,
        ]);

        $petugas = $this->createAdminPanelUser('petugas');
        $wajibPajak = $this->createApprovedWajibPajak();
        $jenisPajak = JenisPajak::where('kode', '41101')->firstOrFail();
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->firstOrFail();

        $taxObject = TaxObject::create([
            'nik' => $wajibPajak->nik,
            'nik_hash' => WajibPajak::generateHash($wajibPajak->nik),
            'nama_objek_pajak' => 'Objek Hotel Duplikat',
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

        Tax::create([
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'tax_object_id' => $taxObject->id,
            'user_id' => $wajibPajak->user_id,
            'amount' => 125000,
            'omzet' => 1250000,
            'tarif_persentase' => 10,
            'status' => TaxStatus::Pending,
            'billing_code' => '352210100000260001',
            'payment_channel' => 'QRIS',
            'payment_expired_at' => now()->addDays(7),
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2026,
            'pembetulan_ke' => 0,
            'billing_sequence' => 1,
        ]);

        $periodLabel = Carbon::create(2026, 3, 1)->translatedFormat('F Y');

        $this->actingAs($petugas);

        Livewire::test(BuatBillingSelfAssessment::class)
            ->set('selectedTaxObjectId', $taxObject->id)
            ->set('selectedTaxObjectData', [
                'id' => $taxObject->id,
                'nama' => $taxObject->nama_objek_pajak,
                'alamat' => $taxObject->alamat_objek,
                'npwpd' => $taxObject->npwpd,
                'nopd' => $taxObject->nopd,
                'nik_hash' => $taxObject->nik_hash,
                'sub_jenis' => $subJenisPajak->nama,
                'jenis_pajak_nama' => $jenisPajak->nama,
                'tarif_persen' => 10,
                'jenis_pajak_id' => $jenisPajak->id,
                'sub_jenis_pajak_id' => $subJenisPajak->id,
                'next_bulan' => 3,
                'next_tahun' => 2026,
                'next_label' => $periodLabel,
                'is_new' => false,
                'is_opd' => false,
                'is_insidentil' => false,
                'sub_jenis_kode' => $subJenisPajak->kode,
                'is_multi_billing' => false,
            ])
            ->set('wajibPajakData', [
                'id' => $wajibPajak->id,
                'user_id' => $wajibPajak->user_id,
                'nama_lengkap' => $wajibPajak->nama_lengkap,
                'npwpd' => $wajibPajak->npwpd,
                'tipe' => $wajibPajak->tipe_wajib_pajak,
            ])
            ->set('masaPajakBulan', 3)
            ->set('masaPajakTahun', 2026)
            ->set('omzet', 1500000)
            ->call('terbitkanBilling')
            ->assertSet('showDuplicateConfirm', true)
            ->assertSet('existingBillingInfo.period_label', $periodLabel)
            ->assertSet('existingBillingInfo.amount_label', 'Rp 125.000')
                ->assertSee('Billing Sumber')
            ->assertSee($periodLabel)
            ->assertSee('Rp 125.000');
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
            'nama_lengkap' => 'Wajib Pajak Uji',
            'alamat' => 'Jl. Veteran No. 12',
            'role' => 'wajibPajak',
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        return WajibPajak::create([
            'user_id' => $user->id,
            'nik' => $nik,
            'nik_hash' => WajibPajak::generateHash($nik),
            'nama_lengkap' => 'Wajib Pajak Uji',
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
}