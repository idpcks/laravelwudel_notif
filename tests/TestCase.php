<?php

namespace LaravelWudel\LaravelWudelNotif\Tests;

use LaravelWudel\LaravelWudelNotif\LaravelWudelNotifServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelWudelNotifServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup VAPID keys for testing
        $app['config']->set('laravelwudel-notif.vapid.subject', 'mailto:test@example.com');
        $app['config']->set('laravelwudel-notif.vapid.public_key', 'test_public_key');
        $app['config']->set('laravelwudel-notif.vapid.private_key', 'test_private_key');
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
