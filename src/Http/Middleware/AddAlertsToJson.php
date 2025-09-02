<?php

namespace Laragear\Alerts\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laragear\Alerts\Bag;

use function config;
use function data_set;

class AddAlertsToJson
{
    /**
     * The base signature of the middleware.
     *
     * @const  string
     */
    public const SIGNATURE = 'alerts.json';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request):(\Illuminate\Http\Response|\Illuminate\Http\JsonResponse)  $next
     */
    public function handle(Request $request, Closure $next, ?string $key = null): JsonResponse|Response
    {
        $response = $next($request);

        if ($response instanceof JsonResponse && $response->isSuccessful()) {
            $key ??= config('alerts.key');

            $data = $response->getData();

            $response->setData(
                data_set($data, $key, app(Bag::class)->collect()->toArray())
            );
        }

        return $response;
    }
}
