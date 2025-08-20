<?php

namespace LaravelWudel\LaravelWudelNotif\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * PushNotification Facade
 *
 * @method static int sendToUser(\Illuminate\Contracts\Auth\Authenticatable $user, string $title, string $message, array $data = [])
 * @method static int sendToAll(string $title, string $message, array $data = [])
 * @method static int sendToTopic(string $topic, string $title, string $message, array $data = [])
 * @method static array getVapidKeys()
 * @method static bool isValidSubscription(string $endpoint)
 *
 * @see \LaravelWudel\LaravelWudelNotif\Services\WebPushService
 */
class PushNotification extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravelwudel-notif';
    }
}
