# Laragear Alerts

When showing backend-generated feedback to the user (success/error banners, flash messages, UI notifications), use Laragear Alerts via the alert classes in `App\Alerts\` instead of `session()->flash()` or hand-built Blade partials.

Entry point: `YourAlert::push(['body' => '...'])` adds an alert for the current or next (redirected) request.

- **Setting up rendering:** To configure where alerts render in the frontend, or when alerts do not appear at all, use the `alerts-setup` skill.
- **Creating alert classes:** To define a new alert type or customize its methods, defaults, translations, or rendering, use the `laragear-alerts-alerts-create` skill.
- **Pushing alerts:** To push, conditionally push, persist, or abandon alerts in controllers, use the `laragear-alerts-alerts-push` skill.
- **Rendering alerts:** To display alerts in Blade layouts, use the `laragear-alerts-alerts-render` skill.
- **Notifications as alerts:** To deliver Laravel Notifications as frontend alerts, use the `laragear-alerts-alerts-notify` skill.
