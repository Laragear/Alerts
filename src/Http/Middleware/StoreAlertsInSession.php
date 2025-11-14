<?php

namespace Laragear\Alerts\Http\Middleware;

use Closure;
use Illuminate\Contracts\Session\Session as SessionContract;
use Illuminate\Http\Request;
use Laragear\Alerts\Alert;
use Laragear\Alerts\Bag;

use function array_merge;
use function in_array;

class StoreAlertsInSession
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(protected Bag $bag, protected string $key, protected bool $enabled)
    {
        //
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request):(\Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Inertia\Response)  $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->shouldSetAlertsIntoSession($request)) {
            $this->moveSessionAlertsToBag($request->session());

            $response = $next($request);

            $this->moveBagAlertsToSession($request->session(), $response->isRedirection());

            return $response;
        }

        return $next($request);
    }

    /**
     * Check if the alerts should be inserted into session or not.
     */
    protected function shouldSetAlertsIntoSession(Request $request): bool
    {
        return $this->enabled
            && $request->hasSession()
            && $request->session()->isStarted();
    }

    /**
     * Takes the existing alerts in the session and adds them to the bag.
     */
    protected function moveSessionAlertsToBag(SessionContract $session): void
    {
        // Pull both persistent and non-persistent alerts and add them.
        $this->bag->add(
            array_merge(
                $session->pull("$this->key.persistent", []),
                $session->pull("$this->key.alerts", []),
            )
        );
    }

    /**
     * Move the alerts back to the session.
     */
    protected function moveBagAlertsToSession(SessionContract $session, bool $isRedirection): void
    {
        [$persistent, $nonPersistent] = $this->bag->collect()
            ->partition(function (Alert $alert): bool {
                return in_array($alert->getIndex(), $this->bag->getPersisted(), true);
            });

        // Persistent keys will be put persistently into the session.
        if ($persistent->isNotEmpty()) {
            $session->put("$this->key.persistent", $persistent->all());
        }

        // Non-persistent will be flashed because the response can be a redirection.
        // This way we allow the next response from the app to have these alerts
        // alive for rendering without having to manually flash them after.
        if ($isRedirection && $nonPersistent->isNotEmpty()) {
            $session->flash("$this->key.alerts", $nonPersistent->all());
        }

        $this->bag->flush();
    }
}
