<?php

/** @noinspection JsonEncodingApiUsageInspection */

namespace Tests;

use Laragear\Alerts\Bag;
use Tests\Fixtures\TestAlert;

class BagTest extends TestCase
{
    protected Bag $bag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bag = $this->app[Bag::class];
    }

    public function test_adds_new_alert_to_bag(): void
    {
        $alert = TestAlert::push();

        static::assertSame($alert, $this->bag->collect()[0]);
    }

    public function test_abandons_persisted_alert(): void
    {
        TestAlert::push()->persistAs('foo');

        static::assertCount(1, $this->bag->collect());

        static::assertFalse($this->bag->abandon('bar'));
        static::assertTrue($this->bag->abandon('foo'));
        static::assertFalse($this->bag->abandon('foo'));

        static::assertEmpty($this->bag->collect());
    }

    public function test_flushes_all_alerts(): void
    {
        TestAlert::push()->persistAs('foo');
        TestAlert::push();

        static::assertCount(2, $this->bag->collect());

        $this->bag->flush();

        static::assertEmpty($this->bag->collect());
    }

    public function test_check_has_persistent(): void
    {
        TestAlert::push()->persistAs('foo');
        TestAlert::push();

        static::assertTrue($this->bag->hasPersistent('foo'));
        static::assertFalse($this->bag->hasPersistent('bar'));
    }
}
