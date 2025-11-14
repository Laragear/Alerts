<?php

namespace Laragear\Alerts\Facades;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Facade;
use Laragear\Alerts\Bag;
use Laragear\Alerts\Testing\Fakes\BagFake;

/**
 * @method static array<string, int> getPersisted()
 * @method static static add(\Laragear\Alerts\Alert|iterable<\Laragear\Alerts\Alert> $alerts)
 * @method static \Illuminate\Support\Collection<int, \Laragear\Alerts\Alert> collect()
 * @method static static markPersisted(string $key, int $index)
 * @method static bool abandon(string $key)
 * @method static bool hasPersistent(string $key)
 * @method static void flush()
 * @method static \Laragear\Alerts\Bag getFacadeRoot()
 */
class Alert extends Facade
{
    /**
     * Get the registered name of the component.
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
            'tags' => Arr::wrap(Config::get('alerts.tags')),
        ]);

        static::swap($fake);

        return $fake;
    }
}
