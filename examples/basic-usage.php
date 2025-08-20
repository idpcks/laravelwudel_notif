<?php

/**
 * Basic Usage Example for Laravel Push Notification Package
 * 
 * This example demonstrates the basic usage of the package
 * for sending push notifications to users.
 */

use LaravelWudel\LaravelWudelNotif\Facades\PushNotification;
use LaravelWudel\LaravelWudelNotif\Services\WebPushService;
use LaravelWudel\LaravelWudelNotif\Models\PushSubscription;

// Example 1: Using Facade
// Send notification to a specific user
$user = \App\Models\User::find(1);
$result = PushNotification::sendToUser($user, 'Hello!', 'This is a test notification');

// Example 2: Using Service directly
$pushService = app(WebPushService::class);
$result = $pushService->sendToUser($user, 'Hello!', 'This is a test notification');

// Example 3: Send to all users
$result = PushNotification::sendToAll('Broadcast', 'This is a broadcast message');

// Example 4: Send with additional data
$result = PushNotification::sendToUser($user, 'Action Required', 'Please check your dashboard', [
    'url' => '/dashboard',
    'action' => 'view',
    'priority' => 'high'
]);

// Example 5: Check subscription status
$subscription = PushSubscription::where('user_id', $user->id)->first();
if ($subscription) {
    echo "User has active subscription";
} else {
    echo "User needs to subscribe to push notifications";
}

// Example 6: Send notification to multiple users
$users = \App\Models\User::where('role', 'admin')->get();
foreach ($users as $user) {
    PushNotification::sendToUser($user, 'Admin Alert', 'System maintenance scheduled');
}

echo "Examples completed successfully!";
