<?php

namespace LaravelWudel\LaravelWudelNotif\Tests\Feature;

use LaravelWudel\LaravelWudelNotif\Tests\TestCase;
use LaravelWudel\LaravelWudelNotif\Services\CustomWebPushService;
use LaravelWudel\LaravelWudelNotif\Models\PushSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomWebPushServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CustomWebPushService $customWebPushService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customWebPushService = new CustomWebPushService();
        
        // Set test configuration
        config([
            'laravelwudel-notif.vapid.public_key' => 'test_public_key_87_chars_long_for_testing_purposes_only_123456789',
            'laravelwudel-notif.vapid.private_key' => 'test_private_key_43_chars_long_for_testing_123456789',
            'laravelwudel-notif.vapid.subject' => 'mailto:test@example.com',
        ]);
    }

    /** @test */
    public function it_can_validate_vapid_keys()
    {
        $isValid = $this->customWebPushService->validateVapidKeys();
        
        $this->assertTrue($isValid);
    }

    /** @test */
    public function it_can_get_vapid_public_key()
    {
        $publicKey = $this->customWebPushService->getVapidPublicKey();
        
        $this->assertEquals(
            'test_public_key_87_chars_long_for_testing_purposes_only_123456789',
            $publicKey
        );
    }

    /** @test */
    public function it_can_build_headers_for_web_push_request()
    {
        $subscription = PushSubscription::factory()->create([
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test',
            'p256dh' => 'test_p256dh_key',
            'auth' => 'test_auth_key',
        ]);

        $payload = [
            'title' => 'Test Title',
            'message' => 'Test Message',
        ];

        $headers = $this->invokeMethod($this->customWebPushService, 'buildHeaders', [$subscription, $payload]);

        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('Content-Encoding', $headers);
        $this->assertArrayHasKey('TTL', $headers);
        $this->assertArrayHasKey('Urgency', $headers);
        $this->assertArrayHasKey('Authorization', $headers);
        
        $this->assertEquals('LaravelWudel-Notif/1.0', $headers['User-Agent']);
        $this->assertEquals('application/json', $headers['Content-Type']);
        $this->assertEquals('aes128gcm', $headers['Content-Encoding']);
    }

    /** @test */
    public function it_can_build_payload_body()
    {
        $payload = [
            'title' => 'Test Title',
            'message' => 'Test Message',
            'data' => ['key' => 'value'],
        ];

        $body = $this->invokeMethod($this->customWebPushService, 'buildBody', [$payload]);

        $this->assertIsString($body);
        $this->assertEquals(json_encode($payload), $body);
    }

    /** @test */
    public function it_can_encode_base64_url()
    {
        $data = 'test data with special chars: +/=';
        
        $encoded = $this->invokeMethod($this->customWebPushService, 'base64UrlEncode', [$data]);
        
        $this->assertIsString($encoded);
        $this->assertStringNotContainsString('+', $encoded);
        $this->assertStringNotContainsString('/', $encoded);
        $this->assertStringNotContainsString('=', $encoded);
    }

    /** @test */
    public function it_can_handle_multiple_subscriptions()
    {
        $subscriptions = PushSubscription::factory()->count(3)->create();
        
        $payload = [
            'title' => 'Test Title',
            'message' => 'Test Message',
        ];

        $results = $this->customWebPushService->sendToSubscriptions($subscriptions->toArray(), $payload);

        $this->assertCount(3, $results);
        
        foreach ($results as $result) {
            $this->assertArrayHasKey('subscription', $result);
            $this->assertArrayHasKey('success', $result);
            $this->assertInstanceOf(PushSubscription::class, $result['subscription']);
            $this->assertIsBool($result['success']);
        }
    }

    /**
     * Helper method to invoke private/protected methods for testing
     */
    protected function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
