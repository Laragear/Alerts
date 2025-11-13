<?php

namespace Tests\Testing\Fakes;

use Illuminate\Support\Facades\Route;
use Laragear\Alerts\Facades\Alert;
use PHPUnit\Framework\AssertionFailedError;
use Tests\Fixtures\TestAlert;
use Tests\TestCase;

class FakeTest extends TestCase
{
    public function test_fake_bag_keeps_alerts(): void
    {
        Route::get('test', static function (): void {
            TestAlert::push();
        })->middleware('web');

        $bag = Alert::fake();

        $this->get('test');

        static::assertEmpty($bag->collect());
        static::assertCount(1, $bag->added);
    }

    public function test_asserts_empty(): void
    {
        $bag = Alert::fake();

        $bag->assertEmpty();
    }

    public function test_asserts_empty_exception(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that there is no alerts.\nFailed asserting that an object is empty.");

        $bag = Alert::fake();

        TestAlert::push();

        $bag->assertEmpty();
    }

    public function test_asserts_not_empty(): void
    {
        $bag = Alert::fake();

        TestAlert::push();

        $bag->assertNotEmpty();
    }

    public function test_asserts_not_empty_exception(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that there is any alert.\nFailed asserting that an object is not empty.");
        $bag = Alert::fake();

        $bag->assertNotEmpty();
    }

    public function test_asserts_has_one(): void
    {
        $bag = Alert::fake();

        TestAlert::push();

        $bag->assertHasOne();
    }

    public function test_asserts_has_one_exception(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that there is only one alert.\nFailed asserting that actual size 2 matches expected size 1.");

        $bag = Alert::fake();

        TestAlert::push();
        TestAlert::push();

        $bag->assertHasOne();
    }

    public function test_asserts_has(): void
    {
        $bag = Alert::fake();

        TestAlert::push();
        TestAlert::push();
        TestAlert::push();

        $bag->assertHas(3);
    }

    public function test_asserts_has_exception(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that [3] alerts match the expected [2] count.\nFailed asserting that actual size 3 matches expected size 2.");

        $bag = Alert::fake();

        TestAlert::push();
        TestAlert::push();
        TestAlert::push();

        $bag->assertHas(2);
    }

    public function test_assert_persisted(): void
    {
        $bag = Alert::fake();

        TestAlert::push()->persistAs('bar');

        $bag->assertPersisted('bar');
    }

    public function test_assert_persisted_exception(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that [1] persistent alerts exist.\nFailed asserting that actual size 0 matches expected size 1.");

        $bag = Alert::fake();

        TestAlert::push();

        $bag->assertPersisted('bar');
    }

    public function test_assert_has_persistent(): void
    {
        $bag = Alert::fake();

        TestAlert::push()->persistAs('bar');
        TestAlert::push();

        $bag->assertHasPersistent();
    }

    public function test_assert_has_persistent_exception(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that there is any persistent alert.\nFailed asserting that an object is not empty.");

        $bag = Alert::fake();

        TestAlert::push();

        $bag->assertHasPersistent();
    }

    public function test_assert_has_no_persistent(): void
    {
        $bag = Alert::fake();

        TestAlert::push();

        $bag->assertHasNoPersistent();
    }

    public function test_assert_has_no_persistent_exception(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Failed to assert that there is no persistent alerts.\nFailed asserting that an object is empty.");

        $bag = Alert::fake();

        TestAlert::push()->persistAs('bar');

        $bag->assertHasNoPersistent();
    }
}
