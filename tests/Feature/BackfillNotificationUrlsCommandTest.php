<?php

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('backfills data payload url for known legacy notification titles', function () {
    $user = User::create([
        'name' => 'Portal User',
        'nama_lengkap' => 'Portal User',
        'email' => 'portal-' . Str::random(8) . '@example.test',
        'password' => Hash::make('password'),
        'role' => 'user',
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);

    $notification = Notification::send(
        $user->id,
        'Pembetulan Billing Ditolak',
        'Body lama',
        'verification',
    );

    $this->artisan('notifications:backfill-urls')
        ->expectsOutputToContain('1 notifikasi diupdate')
        ->assertSuccessful();

    expect($notification->fresh()->data_payload['url'] ?? null)
        ->toBe(route('portal.pembetulan.index'));
});

it('supports dry run without persisting changes', function () {
    $user = User::create([
        'name' => 'Portal User',
        'nama_lengkap' => 'Portal User',
        'email' => 'portal-dry-' . Str::random(8) . '@example.test',
        'password' => Hash::make('password'),
        'role' => 'user',
        'status' => 'verified',
        'email_verified_at' => now(),
        'navigation_mode' => 'topbar',
    ]);

    $notification = Notification::send(
        $user->id,
        'Pembetulan Billing Ditolak',
        'Body lama',
        'verification',
    );

    $this->artisan('notifications:backfill-urls --dry-run')
        ->expectsOutputToContain('1 notifikasi akan diupdate')
        ->assertSuccessful();

    expect($notification->fresh()->data_payload['url'] ?? null)->toBeNull();
});
