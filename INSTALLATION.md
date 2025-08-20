# Installation Guide - Laravel Push Notification Package

**Version:** 1.0.0 | **Last Updated:** August 20, 2025

## Prerequisites

- PHP 8.2 or higher
- Laravel 11 or 12
- Composer
- SSL certificate (required for production)

## Step 1: Install Package

### Via Composer

```bash
composer require laravelwudel/laravelwudel-notif
```

### Manual Installation

If you want to install manually:

1. Clone or download the package
2. Add to your `composer.json`:

```json
{
    "require": {
        "laravelwudel/laravelwudel-notif": "*"
    },
    "repositories": [
        {
            "type": "path",
            "url": "./laravelwudel-packagist"
        }
    ]
}
```

3. Run `composer update`

## Step 2: Publish Package Files

### Publish Configuration

```bash
php artisan vendor:publish --provider="LaravelWudel\LaravelWudelNotif\LaravelWudelNotifServiceProvider" --tag="laravelwudel-notif-config"
```

### Publish Migrations

```bash
php artisan vendor:publish --provider="LaravelWudel\LaravelWudelNotif\LaravelWudelNotifServiceProvider" --tag="laravelwudel-notif-migrations"
```

### Publish Models (Optional)

```bash
php artisan vendor:publish --provider="LaravelWudel\LaravelWudelNotif\LaravelWudelNotifServiceProvider" --tag="laravelwudel-notif-models"
```

### Publish Assets

```bash
php artisan push:publish-assets
```

## Step 3: Run Migrations

```bash
php artisan migrate
```

## Step 4: Generate VAPID Keys

### Automatic Generation

```bash
php artisan push:generate-vapid-keys
```

This will:
- Generate new VAPID keys
- Add them to your `.env` file
- Display the keys for manual configuration

### Manual Generation

If you prefer to generate keys manually:

1. Visit [https://web-push-codelab.glitch.me/](https://web-push-codelab.glitch.me/)
2. Generate your keys
3. Add to `.env`:

```env
WEBPUSH_VAPID_SUBJECT=mailto:your-email@example.com
WEBPUSH_VAPID_PUBLIC_KEY=your_public_key_here
WEBPUSH_VAPID_PRIVATE_KEY=your_private_key_here
```

## Step 5: Configure Environment

### Required Environment Variables

```env
# VAPID Configuration
WEBPUSH_VAPID_SUBJECT=mailto:your-email@example.com
WEBPUSH_VAPID_PUBLIC_KEY=your_public_key_here
WEBPUSH_VAPID_PRIVATE_KEY=your_private_key_here

# Optional Configuration
WEBPUSH_ICON=/favicon.ico
WEBPUSH_BADGE=/favicon.ico
WEBPUSH_LOGGING=true
WEBPUSH_AUTO_CLEANUP=false
WEBPUSH_QUEUE_ENABLED=false
```

### Configuration File

The package will create `config/laravelwudel-notif.php` with all available options.

## Step 6: Update User Model

Add the relationship to your User model:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    // ... existing code ...

    /**
     * Get the user's push subscriptions.
     */
    public function pushSubscriptions()
    {
        return $this->hasMany(\LaravelWudel\LaravelWudelNotif\Models\PushSubscription::class);
    }
}
```

## Step 7: Frontend Integration

### Include JavaScript Library

```html
<script src="/js/laravelwudel-notif.js"></script>
```

### Initialize Push Notifications

```javascript
const pushNotifications = new LaravelWudelNotifications({
    vapidPublicKey: 'YOUR_VAPID_PUBLIC_KEY',
    serviceWorkerPath: '/sw.js',
    apiBaseUrl: '/api/push',
    autoSubscribe: true
});

// Subscribe to notifications
pushNotifications.init().then(() => {
    console.log('Push notifications initialized');
});
```

## Step 8: Test Installation

### Send Test Notification

```bash
php artisan tinker
```

```php
use LaravelWudel\LaravelWudelNotif\Facades\PushNotification;

// Send to authenticated user
$user = \App\Models\User::first();
PushNotification::sendToUser($user, 'Test', 'Hello from Laravel!');
```

### Check API Endpoints

```bash
# Get VAPID keys
curl http://your-app.test/api/push/vapid-keys

# Health check
curl http://your-app.test/api/push/health
```

## Troubleshooting

### Common Issues

1. **Service Worker Not Loading**
   - Ensure SSL is enabled
   - Check file permissions
   - Verify service worker path

2. **VAPID Keys Invalid**
   - Regenerate keys using `php artisan push:generate-vapid-keys`
   - Check environment variables
   - Verify key format

3. **Notifications Not Sending**
   - Check browser console for errors
   - Verify subscription is saved
   - Check VAPID configuration

4. **Permission Denied**
   - Ensure HTTPS in production
   - Check browser notification settings
   - Verify user consent

### Debug Mode

Enable debug logging in your `.env`:

```env
WEBPUSH_LOGGING=true
WEBPUSH_LOG_LEVEL=debug
```

### Check Logs

```bash
tail -f storage/logs/laravel.log | grep WebPushService
```

## Production Considerations

1. **SSL Certificate**: Required for production
2. **VAPID Keys**: Keep private key secure
3. **Rate Limiting**: Configure appropriate limits
4. **Queue Processing**: Enable for high volume
5. **Monitoring**: Set up notification delivery tracking

## Support

For issues and questions:

- Check the [README.md](README.md) for usage examples
- Review [examples/basic-usage.php](examples/basic-usage.php)
- Create an issue in the repository
- Check Laravel documentation for general guidance

## Next Steps

After installation:

1. Read the [README.md](README.md) for usage examples
2. Check [examples/basic-usage.php](examples/basic-usage.php) for implementation patterns
3. Customize configuration in `config/laravelwudel-notif.php`
4. Set up monitoring and analytics
5. Implement notification templates and scheduling
