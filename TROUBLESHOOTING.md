# Troubleshooting Guide

## Common Issues and Solutions

### 1. Namespace Conflict Error

**Error:**
```
Class LaravelWudel\LaravelWudelNotif\Models\PushSubscription located in ./app/Models/PushSubscription.php does not comply with psr-4 autoloading standard (rule: App\ => ./app). Skipping.
```

**Cause:**
This error occurs when the package models are published to the wrong location or there's a namespace conflict.

**Solution:**
1. **Remove any published models from your app/Models directory:**
   ```bash
   rm -rf app/Models/PushSubscription.php
   ```

2. **Clear all caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   composer dump-autoload
   ```

3. **Ensure you're using the correct namespace:**
   ```php
   use LaravelWudel\LaravelWudelNotif\Models\PushSubscription;
   ```

4. **Use the model helper for better compatibility:**
   ```php
   use LaravelWudel\LaravelWudelNotif\Helpers\ModelHelper;
   
   $modelClass = ModelHelper::getPushSubscriptionModel();
   $subscription = new $modelClass();
   ```

### 2. OpenSSL Function Not Found Error

**Error:**
```
Call to undefined function openssl_ec_curve_nist_method()
```

**Cause:**
This function was deprecated and removed in OpenSSL 3.0+.

**Solution:**
The package has been updated to handle OpenSSL 3.0+ compatibility. If you're still experiencing issues:

1. **Update to the latest version of the package**
2. **Ensure OpenSSL extension is enabled in PHP**
3. **Check your OpenSSL version:**
   ```bash
   php -r "echo OPENSSL_VERSION_TEXT;"
   ```

### 3. Model Not Found Error

**Error:**
```
Class 'App\Models\PushSubscription' not found
```

**Cause:**
You're trying to use the wrong namespace or the model hasn't been properly registered.

**Solution:**
1. **Use the correct namespace:**
   ```php
   use LaravelWudel\LaravelWudelNotif\Models\PushSubscription;
   ```

2. **Or use the model helper:**
   ```php
   use LaravelWudel\LaravelWudelNotif\Helpers\ModelHelper;
   
   $subscription = ModelHelper::createPushSubscriptionModel();
   ```

### 4. Migration Issues

**Error:**
```
Table 'push_subscriptions' already exists
```

**Cause:**
The migration has already been run or there's a table name conflict.

**Solution:**
1. **Check if the table exists:**
   ```bash
   php artisan migrate:status
   ```

2. **If you need to recreate the table:**
   ```bash
   php artisan migrate:rollback
   php artisan migrate
   ```

3. **Or use a different table name in config:**
   ```php
   // config/laravelwudel-notif.php
   'database' => [
       'table' => 'web_push_subscriptions', // Change this
   ],
   ```

### 5. VAPID Key Generation Issues

**Error:**
```
Failed to generate VAPID keys
```

**Cause:**
OpenSSL configuration issues or insufficient permissions.

**Solution:**
1. **Check OpenSSL installation:**
   ```bash
   php -m | grep openssl
   ```

2. **Ensure proper permissions:**
   ```bash
   chmod 755 storage/
   chmod 644 .env
   ```

3. **Try generating keys manually:**
   ```bash
   php artisan push:generate-vapid-keys --force
   ```

### 6. Service Provider Not Found

**Error:**
```
Class 'LaravelWudel\LaravelWudelNotif\LaravelWudelNotifServiceProvider' not found
```

**Cause:**
The package is not properly installed or autoloaded.

**Solution:**
1. **Reinstall the package:**
   ```bash
   composer remove laravelwudel/laravelwudel-notif
   composer require laravelwudel/laravelwudel-notif
   ```

2. **Clear composer cache:**
   ```bash
   composer clear-cache
   composer dump-autoload
   ```

3. **Check if the service provider is in config/app.php:**
   ```php
   'providers' => [
       // ...
       LaravelWudel\LaravelWudelNotif\LaravelWudelNotifServiceProvider::class,
   ],
   ```

## Best Practices

1. **Always use the correct namespace** when importing models
2. **Don't publish models** to avoid namespace conflicts
3. **Use the model helper** for better compatibility
4. **Clear caches** after making configuration changes
5. **Check OpenSSL compatibility** before generating VAPID keys

## Getting Help

If you're still experiencing issues:

1. **Check the logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Enable debug mode** in your .env file
3. **Check the package documentation**
4. **Open an issue** on the GitHub repository with:
   - Error message
   - Laravel version
   - PHP version
   - OpenSSL version
   - Steps to reproduce
