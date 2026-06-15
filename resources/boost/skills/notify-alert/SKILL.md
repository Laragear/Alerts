---
name: notify-alert
description: Use Laragear Alerts as a channel for Laravel Notifications.
---

# Notify Alert

## When to use this skill

Use this skill when you want Laravel Notifications to be delivered as alerts in your application's frontend.

## Features

### Alert Channel

Add `Laragear\Alerts\AlertChannel::class` to the `via()` method of your notification.

```php
use Laragear\Alerts\AlertChannel;

public function via(object $notifiable): array
{
    return [AlertChannel::class, 'mail'];
}
```

### The toAlert() Method

Implement the `toAlert()` method to return an `Alert` instance. It's recommended to use `make()` instead of `push()` here.

```php
use App\Alerts\MyAlert;
use Laragear\Alerts\Alert;

public function toAlert(object $notifiable): Alert
{
    return MyAlert::make()
        ->set('type', 'success')
        ->set('body', 'Your report is ready!');
}
```

### Lifecycle Constraint

The `AlertChannel` only works during a web request. If the notification is sent via a background queue that doesn't share the same session/request context, the alert will not be visible to the user unless they are using a persistent storage or broadcasting.
