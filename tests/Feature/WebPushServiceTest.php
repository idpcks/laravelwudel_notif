<?php

namespace LaravelWudel\LaravelWudelNotif\Tests\Feature;

use LaravelWudel\LaravelWudelNotif\Tests\TestCase;
use LaravelWudel\LaravelWudelNotif\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WebPushServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WebPushService $webPushService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->webPushService = new WebPushService();
    }

    /** @test */
    public function it_throws_exception_when_vapid_keys_are_missing()
    {
        // Clear VAPID configuration
        config([
            'laravelwudel-notif.vapid.subject' => null,
            'laravelwudel-notif.vapid.public_key' => null,
            'laravelwudel-notif.vapid.private_key' => null,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('VAPID keys are not properly configured. Please run php artisan push:generate-vapid-keys');

        $this->webPushService->getVapidKeys();
    }

    /** @test */
    public function it_throws_exception_when_vapid_keys_are_invalid()
    {
        // Set invalid VAPID configuration
        config([
            'laravelwudel-notif.vapid.subject' => 'invalid-subject',
            'laravelwudel-notif.vapid.public_key' => 'invalid_key',
            'laravelwudel-notif.vapid.private_key' => 'invalid_key',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('VAPID keys are not properly configured. Please run php artisan push:generate-vapid-keys');

        $this->webPushService->getVapidKeys();
    }

    /** @test */
    public function it_returns_vapid_keys_when_properly_configured()
    {
        // Set valid VAPID configuration
        config([
            'laravelwudel-notif.vapid.subject' => 'mailto:test@example.com',
            'laravelwudel-notif.vapid.public_key' => 'test_public_key_87_chars_long_for_testing_purposes_only_123456789',
            'laravelwudel-notif.vapid.private_key' => 'test_private_key_43_chars_long_for_testing_123456789',
        ]);

        $keys = $this->webPushService->getVapidKeys();

        $this->assertArrayHasKey('public_key', $keys);
        $this->assertArrayHasKey('subject', $keys);
        $this->assertEquals('test_public_key_87_chars_long_for_testing_purposes_only_123456789', $keys['public_key']);
        $this->assertEquals('mailto:test@example.com', $keys['subject']);
    }

    /** @test */
    public function it_validates_vapid_subject_format()
    {
        // Test invalid subject formats
        $invalidSubjects = [
            'test@example.com',           // Missing mailto:
            'mailto:',                    // Missing email
            'mailto:invalid-email',       // Invalid email format
            'http://example.com',         // Wrong protocol
            'mailto:test@',               // Incomplete email
        ];

        foreach ($invalidSubjects as $subject) {
            config([
                'laravelwudel-notif.vapid.subject' => $subject,
                'laravelwudel-notif.vapid.public_key' => 'test_public_key_87_chars_long_for_testing_purposes_only_123456789',
                'laravelwudel-notif.vapid.private_key' => 'test_private_key_43_chars_long_for_testing_123456789',
            ]);

            $this->expectException(\RuntimeException::class);
            $this->webPushService->getVapidKeys();
        }
    }

    /** @test */
    public function it_validates_vapid_key_lengths()
    {
        // Test invalid key lengths
        config([
            'laravelwudel-notif.vapid.subject' => 'mailto:test@example.com',
            'laravelwudel-notif.vapid.public_key' => 'short_key',  // Too short
            'laravelwudel-notif.vapid.private_key' => 'test_private_key_43_chars_long_for_testing_123456789',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->webPushService->getVapidKeys();

        // Test private key length
        config([
            'laravelwudel-notif.vapid.subject' => 'mailto:test@example.com',
            'laravelwudel-notif.vapid.public_key' => 'test_public_key_87_chars_long_for_testing_purposes_only_123456789',
            'laravelwudel-notif.vapid.private_key' => 'short_key',  // Too short
        ]);

        $this->expectException(\RuntimeException::class);
        $this->webPushService->getVapidKeys();
    }
}
