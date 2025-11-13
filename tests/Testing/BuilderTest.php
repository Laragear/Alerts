<?php

namespace Tests\Testing;

use Laragear\Alerts\Facades\Alert;
use Laragear\Alerts\Testing\Fakes\BagFake;
use PHPUnit\Framework\AssertionFailedError;
use Tests\Fixtures\TestAlert;
use Tests\TestCase;

class BuilderTest extends TestCase
{
    protected BagFake $bag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bag = Alert::fake();
    }

    public function test_exists(): void
    {
        TestAlert::push();

        $this->bag->assertAlert()->exists();
    }

    public function test_exists_fails(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that at least one alert matches the expectations.\nFailed asserting that an object is not empty.");

        $this->bag->assertAlert()->exists();
    }

    public function test_missing(): void
    {
        $this->bag->assertAlert()->missing();
    }

    public function test_missing_fails(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that no alert matches the expectations.\nFailed asserting that an object is empty.");

        TestAlert::push();

        $this->bag->assertAlert()->missing();
    }

    public function test_unique(): void
    {
        TestAlert::push();

        $this->bag->assertAlert()->unique();
    }

    public function test_unique_fails(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that there is only one alert.\nFailed asserting that actual size 2 matches expected size 1.");

        TestAlert::push();
        TestAlert::push();

        $this->bag->assertAlert()->unique();
    }

    public function test_count(): void
    {
        TestAlert::push();
        TestAlert::push();

        $this->bag->assertAlert()->count(2);
    }

    public function test_count_fails(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that [2] alerts match the expected [3] count.\nFailed asserting that actual size 2 matches expected size 3.");

        TestAlert::push();
        TestAlert::push();

        $this->bag->assertAlert()->count(3);
    }

    public function test_with(): void
    {
        TestAlert::push(['foo' => 'bar']);
        TestAlert::push(['foo' => 'bar']);
        TestAlert::push(['baz' => 'qux']);

        $this->bag->assertAlert()->with('baz', 'qux')->unique();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that [1] alerts match the expected [2] count.\nFailed asserting that actual size 1 matches expected size 2.");

        $this->bag->assertAlert()->with('baz', 'qux')->count(2);
    }

    public function test_with_callback(): void
    {
        TestAlert::push(['foo' => 'bar']);
        TestAlert::push(['baz' => 'qux']);

        $this->bag->assertAlert()->with(fn($alert) => $alert->baz === 'qux')->unique();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that [1] alerts match the expected [2] count.\nFailed asserting that actual size 1 matches expected size 2.");

        $this->bag->assertAlert()->with(fn($alert) => $alert->baz === 'qux')->count(2);
    }

    public function test_filters_by_persisted(): void
    {
        TestAlert::push()->persistAs('bar');

        $this->bag->assertAlert()->persisted()->unique();
    }

    public function test_filters_by_persisted_fails(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that there is only one alert.\nFailed asserting that actual size 0 matches expected size 1.");

        TestAlert::push();

        $this->bag->assertAlert()->persisted()->unique();
    }

    public function test_filters_by_not_persisted(): void
    {
        TestAlert::push();

        $this->bag->assertAlert()->notPersisted()->unique();
    }

    public function test_filters_by_not_persisted_fails(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that there is only one alert.\nFailed asserting that actual size 0 matches expected size 1.");

        TestAlert::push()->persistAs('bar')->persistAs('foo');

        $this->bag->assertAlert()->notPersisted()->unique();
    }

    public function test_filters_by_persisted_as(): void
    {
        TestAlert::push()->persistAs('bar');

        $this->bag->assertAlert()->persistedAs('bar');
    }

    public function test_filters_by_persisted_as_fails(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that [1] persistent alerts exist.\nFailed asserting that actual size 0 matches expected size 1.");

        TestAlert::push()->persistAs('bar');

        $this->bag->assertAlert()->persistedAs('foo');
    }

    public function test_filters_by_persisted_as_array(): void
    {
         TestAlert::push()->persistAs('foo');
         TestAlert::push()->persistAs('bar');
         TestAlert::push()->persistAs('quz');

        $this->bag->assertAlert()->persistedAs('bar', 'quz');
    }

    public function test_filters_by_persisted_as_array_fails(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that [2] persistent alerts exist.\nFailed asserting that actual size 0 matches expected size 2.");

         TestAlert::push()->persistAs('foo');
         TestAlert::push()->persistAs('bar');
         TestAlert::push()->persistAs('quz');

        $this->bag->assertAlert()->persistedAs('qux', 'quuz');
    }
}
