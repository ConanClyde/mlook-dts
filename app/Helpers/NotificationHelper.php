<?php

namespace App\Helpers;

use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Models\User;

class NotificationHelper
{
    /**
     * Notify all admins (excluding the actor if provided).
     */
    public static function notifyAdmins(
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        ?array $data = null,
        ?int $excludeUserId = null
    ): void {
        $query = User::where('usertype', 'admin');

        // Exclude the actor if provided
        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        $admins = $query->get();

        \Log::info('NotificationHelper: Notifying admins', [
            'type' => $type,
            'admin_count' => $admins->count(),
            'exclude_user_id' => $excludeUserId,
        ]);

        foreach ($admins as $admin) {
            $notification = Notification::create([
                'user_id' => $admin->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'data' => $data,
                'read' => false,
            ]);

            \Log::info('NotificationHelper: Broadcasting notification', [
                'notification_id' => $notification->id,
                'admin_id' => $admin->id,
                'type' => $type,
            ]);

            // Broadcast the notification
            event(new NotificationCreated($notification));
        }

        \Log::info('NotificationHelper: All notifications dispatched');
    }
}
