<?php

namespace Tests\Http\Middleware;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Support\Facades\Route;
use Laragear\Alerts\Http\Middleware\StoreAlertsInSession;
use Tests\Fixtures\TestAlertWithView;
use Tests\TestCase;

use function redirect;

class StoreAlertsInSessionTest extends TestCase
{
    use InteractsWithViews;

    protected function defineRoutes($router)
    {
        $router->get('no-session', function () {
            TestAlertWithView::push(['foo' => 'bar'])->persistAs('foo.bar');

            return 'ok';
        })->middleware(StoreAlertsInSession::class);
    }

    protected function defineWebRoutes($router)
    {
        $router->get('foo', function () {
            TestAlertWithView::push(['foo' => 'bar']);

            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        $router->get('bar', function () {
            TestAlertWithView::push(['foo' => 'bar']);

            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        $router->get('empty', function () {
            TestAlertWithView::push();

            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        $router->get('persist', function () {
            TestAlertWithView::push(['foo' => 'bar']);
            TestAlertWithView::push(['baz' => 'quz'])->persistAs('foo.bar');

            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        $router->get('no-alert', function () {
            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        $router->get('redirect', function () {
            TestAlertWithView::push(['text' => 'redirected']);

            return redirect()->to('no-alert');
        })->middleware('web');

        $router->get('redirect-with-both', function () {
            TestAlertWithView::push(['text' => 'redirected']);
            TestAlertWithView::push(['text' => 'redirected persisted'])->persistAs('foo.bar');

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

    public function test_doesnt_stores_persistent_if_disabled_by_config(): void
    {
        $this->app->make('config')->set('alerts.session', false);

        $this->get('persist')
            ->assertOk()
            ->assertSessionMissing('_alerts');
    }

    public function test_renders_empty_alerts(): void
    {
        $response = $this->get('empty')->assertSessionMissing('_alerts');

        static::assertEquals('<div class="container"><div class="alerts">
            itrenders
    </div>
</div>',
            $response->getContent()
        );
    }

    public function test_renders_alert_one_time_if_not_redirect(): void
    {
        $response = $this->get('foo')->assertSessionMissing('_alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"><div class="alerts">
            itrenders
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
        $response = $this->followingRedirects()->get('redirect')->assertSessionMissing('_alerts.alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"><div class="alerts">
            itrenders
    </div>
</div>
VIEW
            ,
            $response->getContent()
        );

        $response = $this->get('no-alert')->assertSessionMissing('_alerts.alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"></div>
VIEW,
            $response->getContent()
        );
    }

    public function test_alert_persistent_and_non_persistent_renders_through_redirect(): void
    {
        $response = $this->followingRedirects()->get('redirect-with-both')->assertSessionMissing('_alerts.alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"><div class="alerts">
            itrenders
            itrenders
    </div>
</div>
VIEW
            ,
            $response->getContent()
        );

        $response = $this->get('no-alert')->assertSessionMissing('_alerts.alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"><div class="alerts">
            itrenders
    </div>
</div>
VIEW
            ,
            $response->getContent()
        );
    }

    public function test_persists_alerts_through_session(): void
    {
        $response = $this->get('persist')->assertSessionMissing('_alerts.alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"><div class="alerts">
            itrenders
            itrenders
    </div>
</div>
VIEW
            ,
            $response->getContent()
        );

        $response = $this->get('no-alert')->assertSessionMissing('_alerts.alerts');

        static::assertEquals(
            <<<'VIEW'
<div class="container"><div class="alerts">
            itrenders
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
            $first = TestAlertWithView::push()->persistAs('foo.bar');
            $first->callback = fn () => 'foo';

            $last = TestAlertWithView::push(['baz' => 'quz'])->persistAs('foo.bar');
            $last->callback = fn () => 'bar';

            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        static::assertEquals(
            <<<'VIEW'
<div class="container"><div class="alerts">
            bar
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
            $first = TestAlertWithView::push()->persistAs('foo.bar');
            $first->callback = fn () => 'foo';

            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        Route::get('second')->uses(function () {
            $first = TestAlertWithView::push()->persistAs('foo.bar');
            $first->callback = fn () => 'bar';

            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        $this->get('first');

        static::assertSame(
            <<<'VIEW'
<div class="container"><div class="alerts">
            bar
    </div>
</div>
VIEW,
            $this->get('second')->getContent()
        );
    }

    public function test_next_redirect_request_replaces_persistent_alert(): void
    {
        Route::get('first')->uses(function () {
            $first = TestAlertWithView::push()->persistAs('foo.bar');
            $first->callback = fn () => 'foo';

            return redirect('/second');
        })->middleware('web');

        Route::get('second')->uses(function () {
            $first = TestAlertWithView::push()->persistAs('foo.bar');
            $first->callback = fn () => 'bar';

            return (string) $this->blade('<div class="container"><x-alerts-container /></div>');
        })->middleware('web');

        static::assertSame(
            <<<'VIEW'
<div class="container"><div class="alerts">
            bar
    </div>
</div>
VIEW,
            $this->followingRedirects()->get('first')->getContent()
        );
    }
}
