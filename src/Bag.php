<?php

namespace Laragear\Alerts;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use function array_key_last;
use function is_iterable;

class Bag
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * Create a new Bag instance.
     *
     * @param  array<string, int>  $persisted
     * @param  \Illuminate\Support\Collection<int, \Laragear\Alerts\Alert>  $alerts
     */
    public function __construct(protected array $persisted = [], protected Collection $alerts = new Collection)
    {
        //
    }

    /**
     * Returns all a key-index map of all persisted alerts.
     *
     * @return array<string, int>
     */
    public function getPersisted(): array
    {
        return $this->persisted;
    }

    /**
     * Adds an Alert into the bag.
     *
     * @return $this
     */
    public function add(Alert|iterable $alert): static
    {
        if (!is_iterable($alert)) {
            $alert = [$alert];
        }

        /** @var \Laragear\Alerts\Alert $item */
        foreach ($alert as $item) {
            $this->alerts->push($item);

            $item->setIndex($index = array_key_last($this->alerts->all()));

            // The method is also used to put alerts from the session. Because
            // of that, we will check if it already has a persistent key and,
            // if it has one, we will add it to the internal map of alerts.
            if ($key = $item->getPersistenceKey()) {
                $this->persisted[$key] = $index;
            }

            $item->setAlertBag($this);
        }

        return $this;
    }

    /**
     * Returns the underlying collection of alerts.
     *
     * @return \Illuminate\Support\Collection<int, \Laragear\Alerts\Alert>
     */
    public function collect(): Collection
    {
        return $this->alerts;
    }

    /**
     * Marks an existing Alert as persistent.
     *
     * @return $this
     */
    public function markPersisted(string $key, int $index): static
    {
        // Find if there is a key already for the persisted alert and replace it.
        $this->abandon($key);

        $this->persisted[$key] = $index;

        return $this;
    }

    /**
     * Abandons a persisted Alert.
     *
     * @return bool Returns true if successful.
     */
    public function abandon(string $key): bool
    {
        if (null !== $index = $this->whichPersistent($key)) {
            /** @noinspection PhpParamsInspection */
            $this->alerts->forget($index); // @phpstan-ignore-line
            unset($this->persisted[$key]);

            return true;
        }

        return false;
    }

    /**
     * Check if an Alert by the given key is persistent.
     */
    public function hasPersistent(string $key): bool
    {
        return null !== $this->whichPersistent($key);
    }

    /**
     * Locates the key of a persistent alert.
     */
    protected function whichPersistent(string $key): ?int
    {
        return $this->persisted[$key] ?? null;
    }

    /**
     * Deletes all alerts.
     */
    public function flush(): void
    {
        $this->alerts = new Collection();
    }
}
