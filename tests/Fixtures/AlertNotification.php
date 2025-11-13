<?php

namespace Tests\Fixtures;

use Illuminate\Notifications\Notification;

class AlertNotification extends Notification
{
    public static $return = null;

    public function toAlert(object $notifiable)
    {
        return static::$return;
    }
}
