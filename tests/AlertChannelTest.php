<?php

namespace Tests;

use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Laragear\Alerts\Alert;
use Laragear\Alerts\AlertChannel;
use Laragear\Alerts\Bag;
use Mockery;
use PHPUnit\Framework\TestCase as PHPUnit;
use RuntimeException;
use Tests\Fixtures\AlertNotification;
use TypeError;

class AlertChannelTest extends PHPUnit
{

    protected Bag|Mockery\MockInterface $bag;

    protected function setUp(): void
    {
        $this->bag = Mockery::mock(Bag::class);
        AlertNotification::$return = null;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        AlertNotification::$return = null;
    }

    public function test_throws_when_outside_request_lifecycle(): void
    {
        $channel = new AlertChannel($this->bag, null);

        $this->bag->expects('add')->never();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot set an alert notification outside a request lifecycle.');

        $channel->send((object) [], new Notification());
    }

    public function test_throws_when_not_alert(): void
    {
        $channel = new AlertChannel($this->bag, new Request);

        $this->bag->expects('add')->never();

        AlertNotification::$return = ['test'];

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('The toAlert() method must return a Laragear\Alert\Alert instance, array issued.');

        $channel->send((object) [], new AlertNotification());
    }

    public function test_throws_when_alert_already_added_to_bag(): void
    {
        $channel = new AlertChannel($this->bag, new Request);

        $this->bag->expects('add')->never();

        AlertNotification::$return = new class extends Alert {
            protected int $index = 0;
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Alert #0 is already set in the bag.');

        $channel->send((object) [], new AlertNotification());
    }

    public function test_pushes_alert(): void
    {
        $this->expectNotToPerformAssertions();

        $channel = new AlertChannel($this->bag, new Request);

        AlertNotification::$return = new class extends Alert {

        };

        $this->bag->expects('add')->with(AlertNotification::$return);

        $channel->send((object) [], new AlertNotification());
    }
}
