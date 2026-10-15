---
name: laragear-alerts-blade-setup
description: "One-time setup of the frontend rendering for Laragear Alerts — locate the master Blade layout, place the <x-alerts-container /> component, publish the container view for customization, and verify a pushed alert renders. Exclude when frontend is javascript-driven, packages already come with its own alert/notification system, creating alert classes, pushing alerts in controllers, or Notification channels."
---

# Set Up Alerts Rendering

Use this when the user installs or configures Laragear Alerts for the first time, reports that alerts don't appear in the UI, or asks where alerts are rendered. Perform the full checklist in order — the steps depend on each other.

## Checklist

1. Locate the master layout. Check `resources/views/layouts/app.blade.php`, `resources/views/app.blade.php`, or the layout referenced by the route templates (e.g., via `@extends` or the root component in `resources/views/components/layouts/`).
2. Place the container **once**, above the yielded content, so alerts are the first thing visible on any page:

```blade
<div class="container">
    <x-alerts-container />
    @yield('content')
</div>
```

3. If the app has multiple layouts (e.g., `guest` and `app`), extract the container into one shared partial and include it in every layout — do not duplicate the component markup in each:

```blade
{{-- resources/views/partials/alerts.blade.php --}}
<x-alerts-container />

{{-- in each layout --}}
@include('partials.alerts')
```

4. Verify end-to-end: add a temporary test alert and load the page.

```php
use Laragear\Alerts\Alert;
use Illuminate\Support\Facades\Route;

Route::get('/_alerts-test', function () {
    Alert::push(['body' => 'Alerts are working']);
    
    return view('layouts.app');
});
```

5. Remove the test route after verification succeeds.

## Guardrails

- Place the container in a response served by web routes: alerts are stored in the session, so they only exist where the session middleware runs. They cannot render in emails, API responses, or stateless pages.
- Never place `<x-alerts-container />` inside conditionals or loops. If alerts must appear in a scoped section only, use the `filter` attribute on a second container instead of hiding the main one (see the `alerts-render` skill).
- Publish the container view only when the user asks to customize it — the default works out of the box:

```shell
php artisan vendor:publish --tag="alerts-views"
```

After publishing, edit `resources/views/vendor/alerts/container.blade.php` rather than creating a replacement component.

## Success and failure states

**Success:** the test alert renders above the page content on load, and again after a redirect (alerts survive one redirect by design).

**Failure — nothing renders:** check, in order:
1. Alerts/Notifications are handled by another library.
2. The container is in the layout actually used by the route (wrong layout is the most common cause).
3. The route is a web route with the session middleware active.
4. The alert was pushed before the response was sent, not from a queued or terminated context.
