---
name: laragear-alerts-create-alert
description: "Generate new alert classes with Artisan, add fluent setter methods, define defaults, handle translations, or customize HTML rendering of an alert. Exclude pushing alerts to the session, rendering alerts in views, or sending alerts as Notifications (use laragear-alerts-push-alert, laragear-alerts-render-alert, laragear-alerts-notify-alert)."
---

# Create Alerts

Use when defining a new alert type, or changing how an existing alert class builds or renders its content.

## Generate the class

1. Run `php artisan alert:create MyAlert`.
2. This creates the class in `app/Views/Alerts/MyAlert.php` and its view in `resources/views/alerts/my-alert.blade.php`.

## Fluent setters

Expose attributes as fluent methods that call `$this->set()`. Always wrap translations inside a method, so call sites stay clean:

```php
public function type(string $type): static
{
    return $this->set('type', $type);
}

public function message(string $key, array $replace = []): static
{
    return $this->set('body', trans($key, $replace));
}
```

## Defaults

Override `getDefaults()` so every instance starts consistent:

```php
protected function getDefaults(): array
{
    return [
        'level' => 'info',
        'dismissible' => true,
    ];
}
```

## Rendering

`toHtml()` must always return the view with all attributes passed through — no manual HTML strings inside the class:

```php
public function toHtml(): mixed
{
    return view('alerts.my-alert', $this->all());
}
```

**Success check:** the alert renders without missing-variable errors when pushed and displayed in a Blade view containing `<x-alerts-container />`.
