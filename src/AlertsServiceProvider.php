<?php

namespace Laragear\Alerts;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel as HttpContract;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class AlertsServiceProvider extends ServiceProvider
{
    public const CONFIG = __DIR__.'/../config/alerts.php';
    public const VIEWS = __DIR__.'/../resources/views';

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(static::CONFIG, 'alerts');

        $this->app->singleton(RendererManager::class);

        $this->app->singleton(Contracts\Renderer::class, static function (Application $app): Contracts\Renderer {
            return $app->make(RendererManager::class)->driver($app->make('config')->get('alerts.renderer'));
        });

        $this->app->singleton(Bag::class, static function (Application $app): Bag {
            return new Bag((array) $app->make('config')->get('alerts.tags', ['default']));
        });

        $this->app->bind(
            Http\Middleware\StoreAlertsInSession::class,
            static function (Application $app): Http\Middleware\StoreAlertsInSession {
                return new Http\Middleware\StoreAlertsInSession(
                    $app->make(Bag::class),
                    $app->make('config')->get('alerts.key')
                );
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(HttpContract $http, Router $router): void
    {
        $this->loadViewsFrom(static::VIEWS, 'alerts');
        $this->loadViewComponentsAs('alerts', [Blade\Components\Container::class]);

        // Add the Global Middleware to the `web` group only if it exists.
        // @phpstan-ignore-next-line
        if (array_key_exists('web', $http->getMiddlewareGroups())) {
            // @phpstan-ignore-next-line
            $http->appendMiddlewareToGroup('web', Http\Middleware\StoreAlertsInSession::class);
        }

        $router->aliasMiddleware('alerts.json', Http\Middleware\AddAlertsToJson::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([static::CONFIG => $this->app->configPath('alerts.php')], 'config');
            // @phpstan-ignore-next-line
            $this->publishes([static::VIEWS => $this->app->viewPath('vendor/alerts')], 'views');
        }
    }
}
