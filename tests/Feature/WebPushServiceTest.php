<?php

namespace LaravelWudel\LaravelWudelNotif\Tests\Feature;

use LaravelWudel\LaravelWudelNotif\Tests\TestCase;
use LaravelWudel\LaravelWudelNotif\Services\WebPushService;
use LaravelWudel\LaravelWudelNotif\Models\PushSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WebPushServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WebPushService $pushService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pushService = app(WebPushService::class);
    }

    public function test_can_send_notification_to_user()
    {
        // Create a test user
        $user = \App\Models\User::factory()->create();
        
        // Create a push subscription for the user
        $subscription = PushSubscription::factory()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test',
            'p256dh' => 'test_p256dh_key',
            'auth' => 'test_auth_key'
        ]);

        // Mock the web push service to avoid actual HTTP requests
        $this->mock(WebPushService::class, function ($mock) {
            $mock->shouldReceive('sendToUser')
                ->once()
                ->andReturn(1);
        });

        $result = $this->pushService->sendToUser($user, 'Test Title', 'Test Message');
        
        $this->assertEquals(1, $result);
    }

    public function test_can_send_notification_to_all_users()
    {
        // Create multiple test users with subscriptions
        $users = \App\Models\User::factory()->count(3)->create();
        
        foreach ($users as $user) {
            PushSubscription::factory()->create([
                'user_id' => $user->id,
                'endpoint' => 'https://fcm.googleapis.com/fcm/send/test',
                'p256dh' => 'test_p256dh_key',
                'auth' => 'test_auth_key'
            ]);
        }

        // Mock the web push service
        $this->mock(WebPushService::class, function ($mock) {
            $mock->shouldReceive('sendToAll')
                ->once()
                ->andReturn(3);
        });

        $result = $this->pushService->sendToAll('Broadcast Title', 'Broadcast Message');
        
        $this->assertEquals(3, $result);
    }

    public function test_returns_zero_when_no_subscriptions()
    {
        $user = \App\Models\User::factory()->create();
        
        // No subscriptions created
        
        $result = $this->pushService->sendToUser($user, 'Test Title', 'Test Message');
        
        $this->assertEquals(0, $result);
    }

    public function test_can_handle_invalid_subscription()
    {
        $user = \App\Models\User::factory()->create();
        
        // Create an invalid subscription
        PushSubscription::factory()->create([
            'user_id' => $user->id,
            'endpoint' => 'invalid_endpoint',
            'p256dh' => 'invalid_key',
            'auth' => 'invalid_auth'
        ]);

        // Mock the service to handle invalid subscription gracefully
        $this->mock(WebPushService::class, function ($mock) {
            $mock->shouldReceive('sendToUser')
                ->once()
                ->andReturn(0);
        });

        $result = $this->pushService->sendToUser($user, 'Test Title', 'Test Message');
        
        $this->assertEquals(0, $result);
    }
}
