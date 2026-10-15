---
name: laragear-alerts-render-alert
description: "Display pushed alerts in Blade layouts with the container component, filter which alerts render in a section, or publish and customize the container view. Exclude creating or pushing alerts (use alerts-create, alerts-push)."
---

# Render Alerts

Use in Blade layouts where pushed alerts must become visible HTML.

## Container placement

Place `<x-alerts-container />` exactly once in the master layout, above the yielded content — all pushed alerts render there automatically:

```blade
<div class="container">
    <x-alerts-container />
    @yield('content')
</div>
```

## Scoped sections

Use the `filter` attribute with fully-qualified class names, comma-separated, when a section must show only specific alerts:

```blade
<x-alerts-container filter="\App\Alerts\ErrorAlert,\App\Alerts\WarningAlert" />
```

## Customizing the container

Publish and edit the container view (`laragear::alerts.container`) instead of building a replacement component:

```shell
php artisan vendor:publish --tag="alerts-views"
```
