<?php

namespace LaravelWudel\LaravelWudelNotif\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use LaravelWudel\LaravelWudelNotif\Models\PushSubscription;

class WebPushService
{
    protected CustomWebPushService $customWebPush;
    protected array $vapidKeys;

    public function __construct()
    {
        $this->vapidKeys = [
            'VAPID' => [
                'subject' => config('laravelwudel-notif.vapid.subject'),
                'publicKey' => config('laravelwudel-notif.vapid.public_key'),
                'privateKey' => config('laravelwudel-notif.vapid.private_key'),
            ],
        ];

        $this->customWebPush = new CustomWebPushService();
        
        // Validate VAPID keys on construction
        $this->validateVapidKeys();
    }

    /**
     * Send notification to a specific user.
     */
    public function sendToUser($user, string $title, string $message, array $data = []): int
    {
        $subscriptions = $user->pushSubscriptions()->active()->get();
        
        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $sentCount = 0;
        $payload = $this->buildPayload($title, $message, $data);

        foreach ($subscriptions as $subscription) {
            if ($this->sendToSubscription($subscription, $payload)) {
                $sentCount++;
                $subscription->markAsUsed();
            }
        }

        return $sentCount;
    }

    /**
     * Send notification to all users.
     */
    public function sendToAll(string $title, string $message, array $data = []): int
    {
        $subscriptions = PushSubscription::active()->get();
        
        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $sentCount = 0;
        $payload = $this->buildPayload($title, $message, $data);

        foreach ($subscriptions as $subscription) {
            if ($this->sendToSubscription($subscription, $payload)) {
                $sentCount++;
                $subscription->markAsUsed();
            }
        }

        return $sentCount;
    }

    /**
     * Send notification to a specific topic.
     */
    public function sendToTopic(string $topic, string $title, string $message, array $data = []): int
    {
        $subscriptions = PushSubscription::active()->forTopic($topic)->get();
        
        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $sentCount = 0;
        $payload = $this->buildPayload($title, $message, $data);

        foreach ($subscriptions as $subscription) {
            if ($this->sendToSubscription($subscription, $payload)) {
                $sentCount++;
                $subscription->markAsUsed();
            }
        }

        return $sentCount;
    }

    /**
     * Get VAPID keys for client-side use.
     */
    public function getVapidKeys(): array
    {
        if (!$this->validateVapidKeys()) {
            throw new \RuntimeException('VAPID keys are not properly configured. Please run php artisan push:generate-vapid-keys');
        }
        
        return [
            'public_key' => $this->vapidKeys['VAPID']['publicKey'],
            'subject' => $this->vapidKeys['VAPID']['subject'],
        ];
    }

    /**
     * Validate VAPID keys configuration
     */
    protected function validateVapidKeys(): bool
    {
        $subject = $this->vapidKeys['VAPID']['subject'];
        $publicKey = $this->vapidKeys['VAPID']['publicKey'];
        $privateKey = $this->vapidKeys['VAPID']['privateKey'];
        
        if (empty($subject) || empty($publicKey) || empty($privateKey)) {
            return false;
        }
        
        // Validate subject format (should be mailto:email@domain.com)
        if (!preg_match('/^mailto:[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $subject)) {
            return false;
        }
        
        // Validate public key format (87 characters, base64)
        if (!preg_match('/^[A-Za-z0-9_-]{87}$/', $publicKey)) {
            return false;
        }
        
        // Validate private key format (43 characters, base64)
        if (!preg_match('/^[A-Za-z0-9_-]{43}$/', $privateKey)) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate subscription endpoint.
     */
    public function isValidSubscription(string $endpoint): bool
    {
        return filter_var($endpoint, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Build notification payload.
     */
    protected function buildPayload(string $title, string $message, array $data = []): string
    {
        $payload = [
            'title' => $title,
            'message' => $message,
            'icon' => config('laravelwudel-notif.icon', '/favicon.ico'),
            'badge' => config('laravelwudel-notif.badge', '/favicon.ico'),
            'data' => $data,
        ];

        return json_encode($payload);
    }

    /**
     * Send notification to a specific subscription.
     */
    protected function sendToSubscription(PushSubscription $subscription, string $payload): bool
    {
        try {
            $payloadData = json_decode($payload, true);
            
            return $this->customWebPush->sendToSubscription($subscription, $payloadData);
            
        } catch (\Exception $e) {
            Log::error('Failed to send push notification', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
