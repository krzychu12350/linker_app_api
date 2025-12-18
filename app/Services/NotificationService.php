<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Helpers\Pusher;
use App\Models\User;

readonly class NotificationService
{
    public function __construct(private Pusher $pusher)
    {
    }

    public function addNotification(
        User   $user,
        string $notificationMessage,
        bool   $broadcasting = true,
    ): void
    {
        $notification = $user->notifications()->create([
            'message' => $notificationMessage,
            'type' => NotificationType::MATCH,
        ]);

        if ($broadcasting) {
            $this->pusher->triggerAsync(
                'notifications.user.' . $user->id,
                'notification.added',
                $notification->toArray()
            );
        }
    }
}