<?php

namespace Tests;

use Laragear\Alerts\Alert;
use Laragear\Alerts\Bag;

class HelpersTest extends TestCase
{
    public function test_resolves_alert_factory(): void
    {
        static::assertInstanceOf(Bag::class, alert());
    }

    public function test_creates_alert(): void
    {
        $alert = alert('test-message');

        static::assertInstanceOf(Alert::class, $alert);
        static::assertEquals([
            'message' => 'test-message',
            'types' => [],
            'dismissible' => false,
        ], $alert->toArray());
    }

    public function test_creates_alert_with_types(): void
    {
        $alert = alert('test-message', 'info');

        static::assertInstanceOf(Alert::class, $alert);
        static::assertEquals([
            'message' => 'test-message',
            'types' => ['info'],
            'dismissible' => false,
        ], $alert->toArray());
    }
}
