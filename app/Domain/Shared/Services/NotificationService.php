<?php

namespace App\Domain\Shared\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Models\Notification as AppNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;

class NotificationService
{
    /**
     * Kirim notifikasi ke user via app_notifications table (portal).
     * Jika $actionUrl diisi, akan disimpan di data_payload['url'] agar bisa diklik
     * dari list notifikasi portal.
     */
    public static function notifyUser(
        User $user,
        string $title,
        string $body,
        string $type = 'info',
        ?array $data = null,
        ?string $actionUrl = null,
    ): void {
        $payload = self::mergeUrl($data, $actionUrl);
        AppNotification::send($user->id, $title, $body, $type, $payload);
    }

    /**
     * Kirim notifikasi ke semua user dengan role tertentu via Filament database notifications.
     * Muncul di bell icon admin panel Filament. Jika $actionUrl diisi (atau $data['url']),
     * action button "Lihat" otomatis ditambahkan.
     */
    public static function notifyRole(
        string|array $roles,
        string $title,
        string $body,
        ?array $data = null,
        ?string $actionLabel = null,
        ?string $actionUrl = null,
    ): void {
        $roles = is_array($roles) ? $roles : [$roles];

        $url = $actionUrl ?? ($data['url'] ?? null);
        $label = $actionLabel ?? 'Lihat';

        $users = User::whereIn('role', $roles)
            ->whereNull('deleted_at')
            ->get();

        foreach ($users as $user) {
            $notification = FilamentNotification::make()
                ->title($title)
                ->body($body);

            if (filled($url)) {
                $notification->actions([
                    Action::make('view')
                        ->label($label)
                        ->button()
                        ->url($url),
                ]);
            }

            $notification->sendToDatabase($user);
        }
    }

    /**
     * Kirim notifikasi ke user via KEDUA sistem:
     * 1. app_notifications (untuk portal API) — url disimpan di data_payload['url']
     * 2. Filament database notifications (bell icon Filament) — url menjadi action "Lihat"
     */
    public static function notifyUserBoth(
        User $user,
        string $title,
        string $body,
        string $type = 'info',
        ?array $data = null,
        ?string $actionUrl = null,
    ): void {
        $payload = self::mergeUrl($data, $actionUrl);
        $url = $payload['url'] ?? null;

        AppNotification::send($user->id, $title, $body, $type, $payload);

        $notification = FilamentNotification::make()
            ->title($title)
            ->body($body);

        if (filled($url)) {
            $notification->actions([
                Action::make('view')
                    ->label('Lihat')
                    ->button()
                    ->url($url),
            ]);
        }

        $notification->sendToDatabase($user);
    }

    private static function mergeUrl(?array $data, ?string $actionUrl): ?array
    {
        if (filled($actionUrl)) {
            $data = ($data ?? []) + ['url' => $actionUrl];
        }

        return $data;
    }
}
