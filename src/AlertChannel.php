<?php

namespace Laragear\Alerts;

use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use RuntimeException;
use TypeError;

use function get_class;
use function gettype;
use function is_object;
use function vsprintf;

class AlertChannel
{
    /**
     * Create a new Alert Notification instance.
     */
    public function __construct(protected Bag $bag, protected ?Request $request)
    {
        //
    }

    /**
     * Send the given notification as an Alert.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        // If we're outside the request lifecycle, do nothing.
        if (! $this->request) {
            throw new RuntimeException('Cannot set an alert notification outside a request lifecycle.');
        }

        $alert = $notification->toAlert($notifiable); // @phpstan-ignore-line

        if (! $alert instanceof Alert) {
            throw new TypeError(
                vsprintf('The toAlert() method must return a Laragear\Alert\Alert instance, %s issued.', [
                    is_object($alert) ? get_class($alert) : gettype($alert),
                ])
            );
        }

        if ($alert->hasIndex()) {
            throw new RuntimeException('Alert #'.$alert->getIndex().' is already set in the bag.');
        }

        $this->bag->add($alert);
    }
}
