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

        // Setup VAPID keys for testing (using valid format)
        $app['config']->set('laravelwudel-notif.vapid.subject', 'mailto:test@example.com');
        $app['config']->set('laravelwudel-notif.vapid.public_key', 'test_public_key_87_chars_long_for_testing_purposes_only_123456789');
        $app['config']->set('laravelwudel-notif.vapid.private_key', 'test_private_key_43_chars_long_for_testing_123456789');
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
