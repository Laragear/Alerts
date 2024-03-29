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
    public function __construct(protected Bag $bag, protected string $key)
    {
        //
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->hasSession() && $request->session()->isStarted()) {
            $this->sessionAlertsToBag($request->session());

            $response = $next($request);

            $this->bagAlertsToSession($request->session(), $response->isRedirection());

            return $response;
        }

        return $next($request);
    }

    /**
     * Takes the existing alerts in the session and adds them to the bag.
     */
    protected function sessionAlertsToBag(SessionContract $session): void
    {
        // Retrieve both persistent and non-persistent alerts and add them.
        $this->bag->add(
            array_merge(
                $session->get("$this->key.persistent", []),
                $session->get("$this->key.alerts", []),
            )
        );

        // Remove the alerts from the session so these don't duplicate when re-adding them.
        $session->forget($this->key);
    }

    /**
     * Move the alerts back to the session.
     */
    protected function bagAlertsToSession(SessionContract $session, bool $isRedirection): void
    {
        [$persistent, $nonPersistent] = $this->bag->collect()
            ->partition(function (Alert $alert): bool {
                return in_array($alert->index, $this->bag->getPersisted(), true);
            });

        // Persistent keys will be put persistently into the session.
        if ($persistent->isNotEmpty()) {
            $session->put("$this->key.persistent", $persistent->all());
        }

        // Non-persistent will be flashed because the response can be a redirection.
        // This way we allow the next response from the app to have these alerts
        // alive for rendering without having to manually flash them after.
        if ($isRedirection && $nonPersistent->isNotEmpty()) {
            // @phpstan-ignore-next-line
            $session->flash("$this->key.alerts", $nonPersistent->all());
        }

        $this->bag->flush();
    }
}
