<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Renderer
    |--------------------------------------------------------------------------
    |
    | When an Alert is rendered into HTML, it uses a "render" which transforms
    | the Alert into HTML code for a given frontend framework. By default, it
    | uses "Bootstrap 5", but you can change it or create your own renderer.
    |
    */

    'default' => 'bootstrap',

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

    /*
    |--------------------------------------------------------------------------
    | Default tags
    |--------------------------------------------------------------------------
    |
    | Alerts support tagging, meanining you can filter which alerts to present
    | in your frontend by a name, like "global" or "admin". This contains the
    | default tags all Alerts made in your application will have by default.
    |
    | Supported: "array", "string".
    |
    */

    'tags' => 'default',
];
