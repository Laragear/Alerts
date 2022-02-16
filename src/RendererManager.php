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
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('alerts.default');
    }

    /**
     * Creates a Bootstrap renderer.
     *
     * @return \Laragear\Alerts\Contracts\Renderer
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createBootstrapDriver(): Contracts\Renderer
    {
        return new Renderers\BootstrapRenderer($this->container->make(Factory::class));
    }
}
