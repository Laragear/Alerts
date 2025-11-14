<?php

namespace Tests;

use Inertia\ServiceProvider as InertiaServiceProvider;
use Laragear\Alerts\AlertsServiceProvider;
use Laragear\Alerts\Facades\Alert;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageAliases($app): array
    {
        return [
            'Alert' => Alert::class,
        ];
    }

    protected function getPackageProviders($app): array
    {
        return [AlertsServiceProvider::class, InertiaServiceProvider::class];
    }
}
