<?php

namespace Laragear\Alerts\Renderers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Laragear\Alerts\Contracts\Renderer;

class BootstrapRenderer implements Renderer
{
    use CompilesAlert;

    /**
     * View file for Bootstrap Alerts.
     *
     * @var string
     */
    protected const VIEW = 'alerts::bootstrap.container';

    /**
     * Class translation table for known types.
     *
     * @var array<string, string|array<int, string>>
     */
    protected const TYPE_CLASSES = [
        'primary'   => 'alert-primary',
        'secondary' => 'alert-secondary',
        'success'   => 'alert-success',
        'danger'    => 'alert-danger',
        'warning'   => 'alert-warning',
        'info'      => 'alert-info',
        'light'     => 'alert-light',
        'dark'      => 'alert-dark',
    ];

    /**
     * Classes that should be added when dismissing the alert.
     *
     * @var array|string[]
     */
    protected const DISMISS_CLASSES = ['fade', 'show', 'alert-dismissible'];

    /**
     * Bootstrap Renderer constructor.
     *
     * @param  \Illuminate\Contracts\View\Factory  $factory
     */
    public function __construct(protected Factory $factory)
    {
        //
    }

    /**
     * Returns the rendered alerts as a single HTML string.
     *
     * @param  \Illuminate\Support\Collection<int, \Laragear\Alerts\Alert>  $alerts
     * @return string
     */
    public function render(Collection $alerts): string
    {
        return $this->factory
            ->make(static::VIEW)
            ->with('alerts', $alerts->map([$this, 'compileAlert']))->render();
    }
}
