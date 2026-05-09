<?php

namespace Modules\SystemNotification\Listeners;

use Illuminate\Notifications\Events\NotificationSent;

class ModularousNotificationSentListener
{
    public function handle(NotificationSent $event)
    {
        // $event->channel
        // $event->notifiable
        // $event->notification
        // $event->response
        $notification = $event->notification;
        $notificationClass = get_class($notification);

        if (str_starts_with($notificationClass, 'Modules\\') || str_starts_with($notificationClass, 'Unusualify\\Modularous\\')) {
            if (method_exists($notification, 'afterNotificationSent')) {
                $notification->afterNotificationSent($event->notifiable);
            }
        }

    }
}
