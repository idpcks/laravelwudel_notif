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
        return [
            'public_key' => $this->vapidKeys['VAPID']['publicKey'],
            'subject' => $this->vapidKeys['VAPID']['subject'],
        ];
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
