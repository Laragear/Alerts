# Alerts
[![Latest Version on Packagist](https://img.shields.io/packagist/v/laragear/alerts.svg)](https://packagist.org/packages/laragear/alerts)
[![Latest stable test run](https://github.com/Laragear/Alerts/workflows/Tests/badge.svg)](https://github.com/Laragear/Alerts/actions)
[![Codecov coverage](https://codecov.io/gh/Laragear/Alerts/graph/badge.svg?token=U6QBXDK9BQ)](https://codecov.io/gh/Laragear/Alerts)
[![Maintainability](https://qlty.sh/gh/Laragear/projects/Alerts/maintainability.svg)](https://qlty.sh/gh/Laragear/projects/Alerts)
[![Sonarcloud Status](https://sonarcloud.io/api/project_badges/measure?project=Laragear_Alerts&metric=alert_status)](https://sonarcloud.io/dashboard?id=Laragear_Alerts)
[![Laravel Octane Compatibility](https://img.shields.io/badge/Laravel%20Octane-Compatible-success?style=flat&logo=laravel)](https://laravel.com/docs/10.x/octane#introduction)

Set multiple alerts from your backend, render them in the frontend with any HTML.

```php
use App\Alerts\FluxCallout;

FluxCallout::push(['body' => 'This is awesome!']);
```

```html
<div class="alert alert-success" role="alert">
    This is awesome! 😍
</div>
```

## Become a sponsor

[![](.github/assets/support.png)](https://github.com/sponsors/DarkGhostHunter)

Your support allows me to keep this package free, up-to-date and maintainable. Alternatively, you can **[spread the word!](http://x.com/share?text=I%20am%20using%20this%20cool%20PHP%20package&url=https://github.com%2FLaragear%2FAlerts&hashtags=PHP,Laravel)**

## Requirements

* PHP 8.3 or later
* Laravel 12 or later

## Installation

You can install the package via composer:

```bash
composer require laragear/alerts
```

## Creating Alerts

Laragear Alerts allows pushing your own custom Alerts into any frontend, being Blade views, Livewire responses or Inertia applications.

This approach makes it compatible with any type of frontend you have: Flux UI, Vuetify, Flowbite, Nuxt UI, DaisyUI, TallStackUI, Wire UI, you name it!

First, create your own alert using the `alert:create` Artisan command with the name of your alert. For example, we will create one for [Flux UI Callouts](https://fluxui.dev/components/callout).

```shell
php artisan alert:create FluxCallout
```

You will receive a class with some common defaults inside the `app\Views\Alerts` directory, and a view at the `resources/views/alerts` using the name of the alert in `slug-case`.

```php
namespace App\Views\Alerts;

use Laragear\Alerts\Alert;

class FluxCallout extends Alert
{
    /**
     * @inheritDoc
     */
    public function toHtml()
    {
        return view('alerts.flux-callout', $this->all());
    }
}
```

```bladehtml
<!-- resources/views/alerts/flux-callout.blade.php -->
<div class="alert">
    {{ $body }}
</div>
```

Once your alert is created, you can start modifying to match your frontend.

### Adding methods

Alerts are simple classes, so you can add any method you see fit to make filling your Alert data more convenient.

For example, you may create a static method to create a "successful" Alert, or automatically escape data that may include user-generated content with the [`e()`](https://laravel.com/docs/strings#method-e) helper.

```php
use Illuminate\Support\Str;

/**
 * Add actions to the Flux UI Callout.
 * 
 * @return $this
 */
public function actions(array $actions): static
{
    return $this->set('actions', $actions);
}

/**
 * Create a successful Flux UI callout.
 *  
 * @return $this
 */
public static function success(string $heading, string $text = '', string $icon = ''): static
{
    return static::push(array_filter([
        'heading' => $heading,
        'text' => e($text)
        'icon' => $icon ?: 'check'
    ]));
}
```

> [!IMPORTANT]
> 
> When adding objects, consider some [serialization caveats](#serialization).

### Default values

If you need to create Alerts with a set of default values, use the `getDefaults()` method to return an array of these values. These will be copied into the Alert attributes when it is instanced.

```php
/**
 * Returns an array of default values.
 */
protected function getDefaults(): array
{
    return [
        'color' => 'secondary',
    ];
}
```

## Pushing Alerts

Alerts are _pushed_ to the frontend using the `push()` static method. After pushed, you can still modify the Alert, like adding a title, text, links or anything you want.

The `push()` accepts an array of raw attributes you can add to your Alert in one go, but you're not limited to that. If you defined convenience methods, you can also use them.

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Alerts\FluxCallout;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    /**
     * Confirm the invoice payment.
     */
    public function confirm(Request $request, Invoice $invoice)
    {
        $invoice->markAsConfirmed();
        
        FluxCallout::push([
            'color' => 'green',
            'heading' => 'Invoice paid',
        ])->actions([
            ['label' => 'Follow order', 'href' => route('order.show', $invoice->order)]
        ]);

        return to_route('invoice.show', $invoice);
    }
}
```

You may also use the `pushToBag()` method after it has been instanced to move the Alert to the bag, especially if you use the `make()` static method to create instance. This is great to use when combined with conditional methods, like `when()`.

```php
FluxCallout::make([
    'color' => 'green',
    'heading' => 'Invoice paid',
])->actions([
    ['label' => 'Follow order', 'href' => route('order.show', $invoice->order)]
])->when($invoice->isPaid())->pushToBag();
```

> [!NOTE]
> 
> The `make()` static method **doesn't add the alert to the bag**. It's just syntactic sugar for `new Alert()`.  

### Localization

Localization for your Alert is done at class-level. In other words, when you set a given string, you may use Laravel's `trans()` or `transChoice()` methods _inside_ the class method to translate the string, instead of doing it outside, thus making it consistent across your application.

```php
/**
 * Add a localized title. 
 */
public function heading(...$arguments)
{
    return $this->set('heading', trans(...$arguments));
}
```

```php
FluxCallout::push()->heading('payment.success');
```

### Conditions

You can also push an Alert if a condition evaluates to true or false by using `pushWhen()` and `pushUnless()`, respectively. Further method calls will be sent to the void.

```php
use Illuminate\Support\Facades\Auth;
use App\Alerts\FluxCallout;

FluxCallout::pushWhen(Auth::check())
    ->heading('You are authenticated');

FluxCallout::pushUnless(Auth::user()->mailbox()->isNotEmpty())
    ->heading('You have messages in your inbox');
```

### Persistence

Alerts only last for the actual response being sent. On redirects, these are [flashed into the session](https://laravel.com/docs/session#flash-data) so these are available on the next request (the redirection target).

To make any alert persistent, you can use the `persistAs()` method with a key to identify the alert.

```php
use App\Alerts\FluxCallout;

FluxCallout::push(['title' => 'Your disk size is almost full'])
    ->color('red')
    ->persistAs('disk.full');
```

> [!NOTE]
>
> Setting a persistent alert replaces any previous set with the same key.

Once you're done, you can delete the persistent Alert using `abandon()` method directly from the helper using the key of the persisted Alert. For example, we can abandon the previous alert if the disk is no longer full.

```php
use App\Alerts\FluxCallout;

if ($disk->notFull()) {
    FluxCallout::abandon('disk.full');
}
```

## Rendering

To render alerts in the frontend, use the `<x-alerts-container />` Blade component which will take care of the magic, anywhere you want to put it.

```bladehtml
<div class="header">
    <h1>Welcome to my site</h1>
    
    <!-- Add the container for the alerts -->
    <x-alerts-container />
</div>
```

The component cycles through each alert and calls `toHtml()` to render them in your view. You're free to alter the `toHtml()` method of your alert with custom data.

```php
public function toHtml()
{
    return view('alerts.flux-callout', [
        'heading' => $this->heading,
        'text' => $this->text,
        'actions' => $this->actions,
    ]);
}
```

The container view is set as `laragear::alerts.container`. You're free to change the container as you see fit for your application.

### Filter by class

When using the [Alerts directive](#rendering), all the alerts will be rendered in the order these were instanced.

You can _filter_ the alerts to be rendered using the `filter` attribute of the directive. Only matching FQN names of the classes alerts will be rendered.

```bladehtml
<!-- Only show system-wide alerts -->
<x-alerts-container filter="\App\Alerts\SystemAlert" />

<!-- Show everything except global alerts and system-wide alerts -->
<x-alerts-container filter="\App\Alerts\GlobalAlert,\App\Alerts\SystemAlert" />
```

## Alerts as Notifications

If you're deep into Laravel Notifications, you can easily create notifications for your frontend, especially if these are broadcasted, using the Alert Channel.

Use the `Laragear\Alerts\AlertChannel` class as a channel, and the `toAlert()` method in your notification with an Alert instance.

```php
use App\Alerts\FluxCallout;
use App\Models\Invoice;
use Laragear\Alerts\Alert;
use Laragear\Alerts\AlertChannel;
use Illuminate\Notifications\Notification;
 
class InvoicePaid extends Notification
{
    /**
     * Create a new Invoice Paid instance.
     */
    public function __construct(protected Invoice $invoice)
    {
        //
    }

    /**
     * Get the notification channels.
     */
    public function via(object $notifiable): string
    {
        return AlertChannel::class;
    }
 
    /**
     * Get the alert representation of the notification.
     */
    public function toAlert(object $notifiable): Alert
    {
        return FluxCallout::make()
            ->title('InvoicePaid')
            ->text('You will receive a copy of the transaction in your email')
            ->actions(
                ['label' => 'Follow order', 'href' => route('orders.show', $this->invoice->order)],
                ['label' => 'See all orders', 'href' => route('orders.index')],
            );
    }
}
```

It's recommended to use `make()` instead of `push()` if you expect to test your notifications outside a request lifecycle. 

## Configuration

Alerts will work out-of-the-box with some common defaults, but if you need a better approach for your particular application, you can configure some parameters. First, publish the configuration file.

```bash
php artisan vendor:publish --provider="Laragear\Alerts\AlertsServiceProvider" --tag="config"
``` 

Let's examine the configuration array, which is quite simple:

```php
return [
    'session' => true,
    'key' => '_alerts',
];
```

### Session

```php
return [
    'session' => true,
];
```

When your application frontend is detached from the backend, like when using JavaScript or SPA, there is little to no benefit on storing the alerts into the session, especially when your alerts are meant to be ephemeral (like toasts). You will probably send them through your application JSON response [using the included middleware](#sending-json-alerts).

By disabling this with `false`, your session may be leaner since that logic is bypassed. 

### Session Key

```php
return [
    'key' => '_alerts',
];
```

When alerts are flashed or persisted, these are stored in the Session by a given key, which is `_alerts` by default. If you're using this key name for other things, you may want to change it.

This key is also used when [sending JSON alerts](#sending-json-alerts).

## Serialization

Alerts implement the `__serialize()` and `__unserialize()` to only save its attributes and the persistence key. The rest of the class properties will not be serialized.

If you depend on custom serialization, like restoring object instances or other data, you may override the serialization methods to store a storable representation of your data. For example, when you have to deal with object instances like Container Services or Eloquent Models.

```php
use App\Models\User;

protected User $user;

public function __serialize(): array
{
    return array_merge(parent::__serialize(), [
        'user' => $this->user->getKey(),
    ];
}

public function __unserialize(array $data): void
{
    parent::__unserialize($data);
    
    $this->user = User::findOrFail($data['user']);
}
```

### JSON

Alerts can be serialized into an array and a JSON string automatically, using `toArray()` and `toJson()`, respectively. This means you can send an Alert as a JSON response. By default, it will expose all its attributes.

```json
{
    "heading": "Invoice paid",
    "text": "Follow your order in your dashboard.",
    "color": "green"
}
```

> [!NOTE]
>
> If you want to alter how these are serialized, alter the `toArray()` or `toJson()` methods.

### Receiving JSON Alerts

Sometimes your application may receive a JSON Alert from an external service using this package. You can quickly add this JSON as an Alert to your application using the `fromJson()` method.

```json
{
    "data" : {
        "items": "..."
    },
    "alerts": [
        {
            "heading": "Invoice paid",
            "text": "Follow your order in your dashboard.",
            "color": "green"
        }
    ]
}
```

```php
use Illuminate\Http\Request;
use App\Alerts\FluxCallout;

public function fromServer(Request $request)
{
    // Add the alerts from the response
    $request->collect('alerts')->map(FluxCallout::push(...))
}
```

### Sending JSON Alerts

This library has a convenient way to add Alerts into your JSON Responses. This can be invaluable to add your alerts to each response being sent to the browser, like combining this package with [Laravel Jetstream](https://jetstream.laravel.com/).

[Add the `alerts.json` middleware](https://laravel.com/docs/middleware#registering-middleware) into your `api` routes or, if you're using [Laravel Jetstream](https://jetstream.laravel.com/) or similar, as a [global middleware](https://laravel.com/docs/middleware#global-middleware).

When you return a `JsonResponse` to the browser, the middleware will append the alert as JSON using the same [session key](#session-key) defined in the configuration, which is `_alerts` by default. It also accepts the `key` parameter to use as an alternative, compatible with `dot.notation`.

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::prefix('api')
    ->middleware('alerts.json:_status.alerts')
    ->controller(UserController::class, function () {
    
        Route::post('user/create', 'create');
        Route::post('user/{user}/update', 'update');
        
    });
```

When you receive a JSON Response, you will see the alerts appended to whichever key you issued. Using the above example, we should see the `alerts` key under the `_status` key:

```json
{
    "resource": "users",
    "url": "/v1/users",
    "_status": {
        "timestamp":  "2019-06-05T03:47:24Z",
        "action" : "created",
        "id": 648,
        "alerts": [
            {
                "heading": "Invoice paid",
                "text": "Follow your order in your dashboard.",
                "color": "green"
            }
        ]
    }
}
```

> [!WARNING]
>
> If your key is already present in the JSON response, **the key will be overwritten**.

#### Sending Alerts to Laravel Inertia

If you're using Laravel Inertia, you may want to use the `alerts.inertia` middleware instead of the `alerts.json`. The middleware will add the alerts **only** to Inertia requests/responses, bypassing redirects.

```php
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Inertia\Inertia;
use Laragear\Alerts\Facades\Alert;

Route::middleware('alerts.inertia')
    ->get('users', function () {
        // Push an alert to the frontend.
        FluxCallout::make()
            ->title('All users accounted for!')
            ->color('red');
    
        return Inertia::render('users/index', [
            'users' => User::paginate()
        ]);
    });
```

Then, you can retrieve them in your frontend page as [shared data](https://inertiajs.com/shared-data#accessing-shared-data).

```vue
<script setup>
import { usePage } from '@inertiajs/vue3'

const page = usePage()
    
const alerts = page.props._alerts
</script>

<template>
  <div v-for="alert in alerts">
    <!-- ... -->
  </div>
</template>
```

## Testing

To test if alerts were generated, you can use `fake()` from the `Alert` facade, which works like any other faked services. It returns a fake Alert Bag that holds a copy of all alerts generated, which exposes some convenient assertion methods.

```php
use Laragear\Alerts\Facades\Alert;

public function test_alert_sent()
{
    $alert = Alert::fake();
    
    $this->post('/comment', ['body' => 'cool'])->assertOk();
    
    $alert->assertHasOne();
}
```

The following assertions are available:

| Method                    | Description                                                            |
|---------------------------|------------------------------------------------------------------------|
| `assertEmpty()`           | Check if the alert bag doesn't contains alerts.                        |
| `assertNotEmpty()`        | Check if the alert bag contains any alert.                             |
| `assertHasOne()`          | Check if the alert bag contains only one alert.                        |
| `assertHas($count)`       | Check if the alert bag contains the exact amount of alerts.            |
| `assertHasPersistent()`   | Check if the alert bag contains at least one persistent alert.         |
| `assertHasNoPersistent()` | Check if the alert bag doesn't contains a persistent alert.            |
| `assertPersistentCount()` | Check if the alert bag contains the exact amount of persistent alerts. |

### Asserting specific alerts

The fake Alert bag allows building conditions for the existence (or nonexistence) of alerts with specific properties, by using `assertAlert()`, followed by the `with()` method to build expectations. Once you build your expectations, you can use `exists()` to check if any alert matches, or `missing()` to check if no alert should match.

```php
use Laragear\Alerts\Facades\Alert;

$bag = Alert::fake();

$bag->assertAlert()->with('heading', 'Hello world!')->exists();

$bag->assertAlert()->with(fn ($alert) => 'green' === $alert->color)->missing();
```

> [!NOTE]
>
> Alternatively, you can use `count()` if you expect a specific number of alerts to match the given conditions, or `unique()` for matching only one alert.

Finally, to test if an alert is persisted or not, use the `persisted()` and `notPersisted()`, respectively.

```php
use Laragear\Alerts\Facades\Alert;

$bag = Alert::fake();

$bag->assertAlert()->persisted()->count(2);
```

## Laravel Octane compatibility

- The Bag is registered as singleton. **You shouldn't resolve it at boot time.**
- The Bag contains a stale version of the app config. **You shouldn't change the config.**
- There are no static properties written during a request.

There should be no problems using this package with Laravel Octane if you use this package as intended.

## Security

If you discover any security-related issues, please [use the online form](https://github.com/Laragear/Alerts/security).

# License

This specific package version is licensed under the terms of the [MIT License](LICENSE.md), at the time of publishing.

[Laravel](https://laravel.com) is a Trademark of [Taylor Otwell](https://github.com/TaylorOtwell/). Copyright © 2011–2026 Laravel LLC.
