<?php

namespace App\Domain\Shared\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Models\Notification as AppNotification;
use Filament\Notifications\Notification as FilamentNotification;

class NotificationService
{
    /**
     * Kirim notifikasi ke user via app_notifications table (portal).
     */
    public static function notifyUser(
        User $user,
        string $title,
        string $body,
        string $type = 'info',
        ?array $data = null
    ): void {
        AppNotification::send($user->id, $title, $body, $type, $data);
    }

    /**
     * Kirim notifikasi ke semua user dengan role tertentu via Filament database notifications.
     * Muncul di bell icon admin panel Filament.
     */
    public static function notifyRole(
        string|array $roles,
        string $title,
        string $body,
        ?array $data = null
    ): void {
        $roles = is_array($roles) ? $roles : [$roles];

        $users = User::whereIn('role', $roles)
            ->whereNull('deleted_at')
            ->get();

        foreach ($users as $user) {
            FilamentNotification::make()
                ->title($title)
                ->body($body)
                ->sendToDatabase($user);
        }
    }

    /**
     * Kirim notifikasi ke user via KEDUA sistem:
     * 1. app_notifications (untuk portal API)
     * 2. Filament database notifications (untuk bell icon Filament jika user punya akses)
     */
    public static function notifyUserBoth(
        User $user,
        string $title,
        string $body,
        string $type = 'info',
        ?array $data = null
    ): void {
        // Portal notification
        AppNotification::send($user->id, $title, $body, $type, $data);

        // Filament database notification
        FilamentNotification::make()
            ->title($title)
            ->body($body)
            ->sendToDatabase($user);
    }
}
