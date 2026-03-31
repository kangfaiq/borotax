<?php

namespace App\Domain\Shared\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'data_payload',
        'is_read',
    ];

    protected $casts = [
        'data_payload' => 'array',
        'is_read' => 'boolean',
    ];

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope untuk unread
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope untuk type tertentu
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Send notification to user
     */
    public static function send(
        string $userId,
        string $title,
        string $body,
        string $type = 'info',
        ?array $dataPayload = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'data_payload' => $dataPayload,
            'is_read' => false,
        ]);
    }

    /**
     * Broadcast notification to all users
     */
    public static function broadcast(
        string $title,
        string $body,
        string $type = 'info',
        ?array $dataPayload = null
    ): int {
        $users = User::where('role', 'wajibPajak')->pluck('id');
        $count = 0;

        foreach ($users as $userId) {
            self::send($userId, $title, $body, $type, $dataPayload);
            $count++;
        }

        return $count;
    }
}
