<?php

namespace Tests\Http\Middleware;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Support\Facades\Route;
use Laragear\Alerts\Http\Middleware\StoreAlertsInSession;
use Tests\TestCase;
use function alert;
use function redirect;

class StoreAlertsInSessionTest extends TestCase
{
    use InteractsWithViews;

    protected function defineRoutes($router)
    {
        $router->get('no-session', function () {
            alert()->message('foo')->persistAs('foo.bar');
            return 'ok';
        })->middleware(StoreAlertsInSession::class);
    }

    protected function defineWebRoutes($router)
    {
        $router->get('foo', function () {
            alert('foo');
            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        $router->get('bar', function () {
            alert('bar');
            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        $router->get('empty', function () {
            alert()->message('');
            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        $router->get('persist', function () {
            alert()->message('foo');
            alert()->message('foo')->persistAs('foo.bar');
            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        $router->get('no-alert', function () {
            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        $router->get('redirect', function () {
            alert()->message('redirected');
            return redirect()->to('no-alert');
        })->middleware('web');

        $router->get('redirect-with-both', function () {
            alert()->message('redirected');
            alert()->message('redirect persisted')->persistAs('foo.bar');
            return redirect()->to('no-alert');
        })->middleware('web');
    }

    public function test_doesnt_stores_persistent_without_session(): void
    {
        $this->get('no-session')
            ->assertOk()
            ->assertSee('ok')
            ->assertSessionMissing('_alerts');
    }

    public function test_renders_empty_alerts(): void
    {
        $response = $this->get('empty')->assertSessionMissing('_alerts');

        static::assertEquals('<div class="container"><div class="alerts">
        <div class="alert" role="alert">
' . '    ' . '
    </div>
    </div>
</div>'     ,
            $response->getContent()
        );
    }

    public function test_renders_alert_one_time_if_not_redirect(): void
    {
        $response = $this->get('foo')->assertSessionMissing('_alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"><div class="alerts">
        <div class="alert" role="alert">
    foo
    </div>
    </div>
</div>
VIEW
            ,
            $response->getContent()
        );

        $response = $this->get('no-alert')->assertSessionMissing('_alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"></div>
VIEW
            ,
            $response->getContent()
        );
    }

    public function test_alert_flashed_in_session_when_redirects(): void
    {
        $this->get('redirect')->assertSessionHas('_alerts');
    }

    public function test_alert_renders_through_redirect(): void
    {
        $response = $this->followingRedirects()->get('redirect')->assertSessionMissing('_alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"><div class="alerts">
        <div class="alert" role="alert">
    redirected
    </div>
    </div>
</div>
VIEW
            ,
            $response->getContent()
        );

        $response = $this->get('no-alert')->assertSessionMissing('_alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"></div>
VIEW,
            $response->getContent()
        );
    }

    public function test_alert_persistent_and_non_persistent_renders_through_redirect(): void
    {
        $response = $this->followingRedirects()->get('redirect-with-both')->assertSessionHas('_alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"><div class="alerts">
        <div class="alert" role="alert">
    redirect persisted
    </div>
<div class="alert" role="alert">
    redirected
    </div>
    </div>
</div>
VIEW
            ,
            $response->getContent()
        );

        $response = $this->get('no-alert')->assertSessionHas('_alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"><div class="alerts">
        <div class="alert" role="alert">
    redirect persisted
    </div>
    </div>
</div>
VIEW
            ,
            $response->getContent()
        );
    }

    public function test_persists_alerts_through_session(): void
    {
        $response = $this->get('persist')->assertSessionHas('_alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"><div class="alerts">
        <div class="alert" role="alert">
    foo
    </div>
<div class="alert" role="alert">
    foo
    </div>
    </div>
</div>
VIEW
            ,
            $response->getContent()
        );

        $response = $this->get('no-alert')->assertSessionHas('_alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"><div class="alerts">
        <div class="alert" role="alert">
    foo
    </div>
    </div>
</div>
VIEW
            ,
            $response->getContent()
        );
    }

    public function test_same_persisted_key_replaces_previous_alert(): void
    {
        Route::get('persist')->uses(function () {
            alert()->message('foo')->types('success')->persistAs('foo.bar');
            alert()->message('bar')->persistAs('foo.bar');
            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        static::assertEquals(
            <<<'VIEW'
<div class="container"><div class="alerts">
        <div class="alert" role="alert">
    bar
    </div>
    </div>
</div>
VIEW
            ,
            $this->get('persist')->getContent()
        );
    }

    public function test_next_request_replaces_persistent_alert(): void
    {
        Route::get('first')->uses(function () {
            alert()->message('foo')->types('success')->persistAs('foo.bar');
            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        Route::get('second')->uses(function () {
            alert()->message('bar')->persistAs('foo.bar');
            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        $this->get('first');

        static::assertSame(
            <<<'VIEW'
<div class="container"><div class="alerts">
        <div class="alert" role="alert">
    bar
    </div>
    </div>
</div>
VIEW,
            $this->get('second')->getContent()
        );
    }

    public function test_next_redirect_request_replaces_persistent_alert(): void
    {
        Route::get('first')->uses(function () {
            alert()->message('foo')->types('success')->persistAs('foo.bar');
            return redirect('/second');
        })->middleware('web');

        Route::get('second')->uses(function () {
            alert()->message('bar')->persistAs('foo.bar');
            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        static::assertSame(
            <<<'VIEW'
<div class="container"><div class="alerts">
        <div class="alert" role="alert">
    bar
    </div>
    </div>
</div>
VIEW,
            $this->followingRedirects()->get('first')->getContent()
        );
    }
}
