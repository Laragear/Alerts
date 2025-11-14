<?php

namespace Laragear\Alerts\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\ResponseFactory as Inertia;
use Laragear\Alerts\Bag;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use function config;

class AddAlertsToInertia
{
    /**
     * The base signature of the middleware.
     *
     * @const  string
     */
    public const SIGNATURE = 'alerts.inertia';

    /**
     * Create a new Add Alerts To Inertia instance.
     */
    public function __construct(protected Inertia $inertia, protected Bag $alerts)
    {
        //
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $key = null): SymfonyResponse
    {
        $this->inertia->share($key ?? config('alerts.key'), $this->alerts->collect());

        return $next($request);
    }
}
