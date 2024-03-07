<?php

namespace Laragear\Alerts\Renderers;

use Illuminate\Contracts\View\Factory as ViewContract;
use Illuminate\Support\Collection;
use Laragear\Alerts\Contracts\Renderer;

class TailwindRenderer implements Renderer
{
    use CompilesAlert;

    /**
     * View file for Bootstrap Alerts.
     */
    protected const VIEW = 'alerts::tailwind.container';

    /**
     * Class translation table for known types.
     *
     * @var array<string, string|string[]>
     */
    protected const TYPE_CLASSES = [
        'success' => ['bg-green-100',     'ring-green-500/20',    'text-green-900'],
        'failure' => ['bg-red-100',       'ring-red-500/20',      'text-red-900'],
        'warning' => ['bg-yellow-100',    'ring-yellow-500/20',   'text-yellow-900'],
        'info' => ['bg-blue-100',      'ring-blue-500/20',     'text-blue-900'],
        'light' => ['bg-white',         'ring-gray-900/5',      'text-gray-900'],
        'dark' => ['bg-gray-800',      'ring-white/10',        'text-gray-300'],
    ];

    /**
     * Classes that should be added when dismissing the alert.
     */
    protected const DISMISS_CLASSES = ['transition-opacity', 'opacity-100'];

    /**
     * Bootstrap Renderer constructor.
     */
    public function __construct(protected ViewContract $factory)
    {
        //
    }

    /**
     * Returns the rendered alerts as a single HTML string.
     *
     * @param  \Illuminate\Support\Collection<int, \Laragear\Alerts\Alert>  $alerts
     */
    public function render(Collection $alerts): string
    {
        return $this->factory
            ->make(static::VIEW)
            ->with('alerts', $alerts->map([$this, 'compileAlert']))->render();
    }
}
