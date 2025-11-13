<?php

namespace Tests\Fixtures;

use Laragear\Alerts\Alert;

class TestAlertWithDefaults extends Alert
{
    protected function getDefaults(): array
    {
        return [
            'icon' => 'check',
            'color' => 'gray',
        ];
    }

    public function body(string $body)
    {
        $this->body = $body;

        return $this;
    }
}
