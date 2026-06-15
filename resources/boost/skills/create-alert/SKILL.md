---
name: create-alert
description: Create and customize Alerts in the Laragear Alerts package.
---

# Create Alert

## When to use this skill

Use this skill when you need to create a new type of alert, add custom methods to an alert class, set default values, handle localization within alerts, or customize how an alert is rendered to HTML.

## Features

### Create via Artisan

Generate a new alert class in `app/Views/Alerts` and its corresponding view in `resources/views/alerts`.

```shell
php artisan alert:create MyAlert
```

### Custom Methods

Add methods to the alert class to fluently set attributes using $this->set('key', $value).

```php
public function type(string $type): static
{
    return $this->set('type', $type);
}
```

### Default Values

Define defaults for the alert by overriding the `getDefaults()` method.

```php
protected function getDefaults(): array
{
    return [
        'level' => 'info',
        'dismissible' => true,
    ];
}
```

### Localization

Handle translation within alert methods to keep the calling code clean.

```php
public function message(string $key, array $replace = []): static
{
    return $this->set('message', trans($key, $replace));
}
```

### HTML Rendering

The `toHtml()` method determines how the alert is rendered. It should return a view with the alert's attributes.

```php
public function toHtml(): mixed
{
    return view('alerts.my-alert', $this->all());
}
```
