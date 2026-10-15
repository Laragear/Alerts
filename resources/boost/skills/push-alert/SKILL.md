---
name: laragear-alerts-push-alert
description: "Push alerts to the current or next request, push conditionally, make alerts persist across requests, or abandon persisted alerts. Exclude creating alert classes, rendering alerts in Blade, or Notification channels (use alerts-create, alerts-render, alerts-notify)."
---

# Push Alerts

Use inside controllers, actions, or middleware where the backend must tell
the user something.

## One-shot push

`push()` adds the alert to the bag for the current request — or the next one,
if the response is a redirect:

```php
MyAlert::push(['body' => 'Operation successful']);
```

## Conditional push

Prefer `pushWhen()` / `pushUnless()` over wrapping `push()` in an `if` —
the intent reads directly at the call site:

```php
MyAlert::pushWhen($user->isAdmin(), ['body' => 'Welcome, Admin!']);
```

## Persistent alerts (multi-request lifecycle)

A persistent alert survives requests **until explicitly abandoned**. Follow
this exact order:

1. Persist with a stable, semantic key:
   `MyAlert::push(['body' => 'Disk full'])->persistAs('disk.full');`
2. Abandon when the condition is resolved, in the code path that resolves it:
   `MyAlert::abandon('disk.full');`

**Success state:** the alert no longer appears after `abandon()`.
**Failure state:** the alert reappears on every request — the abandon path
was never reached. Wire `abandon()` to the action that resolves the condition
(e.g., the controller that fixes it), not to an arbitrary page.

## Deferred push

When the alert must pass through other logic before being bagged, build with
`make()` and bag it later:

```php
$alert = MyAlert::make(['body' => 'Wait for it...']);
// ... logic that decides ...
$alert->pushToBag();
```
