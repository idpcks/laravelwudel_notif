# Laravel Push Notification Package

Package Laravel yang komprehensif untuk web push notifications dengan dukungan VAPID.

**Version:** 1.0.0 | **Release Date:** August 20, 2025

## Fitur

- ✅ Dukungan Laravel 11 & 12
- ✅ Web Push Notifications dengan VAPID
- ✅ Service Provider auto-discovery
- ✅ Facade untuk kemudahan penggunaan
- ✅ Model dan Migration yang fleksibel
- ✅ Queue support untuk notifikasi massal
- ✅ Error handling yang robust
- ✅ Logging yang detail
- ✅ Testing support

## Instalasi

### 1. Install via Composer

```bash
composer require laravelwudel/laravelwudel-notif
```

### 2. Publish Configuration dan Migration

```bash
php artisan vendor:publish --provider="LaravelWudel\LaravelWudelNotif\LaravelWudelNotifServiceProvider"
```

### 3. Jalankan Migration

```bash
php artisan migrate
```

### 4. Generate VAPID Keys

```bash
php artisan push:generate-vapid-keys
```

## Konfigurasi

### Environment Variables

Tambahkan ke file `.env`:

```env
WEBPUSH_VAPID_SUBJECT=mailto:your-email@example.com
WEBPUSH_VAPID_PUBLIC_KEY=your_public_key_here
WEBPUSH_VAPID_PRIVATE_KEY=your_private_key_here
```

### Config File

File `config/laravelwudel-notif.php` akan otomatis dibuat dengan konfigurasi default.

## Penggunaan

### Basic Usage

```php
use LaravelWudel\LaravelWudelNotif\Facades\PushNotification;

// Kirim notifikasi ke user tertentu
PushNotification::sendToUser($user, 'Judul', 'Pesan notifikasi');

// Kirim notifikasi ke semua user
PushNotification::sendToAll('Judul', 'Pesan broadcast');

// Kirim dengan data tambahan
PushNotification::sendToUser($user, 'Judul', 'Pesan', [
    'url' => '/dashboard',
    'action' => 'view'
]);
```

### Via Service

```php
use LaravelWudel\LaravelWudelNotif\Services\WebPushService;

class NotificationController extends Controller
{
    public function __construct(private WebPushService $pushService) {}

    public function sendNotification(Request $request)
    {
        $user = auth()->user();
        $sent = $this->pushService->sendToUser(
            $user,
            $request->title,
            $request->message
        );

        return response()->json(['sent' => $sent]);
    }
}
```

### Model Relationships

```php
// User model
public function pushSubscriptions()
{
    return $this->hasMany(PushSubscription::class);
}

// PushSubscription model
public function user()
{
    return $this->belongsTo(User::class);
}
```

## Frontend Integration

### JavaScript

```javascript
// Subscribe to push notifications
async function subscribeToPushNotifications() {
    try {
        const registration = await navigator.serviceWorker.register('/sw.js');
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: 'YOUR_VAPID_PUBLIC_KEY'
        });

        // Send subscription to backend
        await fetch('/api/push-subscriptions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(subscription)
        });
    } catch (error) {
        console.error('Error subscribing to push notifications:', error);
    }
}
```

### Service Worker

```javascript
// sw.js
self.addEventListener('push', function(event) {
    const data = event.data.json();
    
    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.message,
            icon: data.icon,
            badge: data.badge,
            data: data.data
        })
    );
});
```

## Commands

### Generate VAPID Keys

```bash
php artisan push:generate-vapid-keys
```

### Cleanup Old Subscriptions

```bash
php artisan push:cleanup-subscriptions
```

## Testing

```bash
composer test
```

## Contributing

1. Fork repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

Untuk dukungan, silakan buat issue di repository ini atau hubungi tim LaravelWudel.
