<?php

namespace LaravelWudel\LaravelWudelNotif;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use LaravelWudel\LaravelWudelNotif\Services\WebPushService;
use LaravelWudel\LaravelWudelNotif\Console\Commands\GenerateVapidKeysCommand;
use LaravelWudel\LaravelWudelNotif\Console\Commands\CleanupSubscriptionsCommand;
use LaravelWudel\LaravelWudelNotif\Console\Commands\PublishAssetsCommand;
use LaravelWudel\LaravelWudelNotif\Console\Commands\UninstallCommand;
use LaravelWudel\LaravelWudelNotif\Console\Commands\EmergencyCleanupCommand;

class LaravelWudelNotifServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravelwudel-notif.php', 'laravelwudel-notif'
        );

        $this->app->singleton(WebPushService::class, function ($app) {
            return new WebPushService();
        });

        $this->app->alias(WebPushService::class, 'laravelwudel-notif');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/laravelwudel-notif.php' => config_path('laravelwudel-notif.php'),
            ], 'laravelwudel-notif-config');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'laravelwudel-notif-migrations');

            // Publish models
            $this->publishes([
                __DIR__.'/../src/Models/' => app_path('Models'),
            ], 'laravelwudel-notif-models');

            // Register commands
            $this->commands([
                GenerateVapidKeysCommand::class,
                CleanupSubscriptionsCommand::class,
                PublishAssetsCommand::class,
                UninstallCommand::class,
                EmergencyCleanupCommand::class,
            ]);
        }

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravelwudel-notif');
    }
}
