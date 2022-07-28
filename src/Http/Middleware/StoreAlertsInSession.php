<?php

namespace Laragear\Alerts\Http\Middleware;

use function array_merge;
use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use function in_array;
use Laragear\Alerts\Alert;
use Laragear\Alerts\Bag;

class StoreAlertsInSession
{
    /**
     * Create a new middleware instance.
     *
     * @param  \Laragear\Alerts\Bag  $bag
     * @param  string  $key
     */
    public function __construct(protected Bag $bag, protected string $key)
    {
        //
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
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
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @return void
     */
    protected function sessionAlertsToBag(Session $session): void
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
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @param  bool  $isRedirection
     * @return void
     */
    protected function bagAlertsToSession(Session $session, bool $isRedirection): void
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
