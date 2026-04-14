<?php

use App\Domain\Auth\Models\User;
use App\Filament\Pages\BuatBillingMblb;
use App\Filament\Pages\BuatBillingSarangWalet;
use App\Filament\Pages\BuatBillingSelfAssessment;
use App\Filament\Pages\BuatSkpdAirTanah;
use App\Filament\Pages\BuatSkpdReklame;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\LaporanPendapatan;
use App\Filament\Resources\TaxObjectResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('shows the expanded quick actions for admin users', function () {
    $this->actingAs(createAdminPanelUserForDashboard('admin'));

    $response = $this->get(Dashboard::getUrl());

    $response->assertOk();
    $response->assertSee('Aksi Cepat');
    $response->assertSee(LaporanPendapatan::getUrl(), false);
    $response->assertSee('/admin/laporan-pendapatan', false);
    $response->assertSee(TaxObjectResource::getUrl('index'), false);
    $response->assertSee(BuatBillingSelfAssessment::getUrl(), false);
    $response->assertSee(BuatBillingMblb::getUrl(), false);
    $response->assertSee(BuatBillingSarangWalet::getUrl(), false);
    $response->assertSee(BuatSkpdReklame::getUrl(), false);
    $response->assertSee(BuatSkpdAirTanah::getUrl(), false);
    $response->assertSee('Objek Pajak');
    $response->assertSee('Billing Self Assessment');
    $response->assertSee('Billing MBLB');
    $response->assertSee('Billing Sarang Walet');
    $response->assertSee('SKPD Reklame');
    $response->assertSee('SKPD Air Tanah');
});

it('hides admin and petugas only quick actions from verifikator users', function () {
    $this->actingAs(createAdminPanelUserForDashboard('verifikator'));

    $response = $this->get(Dashboard::getUrl());

    $response->assertOk();
    $response->assertSee(LaporanPendapatan::getUrl(), false);
    $response->assertSee(BuatSkpdReklame::getUrl(), false);
    $response->assertSee(BuatSkpdAirTanah::getUrl(), false);
    $response->assertDontSee(TaxObjectResource::getUrl('index'), false);
    $response->assertDontSee(BuatBillingSelfAssessment::getUrl(), false);
    $response->assertDontSee(BuatBillingMblb::getUrl(), false);
    $response->assertDontSee(BuatBillingSarangWalet::getUrl(), false);
});

function createAdminPanelUserForDashboard(string $role): User
{
    return User::create([
        'name' => Str::headline($role) . ' Dashboard User',
        'nama_lengkap' => Str::headline($role) . ' Dashboard User',
        'email' => sprintf('%s-dashboard-%s@example.test', $role, Str::lower(Str::random(8))),
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => 'verified',
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'must_change_password' => false,
        'navigation_mode' => 'topbar',
    ]);
}