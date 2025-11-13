<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Save alerts in the session
    |--------------------------------------------------------------------------
    |
    | Here you may disable setting alerts into the session if your app frontend
    | is detached from your backend. If that's the case, you should complement
    | this with one of the included middleware to add alerts to the response.
    |
    */

    'session' => true,

    /*
    |--------------------------------------------------------------------------
    | Session key
    |--------------------------------------------------------------------------
    |
    | For the Alerts to work, the bag containing them is registered inside the
    | Session store by an identifiable key. You may want to change this key
    | for any other in case it collides with a key you're already using.
    |
    */

    'key' => '_alerts',
];
