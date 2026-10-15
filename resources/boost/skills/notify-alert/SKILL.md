---
name: laragear-alerts-notify-alert
description: "Deliver Laravel Notifications as in-app frontend alerts via the AlertChannel during a web request. Exclude queued or background notifications, non-web channels like mail or database, and manual session alerts (use alerts-push for those)."
---

# Notify via Alerts

Use when a Laravel Notification should surface in the UI as an alert.

## Channel + formatter

Add `AlertChannel::class` in `via()` and implement `toAlert()` returning an alert built with `make()`:

```php
use Laragear\Alerts\AlertChannel;
use App\Alerts\MyAlert;

public function via(object $notifiable): array
{
    return [AlertChannel::class, 'mail'];
}

public function toAlert(object $notifiable): MyAlert
{
    return MyAlert::make()
        ->set('type', 'success')
        ->set('body', 'Your report is ready!');
}
```

Always use `make()` here, never `push()`: the channel bags the alert itself, so calling `push()` would add it to the bag twice.

## Request-bound guardrail

`AlertChannel` only works within the same web request/session, because alerts live in the session bag. For notifications sent from a queued job or background process, there is no shared session for the alert to attach to — use a durable channel instead, or persist the alert (`persistAs()`) from a synchronously executed context so it survives until the user's next request.
