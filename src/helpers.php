<?php

use Laragear\Alerts\Alert;
use Laragear\Alerts\Bag;

if (! function_exists('alert')) {
    /**
     * Creates an Alert to render, or calls the Alert Bag without arguments.
     */
    function alert(string $message = null, string ...$types): Alert|Bag
    {
        $manager = app(Bag::class);

        if (! func_num_args()) {
            return $manager;
        }

        return $manager->new()->message($message)->types(...$types);
    }
}
