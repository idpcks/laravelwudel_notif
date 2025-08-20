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
use LaravelWudel\LaravelWudelNotif\Helpers\ModelHelper;

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

            // Publish models (commented out to avoid namespace conflicts)
            // $this->publishes([
            //     __DIR__.'/../src/Models/' => app_path('Models'),
            // ], 'laravelwudel-notif-models');

            // Register commands
            $this->commands([
                GenerateVapidKeysCommand::class,
                CleanupSubscriptionsCommand::class,
                PublishAssetsCommand::class,
                UninstallCommand::class,
                EmergencyCleanupCommand::class,
            ]);
        }

        // Ensure models are accessible
        $this->ensureModelAccessibility();

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravelwudel-notif');
    }

    /**
     * Ensure model accessibility and prevent namespace conflicts
     */
    protected function ensureModelAccessibility(): void
    {
        // Bind the model to the container with the correct namespace
        $this->app->bind('laravelwudel-notif.push-subscription-model', function () {
            return ModelHelper::getPushSubscriptionModel();
        });

        // Add a facade accessor for the model
        if (!class_exists('PushSubscriptionModel')) {
            class_alias(
                \LaravelWudel\LaravelWudelNotif\Models\PushSubscription::class,
                'PushSubscriptionModel'
            );
        }
    }
}
