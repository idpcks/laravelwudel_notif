<?php

use Illuminate\Support\Facades\Route;
use LaravelWudel\LaravelWudelNotif\Http\Controllers\PushSubscriptionController;
use LaravelWudel\LaravelWudelNotif\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes for Push Notifications
|--------------------------------------------------------------------------
|
| Here are the routes for managing push notification subscriptions
| and sending notifications.
|
*/

Route::prefix('push')->group(function () {
    // Subscription management
    Route::post('/subscriptions', [PushSubscriptionController::class, 'store']);
    Route::delete('/subscriptions/{subscription}', [PushSubscriptionController::class, 'destroy']);
    Route::get('/subscriptions', [PushSubscriptionController::class, 'index']);
    
    // Notification sending
    Route::post('/send', [NotificationController::class, 'send']);
    Route::post('/send-to-user', [NotificationController::class, 'sendToUser']);
    Route::post('/send-to-all', [NotificationController::class, 'sendToAll']);
    
    // VAPID keys
    Route::get('/vapid-keys', [PushSubscriptionController::class, 'getVapidKeys']);
    
    // Health check
    Route::get('/health', [NotificationController::class, 'health']);
});
