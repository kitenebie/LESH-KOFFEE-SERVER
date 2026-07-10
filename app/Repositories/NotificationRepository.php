<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Repositories\Interfaces\NotificationRepositoryInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function getByUser(int $userId)
    {
        return Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function markAsRead(int $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_unread' => false]);

        return $notification;
    }
}
