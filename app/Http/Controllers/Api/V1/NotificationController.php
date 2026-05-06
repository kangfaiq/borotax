<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Shared\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    private function transformNotification(Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'body' => $notification->body,
            'type' => $notification->type,
            'is_read' => (bool) $notification->is_read,
            'created_at' => $notification->created_at?->toIso8601String(),
            'updated_at' => $notification->updated_at?->toIso8601String(),
            'data_payload' => $notification->data_payload,
            'url' => $notification->url,
            'action_url' => $notification->url,
        ];
    }

    /**
     * List notifikasi user (paginated).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $notifications->setCollection(
            $notifications->getCollection()->map(fn (Notification $notification) => $this->transformNotification($notification))
        );

        return $this->sendResponse($notifications, 'Daftar notifikasi.');
    }

    /**
     * Jumlah notifikasi yang belum dibaca.
     */
    public function unreadCount(Request $request)
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->unread()
            ->count();

        return $this->sendResponse(['unread_count' => $count], 'Jumlah notifikasi belum dibaca.');
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return $this->sendError('Notifikasi tidak ditemukan.', [], 404);
        }

        $notification->markAsRead();

        return $this->sendResponse($this->transformNotification($notification->fresh()), 'Notifikasi ditandai sudah dibaca.');
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->unread()
            ->update(['is_read' => true]);

        return $this->sendResponse(null, 'Semua notifikasi ditandai sudah dibaca.');
    }
}
