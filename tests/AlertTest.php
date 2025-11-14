<?php

namespace Tests;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Illuminate\Support\Optional;
use Laragear\Alerts\Alert;
use Laragear\Alerts\Bag;
use Mockery;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Fixtures\TestAlert;
use Tests\Fixtures\TestAlertWithDefaults;

use function json_decode;
use function json_encode;
use function serialize;
use function unserialize;

class AlertTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Container::setInstance();

        TestAlert::flushMacros();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_instances_alert_with_attributes(): void
    {
        $alert = new TestAlert($attributes = ['foo' => 'bar', 'baz' => ['cuz']]);

        static::assertSame($attributes, $alert->all());
    }

    public function test_instances_alert_with_defaults(): void
    {
        $alert = new TestAlertWithDefaults(['foo' => 'bar', 'color' => 'red']);

        static::assertSame(['icon' => 'check', 'color' => 'red', 'foo' => 'bar'], $alert->all());
    }

    public function test_uses_custom_method(): void
    {
        $alert = new TestAlert(['foo' => 'bar', 'baz' => ['cuz']]);

        $alert->body('test-body');

        static::assertSame('test-body', $alert->body);
    }

    public function test_persist_as(): void
    {
        $mock = Mockery::mock(Bag::class);
        $mock->expects('markPersisted')->withArgs(function ($key, $index) {
            static::assertSame('test-persist', $key);
            static::assertSame(10, $index);

            return true;
        })->andReturnSelf();

        $alert = (new TestAlert())->setIndex(10)->setIndex(10)->setAlertBag($mock);

        $alert->persistAs('test-persist');
        static::assertSame('test-persist', $alert->getPersistenceKey());
    }

    public function test_abandon(): void
    {
        $mock = Mockery::mock(Bag::class);
        $mock->expects('abandon')->withArgs(function ($key) {
            static::assertSame('test-persist', $key);

            return true;
        })->andReturnTrue();

        $alert = (new TestAlert())->setIndex(10)->setIndex(10)->setAlertBag($mock);

        $alert->abandon('test-persist');

        static::assertNull($alert->getPersistenceKey());
    }

    public function test_fill_with_iterable(): void
    {
        $attributes = new Collection($values = ['foo' => 'bar', 'baz' => 'quz']);

        $alert = new TestAlert();

        $alert->fill($attributes);

        static::assertSame($values, $alert->all());
    }

    public function test_to_html_throws_by_default(): void
    {
        $alert = new TestAlert();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No view is assigned to render the [Tests\Fixtures\TestAlert] alert.');

        $alert->toHtml();
    }

    public function test_to_array(): void
    {
        $attributes = ['foo' => 'bar', 'baz' => 'quz'];

        $alert = new TestAlert($attributes);

        static::assertSame($attributes, $alert->toArray());
    }

    public function test_all_returns_all_keys(): void
    {
        $alert = new TestAlert(['foo' => 'bar', 'baz' => 'quz']);

        static::assertSame(['foo' => 'bar', 'baz' => 'quz'], $alert->all());
    }

    public function test_all_returns_some_keys(): void
    {
        $alert = new TestAlert(['foo' => 'bar', 'baz' => 'quz', 'qux' => 'doge']);

        static::assertSame(['qux' => 'doge', 'foo' => 'bar'], $alert->all('qux', 'foo'));
    }

    public function test_all_returns_one_key(): void
    {
        $alert = new TestAlert(['foo' => 'bar', 'baz' => 'quz', 'qux' => 'doge']);

        static::assertSame(['qux' => 'doge'], $alert->all('qux'));
    }

    public function test_all_uses_dot_notation(): void
    {
        $alert = new TestAlert(['foo' => 'bar', 'baz' => ['qux' => 'doge', 'cogar', 'asdfg']]);

        static::assertSame(['baz' => ['qux' => 'doge']], $alert->all('baz.qux'));
    }

    public function test_get(): void
    {
        $alert = new TestAlert(['foo' => 'bar', 'baz' => ['qux' => 'doge', 'cogar', 'asdfg']]);

        static::assertNull($alert->get('invalid'));
        static::assertSame('bar', $alert->get('invalid', 'bar'));
        static::assertSame('bar', $alert->get('invalid', fn () => 'bar'));
        static::assertSame('bar', $alert->get('foo'));
        static::assertSame('doge', $alert->get('baz.qux'));
    }

    public function test_set(): void
    {
        $alert = new TestAlert(['foo' => 'bar', 'baz' => ['qux' => 'doge', 'cogar' => 'asdfg']]);

        $alert->set('quz', 'doge');

        static::assertSame('doge', $alert->get('quz'));

        $alert->set('baz.foo', 'bar');

        static::assertSame('bar', $alert->get('baz.foo'));

        $alert->set('baz.qux', 'bar');

        static::assertSame('bar', $alert->get('baz.qux'));
    }

    public function test_to_json(): void
    {
        $alert = new TestAlert(['foo' => 'bar']);

        static::assertJson($alert->toJson());
        static::assertSame(['foo' => 'bar'], json_decode($alert->toJson(), true));
    }

    public function test_json_serialize(): void
    {
        $alert = new TestAlert(['foo' => 'bar']);

        static::assertJson(json_encode($alert));
        static::assertSame(['foo' => 'bar'], json_decode(json_encode($alert), true));
    }

    public function test_property_access(): void
    {
        $alert = new TestAlert(['foo' => 'bar']);

        static::assertFalse(isset($alert->bar));
        static::assertTrue(isset($alert->foo));

        static::assertNull($alert->bar);
        static::assertSame('bar', $alert->foo);

        $alert->bar = 'baz';

        static::assertSame('baz', $alert->bar);

        unset($alert->bar);

        static::assertNull($alert->bar);
    }

    public function test_serialization(): void
    {
        $alert = new TestAlert(['foo' => 'bar']);
        $alert->setPersistenceKey('test-alert')->setAlertBag(Mockery::mock(Bag::class));

        /** @var \Tests\Fixtures\TestAlert $alert */
        $alert = unserialize(serialize($alert));

        static::assertSame(['foo' => 'bar'], $alert->all());
        static::assertSame('test-alert', $alert->getPersistenceKey());
    }

    public function test_push(): void
    {
        Container::setInstance($container = new Container());
        $container->instance(Bag::class, $mock = Mockery::mock(Bag::class));
        $mock->expects('add')->withArgs(function (Alert $alert) use ($mock): true {
            $alert->setAlertBag($mock);

            return true;
        });

        $attributes = ['foo' => 'bar'];

        $alert = TestAlert::push($attributes);

        static::assertSame($attributes, $alert->all());
        static::assertSame($mock, $alert->getAlertBag());
    }

    public function test_push_when(): void
    {
        static::assertInstanceOf(Optional::class, TestAlert::pushWhen(false, ['foo' => 'bar']));
        static::assertInstanceOf(Optional::class, TestAlert::pushWhen(fn () => false, ['foo' => 'bar']));

        Container::setInstance($container = new Container());
        $container->instance(Bag::class, $mock = Mockery::mock(Bag::class));
        $mock->expects('add')->twice();

        static::assertInstanceOf(TestAlert::class, TestAlert::pushWhen(true, ['foo' => 'bar']));
        static::assertInstanceOf(TestAlert::class, TestAlert::pushWhen(fn () => true, ['foo' => 'bar']));
    }

    public function test_push_unless(): void
    {
        static::assertInstanceOf(Optional::class, TestAlert::pushUnless(true, ['foo' => 'bar']));
        static::assertInstanceOf(Optional::class, TestAlert::pushUnless(fn () => true, ['foo' => 'bar']));

        Container::setInstance($container = new Container());
        $container->instance(Bag::class, $mock = Mockery::mock(Bag::class));
        $mock->expects('add')->twice();

        static::assertInstanceOf(TestAlert::class, TestAlert::pushUnless(false, ['foo' => 'bar']));
        static::assertInstanceOf(TestAlert::class, TestAlert::pushUnless(fn () => false, ['foo' => 'bar']));
    }
}
