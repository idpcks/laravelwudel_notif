<?php

namespace LaravelWudel\LaravelWudelNotif\Helpers;

use LaravelWudel\LaravelWudelNotif\Models\PushSubscription;

class ModelHelper
{
    /**
     * Get the PushSubscription model class
     *
     * @return string
     */
    public static function getPushSubscriptionModel(): string
    {
        return PushSubscription::class;
    }

    /**
     * Create a new PushSubscription model instance
     *
     * @return PushSubscription
     */
    public static function createPushSubscriptionModel(): PushSubscription
    {
        return new PushSubscription();
    }

    /**
     * Get the table name for push subscriptions
     *
     * @return string
     */
    public static function getPushSubscriptionTable(): string
    {
        return config('laravelwudel-notif.database.table', 'push_subscriptions');
    }

    /**
     * Check if a model class exists and is accessible
     *
     * @param string $className
     * @return bool
     */
    public static function modelExists(string $className): bool
    {
        return class_exists($className) && is_subclass_of($className, \Illuminate\Database\Eloquent\Model::class);
    }
}
