<?php

namespace Laragear\Alerts;

use Countable;

/**
 * @internal  This is only a bogus alert that does nothing, and it's not added to the Alert bag.
 *
 * @codeCoverageIgnore
 */
class BogusAlert extends Alert
{
    /**
     * Sets a safely-escaped message.
     *
     * @return $this
     */
    public function message(string $message): static
    {
        return $this;
    }

    /**
     * Sets a raw, non-escaped, message.
     *
     * @return $this
     */
    public function raw(string $message): static
    {
        return $this;
    }

    /**
     * Set a localized message into the Alert.
     *
     * @return $this
     */
    public function trans(string $key, array $replace = [], string $locale = null): static
    {
        return $this;
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
        return $this;
    }

    /**
     * Sets one or many types for this alert.
     *
     * @return $this
     */
    public function types(string ...$types): static
    {
        return $this;
    }

    /**
     * Sets the Alert as dismissible.
     *
     * @return $this
     */
    public function dismiss(bool $dismissible = true): static
    {
        return $this;
    }

    /**
     * Persists the key into the session, forever.
     *
     * @return $this
     */
    public function persistAs(string $key): static
    {
        return $this;
    }

    /**
     * Adds an external link that should be replaced before rendering the Alert.
     *
     * @return $this
     */
    public function away(string $replace, string $url, bool $blank = true): static
    {
        return $this;
    }

    /**
     * Adds a link that should be replaced before rendering the Alert.
     *
     * @return $this
     */
    public function to(string $replace, string $url, bool $blank = false): static
    {
        return $this;
    }

    /**
     * Adds a link to a route that should be replaced before rendering the Alert.
     *
     * @return $this
     */
    public function route(string $replace, string $name, array $parameters = [], bool $blank = false): static
    {
        return $this;
    }

    /**
     * Adds a link to an action that should be replaced before rendering the Alert.
     *
     * @return $this
     */
    public function action(string $replace, string|array $action, array $parameters = [], bool $blank = false): static
    {
        return $this;
    }

    /**
     * Tags the alert.
     *
     * @return $this
     */
    public function tag(string ...$tags): static
    {
        return $this;
    }
}
