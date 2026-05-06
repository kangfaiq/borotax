<?php

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves the notification owner relation to the auth user model', function () {
    $wajibPajak = $this->createApprovedWajibPajakFixture([], [
        'email' => 'notif-owner@example.test',
    ]);

    $notification = Notification::send(
        $wajibPajak->user->id,
        'Billing terbit',
        'Billing Anda sudah terbit.',
        'info'
    );

    expect($notification->user)
        ->toBeInstanceOf(User::class)
        ->and($notification->user->is($wajibPajak->user))->toBeTrue();
});

it('returns only portal user notifications from the portal endpoint', function () {
    $owner = $this->createApprovedWajibPajakFixture([], [
        'email' => 'notif-list-owner@example.test',
    ]);

    Notification::query()->forceDelete();

    $other = $this->createApprovedWajibPajakFixture([], [
        'email' => 'notif-list-other@example.test',
    ]);

    $olderNotification = Notification::send(
        $owner->user->id,
        'Notifikasi lama',
        'Milik owner yang lama.',
        'info',
        ['url' => route('portal.dashboard')],
    );
    $latestNotification = Notification::send(
        $owner->user->id,
        'Notifikasi terbaru',
        'Milik owner yang terbaru.',
        'payment',
        ['url' => route('portal.billing')],
    );
    Notification::send($other->user->id, 'Notifikasi orang lain', 'Harus tersembunyi.', 'info');

    $response = $this->actingAs($owner->user)
        ->getJson(route('portal.notifications.index'));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Daftar notifikasi.')
        ->assertJsonCount(2, 'data.data');

    $notificationIds = collect($response->json('data.data'))->pluck('id');
    $latestPayload = collect($response->json('data.data'))->firstWhere('id', $latestNotification->id);

    expect($notificationIds->all())
        ->toMatchArray([$olderNotification->id, $latestNotification->id])
        ->and($notificationIds)->not->toContain(
            Notification::where('user_id', $other->user->id)->value('id')
        )
        ->and($latestPayload['url'] ?? null)->toBe(route('portal.billing'))
        ->and($latestPayload['action_url'] ?? null)->toBe(route('portal.billing'));
});

it('marks only the portal user notifications as read and rejects foreign notifications', function () {
    $owner = $this->createApprovedWajibPajakFixture([], [
        'email' => 'notif-read-owner@example.test',
    ]);

    Notification::query()->forceDelete();
    $other = $this->createApprovedWajibPajakFixture([], [
        'email' => 'notif-read-other@example.test',
    ]);
    $olderNotification = Notification::send($owner->user->id, 'Notifikasi lama', 'Milik owner yang lama.', 'info');
    $olderNotification->forceFill([
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ])->saveQuietly();
    $ownerFirstNotification = Notification::send($owner->user->id, 'Pertama', 'Unread pertama.', 'info');
    $ownerSecondNotification = Notification::send($owner->user->id, 'Kedua', 'Unread kedua.', 'info');
    $otherNotification = Notification::send($other->user->id, 'Orang lain', 'Harus ditolak.', 'info');

    $initialUnreadCount = $this->actingAs($owner->user)
        ->getJson(route('portal.notifications.unread-count'))
        ->assertOk()
        ->json('data.unread_count');

    expect($initialUnreadCount)->toBeGreaterThanOrEqual(2);

    $this->actingAs($owner->user)
        ->postJson(route('portal.notifications.read', $ownerFirstNotification->id))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $ownerFirstNotification->id)
        ->assertJsonPath('data.is_read', true);

    expect($ownerFirstNotification->fresh()->is_read)->toBeTrue();
    expect($ownerSecondNotification->fresh()->is_read)->toBeFalse();

    $this->actingAs($owner->user)
        ->getJson(route('portal.notifications.unread-count'))
        ->assertOk()
        ->assertJsonPath('data.unread_count', $initialUnreadCount - 1);

    $this->actingAs($owner->user)
        ->postJson(route('portal.notifications.read', $otherNotification->id))
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Notifikasi tidak ditemukan.');

    $this->actingAs($owner->user)
        ->postJson(route('portal.notifications.read-all'))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Semua notifikasi ditandai sudah dibaca.');

    expect($ownerSecondNotification->fresh()->is_read)->toBeTrue();
    expect($otherNotification->fresh()->is_read)->toBeFalse();

    $this->actingAs($owner->user)
        ->getJson(route('portal.notifications.unread-count'))
        ->assertOk()
        ->assertJsonPath('data.unread_count', 0);
});

it('renders the portal notification dropdown hooks in the shared layout', function () {
    $wajibPajak = $this->createApprovedWajibPajakFixture([], [
        'email' => 'notif-layout@example.test',
        'password_changed_at' => now(),
        'must_change_password' => false,
    ]);

    $this->actingAs($wajibPajak->user)
        ->get(route('portal.billing'))
        ->assertOk()
        ->assertSee('id="notifBtn"', false)
        ->assertSee('id="notifDropdown"', false)
        ->assertSee("const notifApiBase = '/portal/notifications';", false)
        ->assertSee('function loadNotifications()', false)
        ->assertSee('function loadUnreadCount()', false)
        ->assertSee('function markAllRead()', false)
        ->assertSee("const targetUrl = n.url || n.action_url || n.data_payload?.url || ''", false);
});