## Laragear Alerts

This package allows setting multiple alerts from the backend and rendering them in the frontend using any HTML.

### Features

- **Creating Alerts**: Use the Artisan command to generate alert classes and views.
@verbatim
<code-snippet name="Create an Alert" lang="shell">
php artisan alert:create FluxCallout
</code-snippet>
@endverbatim

- **Pushing Alerts**: Use static methods to push alerts to the bag for the current or next request (if redirecting).
@verbatim
<code-snippet name="Push an Alert" lang="php">
use App\Alerts\FluxCallout;

FluxCallout::push(['body' => 'Success!']);
</code-snippet>
@endverbatim

- **Conditional Pushing**: Push alerts only when a condition is met.
@verbatim
<code-snippet name="Conditional Push" lang="php">
FluxCallout::pushWhen($user->isAdmin(), ['body' => 'Welcome, Admin!']);
</code-snippet>
@endverbatim

- **Persistence**: Make alerts last between requests until explicitly abandoned.
@verbatim
<code-snippet name="Persistent Alert" lang="php">
FluxCallout::push(['title' => 'Disk full'])->persistAs('disk.full');

// Later
FluxCallout::abandon('disk.full');
</code-snippet>
@endverbatim

- **Rendering**: Use the Blade component to render all pushed alerts.
@verbatim
<code-snippet name="Render Alerts" lang="blade">
<x-alerts-container />
</code-snippet>
@endverbatim

- **Notifications**: Integrate alerts with Laravel's notification system.
@verbatim
<code-snippet name="Alert Notification" lang="php">
public function via($notifiable) {
    return \Laragear\Alerts\AlertChannel::class;
}

public function toAlert($notifiable) {
    return FluxCallout::make(['body' => 'Notification alert!']);
}
</code-snippet>
@endverbatim
