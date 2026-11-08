<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();
        if (! $app->environment('testing') || $app['config']->get('database.default') !== 'sqlite' || $app['config']->get('database.connections.sqlite.database') !== ':memory:' || ! empty($app['config']->get('database.connections.sqlite.url'))) {
            throw new \RuntimeException('Tests require an isolated SQLite :memory: database. Refusing to run database tests.');
        }

        return $app;
    }
}
