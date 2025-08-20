<?php

namespace LaravelWudel\LaravelWudelNotif\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use LaravelWudel\LaravelWudelNotif\Models\PushSubscription;

class CustomWebPushService
{
    protected Client $httpClient;
    protected array $config;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
        
        $this->config = config('laravelwudel-notif');
    }

    /**
     * Send push notification to a specific subscription
     */
    public function sendToSubscription(PushSubscription $subscription, array $payload): bool
    {
        try {
            $headers = $this->buildHeaders($subscription, $payload);
            $body = $this->buildBody($payload);
            
            $response = $this->httpClient->post($subscription->endpoint, [
                'headers' => $headers,
                'body' => $body,
            ]);

            $this->logSuccess($subscription, $response);
            return true;

        } catch (RequestException $e) {
            $this->handleRequestError($subscription, $e);
            return false;
        } catch (\Exception $e) {
            $this->logError($subscription, $e);
            return false;
        }
    }

    /**
     * Send push notification to multiple subscriptions
     */
    public function sendToSubscriptions(array $subscriptions, array $payload): array
    {
        $results = [];
        
        foreach ($subscriptions as $subscription) {
            $results[] = [
                'subscription' => $subscription,
                'success' => $this->sendToSubscription($subscription, $payload)
            ];
        }
        
        return $results;
    }

    /**
     * Build HTTP headers for web push request
     */
    protected function buildHeaders(PushSubscription $subscription, array $payload): array
    {
        $vapidHeaders = $this->generateVapidHeaders($subscription, $payload);
        
        return array_merge([
            'User-Agent' => 'LaravelWudel-Notif/1.0',
            'Content-Type' => 'application/json',
            'Content-Encoding' => 'aes128gcm',
            'TTL' => $this->config['ttl'] ?? 86400,
            'Urgency' => $this->config['urgency'] ?? 'normal',
        ], $vapidHeaders);
    }

    /**
     * Generate VAPID headers for authentication
     */
    protected function generateVapidHeaders(PushSubscription $subscription, array $payload): array
    {
        $vapidPublicKey = $this->config['vapid']['public_key'];
        $vapidPrivateKey = $this->config['vapid']['private_key'];
        $vapidSubject = $this->config['vapid']['subject'];
        
        $audience = parse_url($subscription->endpoint, PHP_URL_SCHEME) . '://' . parse_url($subscription->endpoint, PHP_URL_HOST);
        
        $header = [
            'typ' => 'JWT',
            'alg' => 'ES256'
        ];
        
        $payload = [
            'aud' => $audience,
            'exp' => time() + 12 * 3600, // 12 hours
            'sub' => $vapidSubject
        ];
        
        $jwt = $this->createJWT($header, $payload, $vapidPrivateKey);
        
        return [
            'Authorization' => 'vapid t=' . $jwt . ', k=' . $vapidPublicKey
        ];
    }

    /**
     * Create JWT token for VAPID
     */
    protected function createJWT(array $header, array $payload, string $privateKey): string
    {
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        
        $data = $headerEncoded . '.' . $payloadEncoded;
        $signature = $this->signData($data, $privateKey);
        $signatureEncoded = $this->base64UrlEncode($signature);
        
        return $data . '.' . $signatureEncoded;
    }

    /**
     * Sign data using ECDSA
     */
    protected function signData(string $data, string $privateKey): string
    {
        $key = openssl_pkey_get_private($privateKey);
        $signature = '';
        
        openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256);
        openssl_free_key($key);
        
        return $signature;
    }

    /**
     * Base64 URL encode
     */
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Build request body
     */
    protected function buildBody(array $payload): string
    {
        return json_encode($payload);
    }

    /**
     * Handle request errors and cleanup invalid subscriptions
     */
    protected function handleRequestError(PushSubscription $subscription, RequestException $e): void
    {
        $statusCode = $e->getResponse()?->getStatusCode();
        
        if ($statusCode === 410 || $statusCode === 404) {
            // Subscription is invalid, remove it
            $subscription->delete();
            Log::info("Removed invalid push subscription: {$subscription->id}");
        } elseif ($statusCode === 413) {
            // Payload too large
            Log::warning("Push notification payload too large for subscription: {$subscription->id}");
        } elseif ($statusCode === 429) {
            // Rate limited
            Log::warning("Rate limited for push notification to subscription: {$subscription->id}");
        } else {
            Log::error("Push notification failed for subscription {$subscription->id}: " . $e->getMessage());
        }
    }

    /**
     * Log successful push notification
     */
    protected function logSuccess(PushSubscription $subscription, $response): void
    {
        if ($this->config['logging'] ?? false) {
            Log::info("Push notification sent successfully to subscription: {$subscription->id}", [
                'status_code' => $response->getStatusCode(),
                'subscription_id' => $subscription->id,
                'endpoint' => $subscription->endpoint
            ]);
        }
    }

    /**
     * Log error
     */
    protected function logError(PushSubscription $subscription, \Exception $e): void
    {
        Log::error("Push notification error for subscription {$subscription->id}: " . $e->getMessage(), [
            'subscription_id' => $subscription->id,
            'endpoint' => $subscription->endpoint,
            'exception' => get_class($e)
        ]);
    }

    /**
     * Validate VAPID keys
     */
    public function validateVapidKeys(): bool
    {
        $publicKey = $this->config['vapid']['public_key'] ?? null;
        $privateKey = $this->config['vapid']['private_key'] ?? null;
        $subject = $this->config['vapid']['subject'] ?? null;
        
        if (!$publicKey || !$privateKey || !$subject) {
            return false;
        }
        
        // Basic validation - check if keys are in correct format
        if (!preg_match('/^[A-Za-z0-9_-]{87}$/', $publicKey)) {
            return false;
        }
        
        if (!preg_match('/^[A-Za-z0-9_-]{43}$/', $privateKey)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get VAPID public key for frontend
     */
    public function getVapidPublicKey(): ?string
    {
        return $this->config['vapid']['public_key'] ?? null;
    }
}
