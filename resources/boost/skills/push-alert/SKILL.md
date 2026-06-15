---
name: push-alert
description: Push and manage alerts in the session or bag for rendering.
---

# Push Alert

## When to use this skill

Use this skill when you need to add an alert to the current request's bag, handle conditional alert pushing, or manage persistent alerts that last across multiple requests.

## Features

### Pushing Alerts

Use the `push()` static method to create and add an alert to the bag in one go.

```php
MyAlert::push(['body' => 'Operation successful']);
```

### Conditional Pushing

Use `pushWhen()` or `pushUnless()` to add an alert only if a condition evaluates to true or false.

```php
MyAlert::pushWhen($request->user()->hasVerifiedEmail(), ['body' => 'Verified!']);
```

### Persistent Alerts

Make an alert persistent using `persistAs($key)`. It will remain in the session until manually abandoned using `abandon($key)`.

```php
// Persist
MyAlert::push(['body' => 'System maintenance'])->persistAs('maintenance');

// Abandon
MyAlert::abandon('maintenance');
```

### Manual Bagging

Use `make()` to create an instance without adding it to the bag, then use `pushToBag()` when ready.

```php
$alert = MyAlert::make(['body' => 'Wait for it...']);
// ... logic ...
$alert->pushToBag();
```
