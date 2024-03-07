<?php

namespace Laragear\Alerts;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Manager;

/**
 * @method \Laragear\Alerts\Contracts\Renderer driver($driver = null)
 * @codeCoverageIgnore
 */
class RendererManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('alerts.default', 'bootstrap');
    }

    /**
     * Creates a Bootstrap renderer.
     */
    protected function createBootstrapDriver(): Contracts\Renderer
    {
        return new Renderers\BootstrapRenderer($this->container->make(Factory::class));
    }

    /**
     * Creates a Tailwind CSS renderer.
     */
    protected function createTailwindDriver(): Contracts\Renderer
    {
        return new Renderers\TailwindRenderer($this->container->make(Factory::class));
    }
}
