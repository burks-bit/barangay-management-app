<?php

namespace App\Services;

use App\Models\User;

class NotificationService
{
    public function markAsRead(User $user, string $notificationId): void
    {
        $user->notifications()->findOrFail($notificationId)->markAsRead();
    }
}