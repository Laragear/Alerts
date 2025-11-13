<?php

namespace Tests\Fixtures;

use Laragear\Alerts\Alert;

class TestAlertWithView extends Alert
{
    public $callback;

    public function body(string $body)
    {
        $this->body = $body;

        return $this;
    }

    public function toHtml(): mixed
    {
        return isset($this->callback) ? ($this->callback)() : 'itrenders';
    }
}
