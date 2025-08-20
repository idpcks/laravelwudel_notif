<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VAPID Configuration
    |--------------------------------------------------------------------------
    |
    | VAPID (Voluntary Application Server Identification) keys are used for
    | authenticating push notification requests. These keys should be kept
    | secure and never exposed to the client.
    |
    */
    'vapid' => [
        'subject' => env('WEBPUSH_VAPID_SUBJECT', 'mailto:admin@example.com'),
        'public_key' => env('WEBPUSH_VAPID_PUBLIC_KEY'),
        'private_key' => env('WEBPUSH_VAPID_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings for push notifications including icons, badges,
    | and other visual elements.
    |
    */
    'notification' => [
        'icon' => env('WEBPUSH_ICON', '/favicon.ico'),
        'badge' => env('WEBPUSH_BADGE', '/favicon.ico'),
        'image' => env('WEBPUSH_IMAGE', null),
        'tag' => env('WEBPUSH_TAG', null),
        'require_interaction' => env('WEBPUSH_REQUIRE_INTERACTION', false),
        'silent' => env('WEBPUSH_SILENT', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | WebPush Options
    |--------------------------------------------------------------------------
    |
    | Configuration options for the custom WebPush service including TTL,
    | urgency, and topic settings.
    |
    */
    'ttl' => env('WEBPUSH_TTL', 86400), // 24 hours in seconds
    'urgency' => env('WEBPUSH_URGENCY', 'normal'), // low, normal, high
    'topic' => env('WEBPUSH_TOPIC', null),
    
    /*
    |--------------------------------------------------------------------------
    | Custom Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the custom web push service implementation
    | without external dependencies.
    |
    */
    'custom_service' => [
        'enabled' => env('WEBPUSH_CUSTOM_SERVICE', true),
        'user_agent' => env('WEBPUSH_USER_AGENT', 'LaravelWudel-Notif/1.0'),
        'timeout' => env('WEBPUSH_TIMEOUT', 30),
        'connect_timeout' => env('WEBPUSH_CONNECT_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable logging for push notification activities.
    | This is useful for debugging and monitoring.
    |
    */
    'logging' => [
        'enabled' => env('WEBPUSH_LOGGING', true),
        'level' => env('WEBPUSH_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Cleanup Configuration
    |--------------------------------------------------------------------------
    |
    | Automatically remove expired or invalid subscriptions
    | to keep the database clean.
    |
    */
    'auto_cleanup' => [
        'enabled' => env('WEBPUSH_AUTO_CLEANUP', false),
        'expired_after_days' => env('WEBPUSH_EXPIRED_AFTER_DAYS', 30),
        'unused_after_days' => env('WEBPUSH_UNUSED_AFTER_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Enable queuing for push notifications to handle high volume
    | and improve performance.
    |
    */
    'queue' => [
        'enabled' => env('WEBPUSH_QUEUE_ENABLED', false),
        'connection' => env('WEBPUSH_QUEUE_CONNECTION', 'default'),
        'queue' => env('WEBPUSH_QUEUE_NAME', 'laravelwudel-notif'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for push notification endpoints
    | to prevent abuse and ensure fair usage.
    |
    */
    'rate_limiting' => [
        'enabled' => env('WEBPUSH_RATE_LIMITING', true),
        'max_attempts' => env('WEBPUSH_MAX_ATTEMPTS', 60),
        'decay_minutes' => env('WEBPUSH_DECAY_MINUTES', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security settings for push notification endpoints
    | including CORS and authentication requirements.
    |
    */
    'security' => [
        'require_auth' => env('WEBPUSH_REQUIRE_AUTH', true),
        'cors_enabled' => env('WEBPUSH_CORS_ENABLED', true),
        'allowed_origins' => env('WEBPUSH_ALLOWED_ORIGINS', ['*']),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Database settings for push notification subscriptions
    | including table names and connection settings.
    |
    */
    'database' => [
        'connection' => env('WEBPUSH_DB_CONNECTION', null),
        'table' => 'push_subscriptions',
        'user_foreign_key' => 'user_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    |
    | Model class names and namespaces for push notification
    | functionality. These should not be changed unless you
    | have a custom implementation.
    |
    */
    'models' => [
        'push_subscription' => \LaravelWudel\LaravelWudelNotif\Models\PushSubscription::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Topics
    |--------------------------------------------------------------------------
    |
    | Predefined topics that users can subscribe to
    | for receiving specific types of notifications.
    |
    */
    'default_topics' => [
        'general' => 'General notifications',
        'news' => 'News and updates',
        'promotions' => 'Promotional offers',
        'system' => 'System notifications',
    ],
];
