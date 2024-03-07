<?php

namespace Laragear\Alerts;

use BadMethodCallException;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;
use Stringable;
use function is_array;
use function json_encode;
use function sort;
use function sprintf;
use function strcmp;
use function trans;
use function trim;
use function url;

class Alert implements Arrayable, Jsonable, JsonSerializable, Stringable
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The internal key of this Alert in the bag.
     *
     * @internal
     */
    public int $index;

    /**
     * Create a new Alert instance.
     *
     * @param  string[]  $types
     * @param  array<int, object{replace: string, url: string, blank: bool}>  $links
     * @param  string[]  $tags
     */
    final public function __construct(
        protected Bag $bag,
        protected ?string $persistKey = null,
        protected string $message = '',
        protected array $types = [],
        protected array $links = [],
        protected bool $dismissible = false,
        protected array $tags = [],
    ) {
        //
    }

    /**
     * Sets the Bag for the Alert.
     *
     * @return $this
     */
    public function setBag(Bag $bag): static
    {
        $this->bag = $bag;

        return $this;
    }

    /**
     * Returns the key used to persist the alert, if any.
     *
     * @internal
     */
    public function getPersistKey(): ?string
    {
        return $this->persistKey;
    }

    /**
     * Returns the message of the Alert.
     *
     * @internal
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns the types set for this Alert.
     *
     * @return string[]
     *
     * @internal
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Returns the links to replace in the message.
     *
     * @return array<int, object{replace: string, url: string, blank: bool}>
     *
     * @internal
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Check if the Alert should be dismissible.
     *
     * @internal
     */
    public function isDismissible(): bool
    {
        return $this->dismissible;
    }

    /**
     * Returns the tags of this Alert.
     *
     * @internal
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Check if the alert contains any of the given tags.
     *
     * @internal
     */
    public function hasAnyTag(string ...$tags): bool
    {
        foreach ($tags as $tag) {
            if (in_array($tag, $this->tags, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets a safely-escaped message.
     *
     * @return $this
     */
    public function message(string $message): static
    {
        return $this->raw(e($message));
    }

    /**
     * Sets a raw, non-escaped, message.
     *
     * @return $this
     */
    public function raw(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set a localized message into the Alert.
     *
     * @return $this
     */
    public function trans(string $key, array $replace = [], string $locale = null): static
    {
        return $this->raw(trans($key, $replace, $locale));
    }

    /**
     * Sets a localized pluralized message into the Alert.
     *
     * @return $this
     */
    public function transChoice(
        string $key,
        Countable|int|array $number,
        array $replace = [],
        string $locale = null
    ): static {
        return $this->raw(trans_choice($key, $number, $replace, $locale));
    }

    /**
     * Sets one or many types for this alert.
     *
     * @return $this
     */
    public function types(string ...$types): static
    {
        $this->types = $types;

        sort($this->types);

        return $this;
    }

    /**
     * Sets the Alert as dismissible.
     *
     * @return $this
     */
    public function dismiss(bool $dismissible = true): static
    {
        $this->dismissible = $dismissible;

        return $this;
    }

    /**
     * Persists the key into the session, forever.
     *
     * @return $this
     */
    public function persistAs(string $key): static
    {
        $this->persistKey = $key;

        $this->bag->markPersisted($key, $this->index);

        return $this;
    }

    /**
     * Abandons the Alert from persistence.
     *
     * @return $this
     */
    public function abandon(): static
    {
        $this->bag->abandon($this->persistKey);

        $this->persistKey = null;

        return $this;
    }

    /**
     * Adds an external link that should be replaced before rendering the Alert.
     *
     * @return $this
     */
    public function away(string $replace, string $url, bool $blank = true): static
    {
        $this->links[] = (object) [
            'replace' => trim($replace, '{}'),
            'url' => $url,
            'blank' => $blank,
        ];

        usort($this->links, static function (object $first, object $second): int {
            return strcmp($first->replace.$first->url, $second->replace.$second->url);
        });

        return $this;
    }

    /**
     * Adds a link that should be replaced before rendering the Alert.
     *
     * @return $this
     */
    public function to(string $replace, string $url, bool $blank = false): static
    {
        return $this->away($replace, url($url), $blank);
    }

    /**
     * Adds a link to a route that should be replaced before rendering the Alert.
     *
     * @return $this
     */
    public function route(string $replace, string $name, array $parameters = [], bool $blank = false): static
    {
        return $this->away($replace, url()->route($name, $parameters), $blank);
    }

    /**
     * Adds a link to an action that should be replaced before rendering the Alert.
     *
     * @return $this
     */
    public function action(string $replace, string|array $action, array $parameters = [], bool $blank = false): static
    {
        return $this->away($replace, url()->action($action, $parameters), $blank);
    }

    /**
     * Tags the alert.
     *
     * @return $this
     */
    public function tag(string ...$tags): static
    {
        $this->tags = $tags;

        sort($this->tags);

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array{message: string, types: string[], dismissible: bool}
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'types' => $this->types,
            'dismissible' => $this->dismissible,
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options | JSON_THROW_ON_ERROR);
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return array{message: string, types: string[], dismissible: bool }
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Returns the string representation of the Alert.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Serializes the Alert.
     *
     * @codeCoverageIgnore
     *
     * @return array{persist_key: string|null, message: string, types: array<string>, links: array<int, object{replace: string, url: string, blank: bool}>, dismissible: bool, tags: array<string>}
     */
    public function __serialize(): array
    {
        return [
            'persist_key' => $this->persistKey,
            'message' => $this->message,
            'types' => $this->types,
            'links' => $this->links,
            'dismissible' => $this->dismissible,
            'tags' => $this->tags,
        ];
    }

    /**
     * Unserializes the alert.
     *
     * @codeCoverageIgnore
     *
     * @param  array{persist_key: string|null, message: string, types: array<string>, links: array<int, object{replace: string, url: string, blank: bool}>, dismissible: bool, tags: array<string>}  $data
     */
    public function __unserialize(array $data): void
    {
        $this->persistKey = $data['persist_key'];
        $this->message = $data['message'];
        $this->types = $data['types'];
        $this->links = $data['links'];
        $this->dismissible = $data['dismissible'];
        $this->tags = $data['tags'];
    }

    /**
     * Dynamically handle calls to this alert instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     */
    public function __call($method, $parameters): static
    {
        // If the alert already has a macro with the same name of the method called,
        // we will pass it to the macro and call it a day. Otherwise, we will pass
        // the name as the alert unique type and the parameter 0 as the message.
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (count($parameters) < 2) {
            return $this->types(Str::snake($method, '-'))->message($parameters[0] ?? '');
        }

        throw new BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $method));
    }

    /**
     * Creates a new Alert from a Bag and an array.
     */
    public static function fromArray(Bag|array $bag, array $alert = null): Alert
    {
        if (is_array($bag)) {
            [$bag, $alert] = [app(Bag::class), $bag];
        }

        return new static($bag, null, $alert['message'], $alert['types'], [], $alert['dismissible'] ?? false);
    }
}
