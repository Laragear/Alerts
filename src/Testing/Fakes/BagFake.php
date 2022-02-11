<?php

namespace Laragear\Alerts\Testing\Fakes;

use Illuminate\Support\Collection;
use JetBrains\PhpStorm\Pure;
use Laragear\Alerts\Bag;
use Laragear\Alerts\Testing\Builder;

class BagFake extends Bag
{
    /**
     * Alerts that should be flushed
     *
     * @var \Illuminate\Support\Collection<int, \Laragear\Alerts\Alert>
     */
    public Collection $added;

    /**
     * @inheritDoc
     */
    public function __construct(array $tags, array $persisted = [])
    {
        parent::__construct($tags, $persisted);

        $this->added = $this->alerts;
    }

    /**
     * Finds an alert by a given key.
     *
     * @return \Laragear\Alerts\Testing\Builder
     */
    #[Pure]
    public function assertAlert(): Builder
    {
        return new Builder($this);
    }

    /**
     * Assert that the alert bag has no alerts.
     *
     * @return void
     */
    public function assertEmpty(): void
    {
        $this->assertAlert()->missing("Failed to assert that there is no alerts.");
    }

    /**
     * Assert that the alert bag has any alert.
     *
     * @return void
     */
    public function assertNotEmpty(): void
    {
        $this->assertAlert()->exists("Failed to assert that there is any alert.");
    }

    /**
     * Assert the alert bag contains exactly one alert.
     *
     * @return void
     */
    public function assertHasOne(): void
    {
        $this->assertAlert()->unique();
    }

    /**
     * Assert the alert bag contains exactly the given number of alerts.
     *
     * @param  int  $count
     * @return void
     */
    public function assertHas(int $count): void
    {
        $this->assertAlert()->count($count);
    }

    /**
     * Assert the alert bag contains an alert persisted by the given key.
     *
     * @param  string  $key
     * @return void
     */
    public function assertPersisted(string $key): void
    {
        $this->assertAlert()->persistedAs($key);
    }

    /**
     * Assert the alert bag contains persistent alerts.
     *
     * @return void
     */
    public function assertHasPersistent(): void
    {
        $this->assertAlert()->persisted()->exists("Failed to assert that there is any persistent alert.");
    }

    /**
     * Assert the alert bag doesn't contain persistent alerts.
     *
     * @return void
     */
    public function assertHasNoPersistent(): void
    {
        $this->assertAlert()->persisted()->missing("Failed to assert that there is no persistent alerts.");
    }
}
