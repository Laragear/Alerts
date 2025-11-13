<?php

namespace Laragear\Alerts;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Dumpable;
use Illuminate\Support\Traits\InteractsWithData;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;
use JsonSerializable;
use RuntimeException;
use function app;
use function data_get;
use function data_set;
use function func_get_args;
use function is_array;
use function json_encode;
use function optional;
use function value;

abstract class Alert implements Arrayable, Jsonable, JsonSerializable, Htmlable
{
    use Conditionable, InteractsWithData, Macroable, Dumpable, Tappable;

    /**
     * The instance of the Alert bag.
     */
    protected Bag $alertBag;

    /**
     * The index of this Alert instance in the Bag.
     */
    protected int $index;

    /**
     * The key used to persist this alert.
     */
    protected ?string $persistenceKey = null;

    /**
     * The set of attributes for this Alert.
     */
    protected array $attributes = [];

    /**
     * Create a new Base Alert instance.
     */
    public function __construct(iterable $attributes = [])
    {
        $this->fill($this->getDefaults())->fill($attributes);
    }

    /**
     * Returns an array of default values.
     */
    protected function getDefaults(): array
    {
        return [
            //
        ];
    }

    /**
     * Persist the Alert between requests.
     */
    public function persistAs(string $key): static
    {
        $this->alertBag->markPersisted($this->persistenceKey = $key, $this->index);

        return $this;
    }

    /**
     * Abandons the Alert from being persisted between requests.
     */
    public function abandon(string $key): static
    {
        $this->alertBag->abandon($key);

        $this->persistenceKey = null;

        return $this;
    }

    /**
     * Fill the fluent instance with an array of attributes.
     *
     * @param  iterable<string, mixed>  $attributes
     * @return $this
     */
    public function fill(iterable $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toHtml(): mixed
    {
        throw new RuntimeException('No view is assigned to render the [' . static::class . '] alert.');
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->all();
    }

    /**
     * @inheritDoc
     */
    public function all($keys = null): array
    {
        $data = $this->data();

        if (! $keys) {
            return $data;
        }

        $results = [];

        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            Arr::set($results, $key, Arr::get($data, $key));
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    protected function data($key = null, $default = null): mixed
    {
        return $this->get($key, $default);
    }

    /**
     * Get an attribute from the Alert instance using "dot" notation.
     *
     * @template TGetDefault
     *
     * @param  string|null  $key
     * @param  TGetDefault|(\Closure(): TGetDefault)  $default
     * @return mixed|TGetDefault
     */
    public function get(string|null $key, mixed $default = null): mixed
    {
        return data_get($this->attributes, $key, $default);
    }

    /**
     * Set an attribute on the Alert instance using "dot" notation.
     *
     * @return $this
     */
    public function set(string $key, mixed $value): static
    {
        data_set($this->attributes, $key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Dynamically retrieve the value of an attribute.
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * Dynamically set the value of an attribute.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Dynamically check if an attribute is set.
     */
    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * Dynamically unset an attribute.
     */
    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }

    /**
     * Serializes the Alert.
     *
     * @codeCoverageIgnore
     *
     * @return  array{persistenceKey: string|null, index: int, attributes: mixed}
     */
    public function __serialize(): array
    {
        return [
            'persistenceKey' => $this->persistenceKey,
            'index' => $this->index,
            'attributes' => $this->attributes,
        ];
    }

    /**
     * Unserializes the alert.
     *
     * @codeCoverageIgnore
     *
     * @param  array{persistenceKey: string|null, index: int, attributes: mixed}  $data
     */
    public function __unserialize(array $data): void
    {
        [
            'persistenceKey' => $this->persistenceKey,
            'index' => $this->index,
            'attributes' => $this->attributes,
        ] = $data;
    }

    /**
     * Returns the Alert Bag instance.
     *
     * @internal
     */
    public function getAlertBag(): ?Bag
    {
        return $this->alertBag;
    }

    /**
     * Sets an Alert Bag instance into the Alert.
     *
     * @internal
     *
     * @return $this
     */
    public function setAlertBag(Bag $alertBag): static
    {
        $this->alertBag = $alertBag;

        return $this;
    }

    /**
     * Returns the key used to persist the Alert.
     *
     * @internal
     */
    public function getPersistenceKey(): ?string
    {
        return $this->persistenceKey;
    }

    /**
     * Sets the persistence key to persist the Alert.
     *
     * @internal
     *
     * @return $this
     */
    public function setPersistenceKey(?string $persistenceKey): static
    {
        $this->persistenceKey = $persistenceKey;

        return $this;
    }

    /**
     * Checks if the Alert was added into the Alert Bag.
     */
    public function hasIndex(): bool
    {
        return isset($this->index);
    }

    /**
     * Returns the internal index of this Alert in the Alert Bag.
     *
     * @internal
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * Sets the internal index of this Alert in the Alert Bag.
     *
     * @internal
     *
     * @return $this
     */
    public function setIndex(int $index): static
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Moves the Alert to the Bag, if it wasn't moved before.
     */
    public function pushToBag(): static
    {
        if (!isset($this->index)) {
            app(Bag::class)->add($this);
        }

        return $this;
    }

    /**
     * Creates a new Alert instance.
     */
    public static function make(iterable $attributes = []): static
    {
        return new static($attributes);
    }

    /**
     * Creates a new Alert instance and immediately adds it to the Alert bag.
     */
    public static function push(iterable $attributes = []): static
    {
        return static::make($attributes)->pushToBag();
    }

    /**
     * Creates an Alert only if the condition evaluates to true.
     *
     * @return \Laragear\Alerts\Alert
     */
    public static function pushWhen(Closure|bool $condition, iterable $attributes = []): mixed
    {
        return value($condition) ? static::push($attributes) : optional();
    }

    /**
     * Creates an Alert only if the condition evaluates to false.
     *
     * @return \Laragear\Alerts\Alert
     */
    public static function pushUnless(Closure|bool $condition, iterable $attributes = []): mixed
    {
        return ! value($condition) ? static::push($attributes) : optional();
    }
}
