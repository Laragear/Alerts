<?php

namespace Tests;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Laragear\Alerts\AlertsServiceProvider;
use Laragear\Alerts\Bag;
use Laragear\Alerts\Blade\Components\Container;
use Laragear\Alerts\Contracts\Renderer;
use Laragear\Alerts\Http\Middleware\AddAlertsToJson;
use Laragear\Alerts\Http\Middleware\StoreAlertsInSession;
use Laragear\Alerts\RendererManager;
use Laragear\Alerts\Renderers\BootstrapRenderer;

class ServiceProviderTest extends TestCase
{
    public function test_merges_config(): void
    {
        static::assertSame(
            File::getRequire(AlertsServiceProvider::CONFIG),
            $this->app->make('config')->get('alerts')
        );
    }

    public function test_load_views(): void
    {
        static::assertArrayHasKey('alerts', $this->app->make('view')->getFinder()->getHints());
    }

    public function test_loads_blade_component(): void
    {
        $aliases = $this->app->make('blade.compiler')->getClassComponentAliases();

        static::assertArrayHasKey('alerts-container', $aliases);
        static::assertSame(Container::class, $aliases['alerts-container']);
    }

    public function test_registers_renderer_manager(): void
    {
        static::assertTrue($this->app->bound(RendererManager::class));
    }

    public function test_registers_renderer_contract_and_default_renderer(): void
    {
        static::assertTrue($this->app->bound(Renderer::class));
        static::assertInstanceOf(BootstrapRenderer::class, $this->app->make(Renderer::class));
    }

    public function test_registers_bag(): void
    {
        static::assertTrue($this->app->bound(Bag::class));
    }

    public function test_registers_middleware_for_storing_alerts_in_session()
    {
        static::assertTrue($this->app->bound(StoreAlertsInSession::class));
    }

    public function test_registers_middleware(): void
    {
        $kernel = $this->app->make(Kernel::class);

        static::assertEquals(StoreAlertsInSession::class, Arr::last($kernel->getMiddlewareGroups()['web']));

        $router = $this->app->make(Router::class);

        static::assertArrayHasKey('alerts.json', $router->getMiddleware());
        static::assertSame(AddAlertsToJson::class, $router->getMiddleware()['alerts.json']);
    }

    public function test_publishes_config(): void
    {
        static::assertSame([
            AlertsServiceProvider::CONFIG => $this->app->configPath('alerts.php'),
        ], ServiceProvider::pathsToPublish(AlertsServiceProvider::class, 'config'));
    }

    public function test_publishes_views(): void
    {
        static::assertSame([
            AlertsServiceProvider::VIEWS => $this->app->viewPath('vendor/alerts'),
        ], ServiceProvider::pathsToPublish(AlertsServiceProvider::class, 'views'));
    }
}
