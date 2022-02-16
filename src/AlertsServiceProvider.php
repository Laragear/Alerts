<?php

namespace Laragear\Alerts;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class AlertsServiceProvider extends ServiceProvider
{
    public const CONFIG = __DIR__ . '/../config/alerts.php';
    public const VIEWS = __DIR__ . '/../resources/views';

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(static::CONFIG, 'alerts');

        $this->app->singleton(RendererManager::class);

        $this->app->singleton(Contracts\Renderer::class, static function (Container $app): Contracts\Renderer {
            return $app->make(RendererManager::class)->driver($app->make('config')->get('alerts.renderer'));
        });

        $this->app->singleton(Bag::class, static function (Container $app): Bag {
            return new Bag((array) $app->make('config')->get('alerts.tags', ['default']));
        });

        $this->app->bind(
            Http\Middleware\StoreAlertsInSession::class,
            static function (Container $app): Http\Middleware\StoreAlertsInSession {
                return new Http\Middleware\StoreAlertsInSession(
                    $app->make(Bag::class),
                    $app->make('config')->get('alerts.key')
                );
            }
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @param  \Illuminate\Foundation\Http\Kernel  $http
     * @param  \Illuminate\Routing\Router  $router
     *
     * @return void
     */
    public function boot(Kernel $http, Router $router): void
    {
        $this->loadViewsFrom(static::VIEWS, 'alerts');
        $this->loadViewComponentsAs('alerts', [Blade\Components\Container::class]);

        // Add the Global Middleware to the `web` group only if it exists.
        if (array_key_exists('web', $http->getMiddlewareGroups())) {
            $http->appendMiddlewareToGroup('web', Http\Middleware\StoreAlertsInSession::class);
        }

        $router->aliasMiddleware('alerts.json', Http\Middleware\AddAlertsToJson::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([static::CONFIG => $this->app->configPath('alerts.php')], 'config');
            $this->publishes([static::VIEWS => $this->app->viewPath('vendor/alerts')], 'views');
        }
    }
}