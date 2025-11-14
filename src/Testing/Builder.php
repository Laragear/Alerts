<?php

namespace Laragear\Alerts\Testing;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Testing\Assert as PHPUnit;
use Laragear\Alerts\Alert;
use Laragear\Alerts\Testing\Fakes\BagFake;

use function in_array;
use function is_array;
use function is_string;

class Builder
{
    /**
     * Create a new expectation builder instance.
     *
     * @param  \Laragear\Alerts\Testing\Fakes\BagFake  $bag
     * @param  array  $expectations
     * @param  string[]|bool|null|string  $persisted
     */
    public function __construct(
        public BagFake $bag,
        public array $expectations = [],
        protected array|bool|null|string $persisted = null,
    ) {
        //
    }

    /**
     * Expect an alert with the raw message.
     *
     * @return $this
     */
    public function with(Closure|string $key, mixed $expectation = null): static
    {
        if (is_string($key)) {
            $this->expectations[$key] = $expectation;
        } else {
            $this->expectations[] = $key;
        }

        return $this;
    }

    /**
     * Expect an alert persisted.
     *
     * @return $this
     */
    public function persisted(): static
    {
        $this->persisted = true;

        return $this;
    }

    /**
     * Expect an alert not persisted.
     *
     * @return $this
     */
    public function notPersisted(): static
    {
        $this->persisted = false;

        return $this;
    }

    /**
     * Returns a collection of all matching alerts.
     *
     * @return \Illuminate\Support\Collection<int, \Laragear\Alerts\Alert>
     */
    protected function matches(): Collection
    {
        return $this->bag->added->filter($this->is(...));
    }

    /**
     * Check if the given alert matches the expectations.
     */
    protected function is(Alert $alert): bool
    {
        if ($this->persisted !== null) {
            if (is_string($this->persisted)) {
                return $this->persisted === $alert->getPersistenceKey();
            }

            if (is_array($this->persisted)) {
                return in_array($alert->getPersistenceKey(), $this->persisted, true);
            }

            return $this->persisted === (bool) $alert->getPersistenceKey();
        }

        foreach ($this->expectations as $key => $value) {
            if (is_string($key)) {
                if ($value !== $alert->get($key)) {
                    return false;
                }
            } elseif (! $value($alert)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Expect an alert persisted with the issued key.
     */
    public function persistedAs(string ...$key): void
    {
        $this->persisted = $key;

        $count = count($key);

        $this->count($count, "Failed to assert that [$count] persistent alerts exist.");
    }

    /**
     * Assert that at least one Alert exists with the given expectations.
     */
    public function exists(string $message = 'Failed to assert that at least one alert matches the expectations.'): void
    {
        PHPUnit::assertNotEmpty($this->matches(), $message);
    }

    /**
     * Assert that no Alert exists with the given expectations.
     */
    public function missing(string $message = 'Failed to assert that no alert matches the expectations.'): void
    {
        PHPUnit::assertEmpty($this->matches(), $message);
    }

    /**
     * Assert that only one Alert exists with the given expectations.
     */
    public function unique(string $message = 'Failed to assert that there is only one alert.'): void
    {
        $this->count(1, $message);
    }

    /**
     * Assert that the given number of Alerts matches exactly the given expectations.
     */
    public function count(int $count, ?string $message = null): void
    {
        $matches = $this->matches();

        PHPUnit::assertCount(
            $count, $matches,
            $message ?? "Failed to assert that [{$matches->count()}] alerts match the expected [$count] count."
        );
    }
}
