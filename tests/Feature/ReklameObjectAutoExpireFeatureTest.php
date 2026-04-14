<?php

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\ReklameObject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    test()->seedReklameTaxReferences();
});

it('auto expires stale reklame objects before the mobile active-object list is returned', function () {
    $user = createPortalReklameUser('mobile-reklame-auto-expire@example.test', '3522011234567821');
    $activeObject = createReklameObjectForStatusSync($user, now()->addDays(10));
    $expiredObject = createReklameObjectForStatusSync($user, now()->subDay(), 'Objek Kedaluwarsa');

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/reklame-objects');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment([
            'id' => $activeObject->id,
            'nama_objek_pajak' => 'Billboard Simpang Lima',
        ]);

    expect(collect($response->json('data'))->pluck('id'))->not->toContain($expiredObject->id);
    expect($activeObject->fresh()->status)->toBe('aktif');
    expect($expiredObject->fresh()->status)->toBe('kadaluarsa');
});

it('syncs expired reklame objects before portal reklame summary counts are calculated', function () {
    $user = createPortalReklameUser('portal-reklame-auto-expire@example.test', '3522011234567822', 'wajibPajak');

    createReklameObjectForStatusSync($user, now()->addDays(5), 'Objek Aktif Portal');
    createReklameObjectForStatusSync($user, now()->subDay(), 'Objek Kedaluwarsa Portal');

    $this->actingAs($user)
        ->get(route('portal.reklame.index'))
        ->assertOk()
        ->assertViewHas('objekAktif', 1)
        ->assertViewHas('objekKadaluarsa', 1);

    expect(ReklameObject::query()->where('status', 'kadaluarsa')->count())->toBe(1);
});

function createPortalReklameUser(string $email, string $nik, string $role = 'user'): User
{
    return User::create([
        'name' => 'Reklame Auto Expire User',
        'nama_lengkap' => 'Reklame Auto Expire User',
        'email' => $email,
        'password' => Hash::make('password'),
        'nik' => $nik,
        'nik_hash' => User::generateHash($nik),
        'alamat' => 'Jl. Panglima Sudirman No. 1',
        'role' => $role,
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
        'password_changed_at' => now(),
        'must_change_password' => false,
    ]);
}

function createReklameObjectForStatusSync(User $user, $masaBerlakuSampai, string $nama = 'Billboard Simpang Lima'): ReklameObject
{
    $jenisPajak = JenisPajak::where('kode', '41104')->firstOrFail();
    $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)
        ->where('is_active', true)
        ->orderBy('urutan')
        ->firstOrFail();

    return ReklameObject::create([
        'nik' => $user->nik,
        'nik_hash' => ReklameObject::generateHash($user->nik),
        'nama_objek_pajak' => $nama,
        'jenis_pajak_id' => $jenisPajak->id,
        'sub_jenis_pajak_id' => $subJenisPajak->id,
        'npwpd' => 'P10000001099',
        'nopd' => random_int(1000, 9999),
        'alamat_objek' => 'Jl. Ahmad Yani No. 10',
        'kelurahan' => 'Kadipaten',
        'kecamatan' => 'Bojonegoro',
        'panjang' => 4,
        'lebar' => 6,
        'bentuk' => 'persegi',
        'jumlah_muka' => 1,
        'status' => 'aktif',
        'tarif_persen' => 25,
        'tanggal_daftar' => now()->subMonths(6),
        'tanggal_pasang' => now()->subMonths(6),
        'masa_berlaku_sampai' => $masaBerlakuSampai,
        'is_active' => true,
    ]);
}