# Installation Guide - Laravel Push Notification Package

**Version:** 1.0.2 | **Last Updated:** 20 Agust 2025

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

**⚠️ CRITICAL: VAPID keys are required for this package to function properly.**

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

**VAPID Key Requirements:**
- `WEBPUSH_VAPID_SUBJECT`: Must be in format `mailto:email@domain.com`
- `WEBPUSH_VAPID_PUBLIC_KEY`: Must be exactly 87 characters (base64 encoded)
- `WEBPUSH_VAPID_PRIVATE_KEY`: Must be exactly 43 characters (base64 encoded)

**What happens if keys are missing:**
- Package will throw clear error messages
- In development mode: Detailed error with setup instructions
- In production mode: Warning logs and graceful degradation

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

3. **VAPID Key Generation Fails**
   - **OpenSSL Extension Missing**: Ensure PHP OpenSSL extension is installed
   - **OpenSSL Version**: Update to OpenSSL 1.1.1+ for better EC curve support
   - **Permissions**: Check if PHP has write permissions to .env file
   - **Curve Support**: Verify OpenSSL supports prime256v1 curve

4. **Notifications Not Sending**
   - Check browser console for errors
   - Verify subscription is saved
   - Check VAPID configuration

5. **Permission Denied**
   - Ensure HTTPS in production
   - Check browser notification settings
   - Verify user consent

### VAPID Key Generation Troubleshooting

If you encounter issues with `php artisan push:generate-vapid-keys`:

#### Error: "OpenSSL extension is not installed"
```bash
# Ubuntu/Debian
sudo apt-get install php-openssl

# CentOS/RHEL
sudo yum install php-openssl

# Windows (XAMPP/WAMP)
# Enable openssl extension in php.ini
```

#### Error: "Failed to generate VAPID keys"
```bash
# Check OpenSSL version
php -r "echo OPENSSL_VERSION_TEXT;"

# Check PHP extensions
php -m | grep openssl

# Verify curve support
php -r "var_dump(openssl_ec_curve_nist_method('prime256v1'));"
```

#### Error: "VAPID keys already exist"
```bash
# Force regenerate keys
php artisan push:generate-vapid-keys --force

# Or manually remove from .env file and regenerate
# Remove these lines from .env:
# WEBPUSH_VAPID_SUBJECT=...
# WEBPUSH_VAPID_PUBLIC_KEY=...
# WEBPUSH_VAPID_PRIVATE_KEY=...
```

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
- Create an issue in the [GitHub repository](https://github.com/idpcks/laravelwudel_notif)
- Check Laravel documentation for general guidance

## Repository & Links

- **GitHub Repository**: [https://github.com/idpcks/laravelwudel_notif](https://github.com/idpcks/laravelwudel_notif)
- **Packagist Package**: [https://packagist.org/packages/laravelwudel/laravelwudel-notif](https://packagist.org/packages/laravelwudel/laravelwudel-notif)

## Next Steps

After installation:

1. Read the [README.md](README.md) for usage examples
2. Check [examples/basic-usage.php](examples/basic-usage.php) for implementation patterns
3. Customize configuration in `config/laravelwudel-notif.php`
4. Set up monitoring and analytics
5. Implement notification templates and scheduling
