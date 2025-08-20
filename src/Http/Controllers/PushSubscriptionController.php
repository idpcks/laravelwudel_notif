<?php

namespace LaravelWudel\LaravelWudelNotif\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use LaravelWudel\LaravelWudelNotif\Models\PushSubscription;
use LaravelWudel\LaravelWudelNotif\Services\WebPushService;

class PushSubscriptionController extends Controller
{
    public function __construct(private WebPushService $pushService) {}

    /**
     * Store a new push subscription.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string|max:500',
            'p256dh' => 'required|string|max:255',
            'auth' => 'required|string|max:255',
            'topic' => 'sometimes|string|max:100',
            'device_info' => 'sometimes|array'
        ]);

        $user = Auth::user();
        
        // Check if subscription already exists
        $existingSubscription = PushSubscription::where('endpoint', $request->endpoint)->first();
        
        if ($existingSubscription) {
            // Update existing subscription
            $existingSubscription->update([
                'p256dh' => $request->p256dh,
                'auth' => $request->auth,
                'topic' => $request->topic ?? 'general',
                'device_info' => $request->device_info ?? [],
                'last_used_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription updated successfully',
                'subscription' => $existingSubscription
            ]);
        }

        // Create new subscription
        $subscription = PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => $request->endpoint,
            'p256dh' => $request->p256dh,
            'auth' => $request->auth,
            'topic' => $request->topic ?? 'general',
            'device_info' => $request->device_info ?? [],
            'last_used_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription created successfully',
            'subscription' => $subscription
        ], 201);
    }

    /**
     * Display the specified subscription.
     */
    public function show(PushSubscription $subscription): JsonResponse
    {
        $user = Auth::user();
        
        if ($subscription->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to subscription'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'subscription' => $subscription
        ]);
    }

    /**
     * Update the specified subscription.
     */
    public function update(Request $request, PushSubscription $subscription): JsonResponse
    {
        $user = Auth::user();
        
        if ($subscription->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to subscription'
            ], 403);
        }

        $request->validate([
            'topic' => 'sometimes|string|max:100',
            'device_info' => 'sometimes|array'
        ]);

        $subscription->update([
            'topic' => $request->topic ?? $subscription->topic,
            'device_info' => $request->device_info ?? $subscription->device_info,
            'last_used_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription updated successfully',
            'subscription' => $subscription
        ]);
    }

    /**
     * Remove the specified subscription.
     */
    public function destroy(PushSubscription $subscription): JsonResponse
    {
        $user = Auth::user();
        
        if ($subscription->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to subscription'
            ], 403);
        }

        $subscription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subscription removed successfully'
        ]);
    }

    /**
     * Get user's subscriptions.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $subscriptions = $user->pushSubscriptions()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions,
            'count' => $subscriptions->count()
        ]);
    }

    /**
     * Get VAPID keys for client-side use.
     */
    public function getVapidKeys(): JsonResponse
    {
        $keys = $this->pushService->getVapidKeys();

        return response()->json([
            'success' => true,
            'vapid_keys' => $keys
        ]);
    }
}
