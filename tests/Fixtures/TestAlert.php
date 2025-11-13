<?php

namespace Tests\Fixtures;

use Laragear\Alerts\Alert;

class TestAlert extends Alert
{
    public $callback;

    public function body(string $body)
    {
        $this->body = $body;

        return $this;
    }
}
