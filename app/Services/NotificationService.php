<?php

namespace App\Services;

use App\Models\User;

class NotificationService extends Service
{
    public function markAsRead(User $user, string $notificationId): void
    {
        $this->attempt(function () use ($user, $notificationId) {
            $user->notifications()->findOrFail($notificationId)->markAsRead();
        }, 'Failed to mark notification as read.');
    }
}