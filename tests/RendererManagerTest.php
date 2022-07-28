<?php

namespace Tests;

use Laragear\Alerts\RendererManager;
use Laragear\Alerts\Renderers\BootstrapRenderer;
use Laragear\Alerts\Renderers\TailwindRenderer;

class RendererManagerTest extends TestCase
{
    public function test_gets_defaults(): void
    {
        static::assertSame(
            $this->app->make('config')->get('alerts.default'),
            $this->app->make(RendererManager::class)->getDefaultDriver()
        );
    }

    public function test_gets_default_renderer(): void
    {
        static::assertInstanceOf(BootstrapRenderer::class, $this->app->make(RendererManager::class)->driver());
    }

    public function test_gets_tailwind_css_renderer(): void
    {
        static::assertInstanceOf(TailwindRenderer::class, $this->app->make(RendererManager::class)->driver('tailwind'));
    }

    public function test_gets_tailwind_css_renderer_as_default(): void
    {
        $this->app->make('config')->set('alerts.default', 'tailwind');

        static::assertSame('tailwind', $this->app->make(RendererManager::class)->getDefaultDriver());
        static::assertInstanceOf(TailwindRenderer::class, $this->app->make(RendererManager::class)->driver('tailwind'));
    }
}
