---
name: render-alert
description: Render alerts in Blade views using the container component.
---

# Render Alert

## When to use this skill

Use this skill when you need to display pushed alerts in your frontend using Blade, or when you need to filter which alerts are displayed in a specific part of your layout.

## Features

### Alerts Container

Place the `<x-alerts-container />` component in your Blade layout (usually in the master template or above the main content) to render all pushed alerts.

```blade
<div class="container">
    <x-alerts-container />
    @yield('content')
</div>
```

### Filtering by Class

Use the `filter` attribute to only show specific alert classes. Multiple classes can be separated by commas.

```blade
<!-- Only show specific alerts -->
<x-alerts-container filter="\App\Alerts\ImportantAlert" />

<!-- Show multiple specific alerts -->
<x-alerts-container filter="\App\Alerts\ErrorAlert,\App\Alerts\WarningAlert" />
```

### Customizing the Container

The container view is located at `laragear::alerts.container`. You can publish and customize it if needed.

```shell
php artisan vendor:publish --tag="alerts-views"
```
