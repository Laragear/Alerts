<?php /** @noinspection JsonEncodingApiUsageInspection */

namespace Tests;

use BadMethodCallException;
use Laragear\Alerts\Bag;

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
        $alert = $this->bag->new();

        static::assertSame($alert, $this->bag->collect()[0]);
    }

    public function test_abandons_persisted_alert(): void
    {
        $this->bag->new()->persistAs('foo');

        static::assertCount(1, $this->bag->collect());

        static::assertFalse($this->bag->abandon('bar'));
        static::assertTrue($this->bag->abandon('foo'));
        static::assertFalse($this->bag->abandon('foo'));

        static::assertEmpty($this->bag->collect());
    }

    public function test_flushes_all_alerts(): void
    {
        $this->bag->new()->persistAs('foo');
        $this->bag->new();

        static::assertCount(2, $this->bag->collect());

        $this->bag->flush();

        static::assertEmpty($this->bag->collect());
    }

    public function test_check_has_persistent(): void
    {
        $this->bag->new()->persistAs('foo');
        $this->bag->new();

        static::assertTrue($this->bag->hasPersistent('foo'));
        static::assertFalse($this->bag->hasPersistent('bar'));
    }

    public function test_when_true_creates_alert(): void
    {
        $this->bag->when(true)->message('foo')->types('bar');

        static::assertCount(1, $this->bag->collect());
    }

    public function test_when_false_creates_empty_alert(): void
    {
        $this->bag->when(false)->message('foo')->types('bar');

        static::assertEmpty($this->bag->collect());
    }

    public function test_unless_false_creates_alert(): void
    {
        $this->bag->unless(false)->message('foo')->types('bar');

        static::assertCount(1, $this->bag->collect());
    }

    public function test_unless_true_creates_empty_alert(): void
    {
        $this->bag->unless(true)->message('foo')->types('bar');

        static::assertEmpty($this->bag->collect());
    }

    public function test_adds_json_alert(): void
    {
        $alert = $this->bag->fromJson(
            json_encode(
                [
                    'message' => 'foo',
                    'types' => ['bar', 'baz'],
                    'dismissible' => true,
                ]
            )
        );

        static::assertSame('foo', $alert->getMessage());
        static::assertSame(['bar', 'baz'], $alert->getTypes());
        static::assertTrue($alert->isDismissible());

        static::assertCount(1, $this->bag->collect());
    }

    public function test_exception_if_method_macro_doesnt_exists(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method Laragear\Alerts\Alert::nonexistent does not exist.');

        $this->bag->nonexistent('foo');
    }
}
