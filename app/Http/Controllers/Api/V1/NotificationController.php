<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Shared\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    /**
     * List notifikasi user (paginated).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

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

        return $this->sendResponse($notification, 'Notifikasi ditandai sudah dibaca.');
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
