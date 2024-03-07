<?php

namespace Laragear\Alerts;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;

use function array_key_last;
use function is_iterable;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * @mixin \Laragear\Alerts\Alert
 */
class Bag
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The underlying collection of alerts.
     *
     * @var \Illuminate\Support\Collection<int, \Laragear\Alerts\Alert>
     */
    protected Collection $alerts;

    /**
     * Create a new Bag instance.
     *
     * @param  string[]  $tags
     * @param  string[]  $persisted
     */
    public function __construct(protected array $tags, protected array $persisted = [])
    {
        $this->alerts = new Collection;
    }

    /**
     * Returns all a key-index map of all persisted alerts.
     *
     * @return string[]
     */
    public function getPersisted(): array
    {
        return $this->persisted;
    }

    /**
     * Returns the default list of tags injected in each Alert.
     *
     * @return string[]
     */
    public function getDefaultTags(): array
    {
        return $this->tags;
    }

    /**
     * Creates a new Alert into this Bag instance.
     */
    public function new(): Alert
    {
        $this->add($alert = new Alert(bag: $this, tags: $this->tags));

        return $alert;
    }

    /**
     * Adds an Alert into the bag.
     *
     * @return $this
     */
    public function add(Alert|iterable $alert): static
    {
        if (! is_iterable($alert)) {
            $alert = [$alert];
        }

        foreach ($alert as $item) {
            $this->alerts->push($item);

            $item->index = array_key_last($this->alerts->all());

            // The method is also used to put alerts from the session. Because
            // of that, we will check if it already has a persistent key and,
            // if it has one, we will add it to the internal map of alerts.
            if ($key = $item->getPersistKey()) {
                $this->persisted[$key] = $item->index;
            }

            $item->setBag($this);
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
            $this->alerts->forget($index);
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

    /**
     * Creates an Alert only if the condition evaluates to true.
     *
     * @param  \Closure|bool  $condition
     * @return \Laragear\Alerts\Alert
     */
    public function when(Closure|bool $condition): Alert
    {
        return value($condition, $this) ? $this->new() : new BogusAlert($this);
    }

    /**
     * Creates an Alert only if the condition evaluates to false.
     *
     * @param  \Closure|bool  $condition
     * @return \Laragear\Alerts\Alert
     */
    public function unless(Closure|bool $condition): Alert
    {
        return ! value($condition, $this) ? $this->new() : new BogusAlert($this);
    }

    /**
     * Adds an Alert into the bag from a JSON string.
     */
    public function fromJson(string $alert, int $options = 0): Alert
    {
        $this->add($instance = Alert::fromArray($this, json_decode($alert, true, 512, $options | JSON_THROW_ON_ERROR)));

        return $instance;
    }

    /**
     * Pass through all calls to a new Alert.
     *
     * @codeCoverageIgnore
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->new()->{$method}(...$parameters);
    }
}
