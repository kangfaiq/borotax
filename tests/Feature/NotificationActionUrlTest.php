<?php

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Models\Notification as AppNotification;
use App\Domain\Shared\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('persists url in portal payload and filament action when notify user both is used', function () {
    $user = makeNotificationUrlUser('user');

    NotificationService::notifyUserBoth(
        $user,
        'Pembetulan Selesai',
        'Cek riwayat',
        'payment',
        actionUrl: '/portal/riwayat',
    );

    $portal = AppNotification::where('user_id', $user->id)->first();

    expect($portal)->not->toBeNull();
    expect($portal->data_payload['url'] ?? null)->toBe('/portal/riwayat');
    expect($portal->url)->toBe('/portal/riwayat');

    $filament = $user->fresh()->notifications()->first();
    $actions = $filament?->data['actions'] ?? [];

    expect($filament)->not->toBeNull();
    expect($actions)->not->toBeEmpty();
    expect($actions[0]['url'] ?? null)->toBe('/portal/riwayat');
    expect($actions[0]['label'] ?? null)->toBe('Lihat');
    expect($actions[0]['shouldMarkAsRead'] ?? null)->toBeTrue();
});

it('attaches action button when notify role receives a url', function () {
    $admin = makeNotificationUrlUser('admin');

    NotificationService::notifyRole(
        'admin',
        'Pengajuan Baru',
        'Mohon diverifikasi',
        actionUrl: '/admin/pengajuan/123',
    );

    $notif = $admin->fresh()->notifications()->first();
    $actions = $notif?->data['actions'] ?? [];

    expect($notif)->not->toBeNull();
    expect($actions)->not->toBeEmpty();
    expect($actions[0]['url'] ?? null)->toBe('/admin/pengajuan/123');
    expect($actions[0]['shouldMarkAsRead'] ?? null)->toBeTrue();
});

it('does not attach action button when notify role has no url', function () {
    $admin = makeNotificationUrlUser('admin');

    NotificationService::notifyRole(
        'admin',
        'Notifikasi tanpa URL',
        'Body',
    );

    $notif = $admin->fresh()->notifications()->first();

    expect($notif)->not->toBeNull();
    expect($notif->data['actions'] ?? [])->toBeEmpty();
});

it('returns url field from the portal notification api', function () {
    $user = makeNotificationUrlUser('user');

    AppNotification::send($user->id, 'Test', 'Body', 'info', ['url' => '/portal/x']);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/v1/notifications');
    $data = $response->json('data.data') ?? $response->json('data');

    $response->assertOk();
    expect($data)->not->toBeEmpty();
    expect($data[0]['url'] ?? null)->toBe('/portal/x');
});

function makeNotificationUrlUser(string $role): User
{
    return User::create([
        'name' => Str::headline($role),
        'nama_lengkap' => Str::headline($role) . ' User',
        'email' => sprintf('%s-%s@example.test', $role, Str::random(8)),
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);
}
