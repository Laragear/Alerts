<?php

namespace Tests\Http\Middleware;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Inertia\Inertia;
use Inertia\Middleware;
use Inertia\Testing\AssertableInertia;
use Tests\Fixtures\TestAlert;
use Tests\TestCase;
use function response;

class AddAlertsToInertiaTest extends TestCase
{
    use InteractsWithViews;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterApplicationCreated(function (): void {
            $this->app->make('files')->copy(
                __DIR__.'/../../Fixtures/views/app.blade.php',
                resource_path('views/app.blade.php'),
            );

            $this->app->make('config')->set('inertia.testing.ensure_pages_exist', false);
        });
    }

    public function test_does_not_adds_alerts_when_non_inertia_request(): void
    {
        $this->app->make('router')->get(
            'test',
            function () {
                TestAlert::push(['foo' => 'bar']);

                return response()->json(['bar' => 'baz']);
            },
        )->middleware(['web', Middleware::class, 'alerts.inertia']);

        $this->get('test')->assertExactJson(['bar' => 'baz']);
        $this->getJson('test')->assertExactJson(['bar' => 'baz']);
    }

    public function test_adds_alerts_when_when_inertia_response(): void
    {
        $this->app->make('router')->get(
            'test',
            function () {
                TestAlert::push(['foo' => 'bar']);

                return Inertia::render('test', [
                    'foo' => 'bar',
                ]);
            },
        )->middleware(['web', Middleware::class, 'alerts.inertia']);

        $this->get('test')
            ->assertInertia(static function (AssertableInertia $page): void {
                $page->component('test')
                    ->where('foo', 'bar')
                    ->where('_alerts', [
                        ['foo' => 'bar'],
                    ]);
            });
    }

    public function test_adds_alerts_key_when_inertia_response_and_is_empty(): void
    {
        $this->app->make('router')->get(
            'test',
            function () {
                return Inertia::render('test', [
                    'foo' => 'bar',
                ]);
            },
        )->middleware(['web', Middleware::class, 'alerts.inertia']);

        $this->get('test')
            ->assertInertia(static function (AssertableInertia $page): void {
                $page->component('test')
                    ->where('foo', 'bar')
                    ->where('_alerts', []);
            });
    }

    public function test_adds_alerts_to_inertia_after_redirection(): void
    {
        $router = $this->app->make('router');

        $router->get('redirect', function () {
            TestAlert::push(['foo' => 'bar']);

            return redirect()->to('/test');
        })->middleware(['web', Middleware::class, 'alerts.inertia']);

        $router->get('test', fn () => Inertia::render('test', ['foo' => 'bar']))
            ->middleware(['web', Middleware::class, 'alerts.inertia']);

        $this->followingRedirects()
            ->get('redirect')
            ->assertInertia(static function (AssertableInertia $page): void {
                $page->component('test')
                    ->where('foo', 'bar')
                    ->where('_alerts', [
                        ['foo' => 'bar'],
                    ]);
            });
    }
}
