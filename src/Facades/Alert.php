<?php

namespace Laragear\Alerts\Facades;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Facade;
use Laragear\Alerts\Bag;
use Laragear\Alerts\Testing\Fakes\BagFake;

/**
 * @method static \Illuminate\Support\Collection<int, \Laragear\Alerts\Alert> collect()
 * @method static \Laragear\Alerts\Alert persistAs(string $key)
 * @method static bool abandon(string $key)
 * @method static bool hasPersistent(string $key)
 * @method static void flush()
 * @method static \Laragear\Alerts\Alert message(string $message)
 * @method static \Laragear\Alerts\Alert raw(string $message)
 * @method static \Laragear\Alerts\Alert trans(string $key, array $replace = [], string $locale = null)
 * @method static \Laragear\Alerts\Alert transChoice(string $key, \Countable|int|array $number, array $replace = [], string $locale = null)
 * @method static \Laragear\Alerts\Alert types(string ...$types)
 * @method static \Laragear\Alerts\Alert dismiss(bool $dismissible = true)
 * @method static \Laragear\Alerts\Alert when(Closure|bool $condition)
 * @method static \Laragear\Alerts\Alert unless(Closure|bool $condition)
 * @method static \Laragear\Alerts\Alert away(string $replace, string $url, bool $blank = true)
 * @method static \Laragear\Alerts\Alert to(string $replace, string $url, bool $blank = false)
 * @method static \Laragear\Alerts\Alert route(string $replace, string $name, array $parameters = [], bool $blank = false)
 * @method static \Laragear\Alerts\Alert action(string $replace, string|array $action, array $parameters = [], bool $blank = false)
 * @method static \Laragear\Alerts\Alert tags(string ...$tags)
 * @method static \Laragear\Alerts\Alert fromJson(string $alert, int $options = 0)
 *
 * @method static \Laragear\Alerts\Bag getFacadeRoot()
 */
class Alert extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return Bag::class;
    }

    /**
     * Creates a fake Alert Bag.
     *
     * @return \Laragear\Alerts\Testing\Fakes\BagFake
     */
    public static function fake(): BagFake
    {
        $fake = static::getFacadeApplication()->make(BagFake::class, [
            'tags' => Arr::wrap(Config::get('alerts.tags'))
        ]);

        static::swap($fake);

        return $fake;
    }
}
