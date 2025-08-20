<?php

namespace LaravelWudel\LaravelWudelNotif\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use LaravelWudel\LaravelWudelNotif\Services\WebPushService;

class NotificationController extends Controller
{
    public function __construct(private WebPushService $pushService) {}

    /**
     * Send notification to authenticated user.
     */
    public function sendToUser(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'data' => 'sometimes|array'
        ]);

        $user = Auth::user();
        $sentCount = $this->pushService->sendToUser(
            $user,
            $request->title,
            $request->message,
            $request->data ?? []
        );

        return response()->json([
            'success' => true,
            'sent_count' => $sentCount,
            'message' => "Notification sent to {$sentCount} device(s)"
        ]);
    }

    /**
     * Send notification to all users.
     */
    public function sendToAll(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'data' => 'sometimes|array'
        ]);

        $sentCount = $this->pushService->sendToAll(
            $request->title,
            $request->message,
            $request->data ?? []
        );

        return response()->json([
            'success' => true,
            'sent_count' => $sentCount,
            'message' => "Broadcast notification sent to {$sentCount} device(s)"
        ]);
    }

    /**
     * Send notification to a specific topic.
     */
    public function sendToTopic(Request $request): JsonResponse
    {
        $request->validate([
            'topic' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'data' => 'sometimes|array'
        ]);

        $sentCount = $this->pushService->sendToTopic(
            $request->topic,
            $request->title,
            $request->message,
            $request->data ?? []
        );

        return response()->json([
            'success' => true,
            'sent_count' => $sentCount,
            'message' => "Topic notification sent to {$sentCount} device(s)"
        ]);
    }

    /**
     * Send custom notification.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:user,all,topic',
            'target' => 'required_if:type,user|required_if:type,topic|string',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'data' => 'sometimes|array'
        ]);

        $type = $request->type;
        $sentCount = 0;

        switch ($type) {
            case 'user':
                $user = \App\Models\User::findOrFail($request->target);
                $sentCount = $this->pushService->sendToUser(
                    $user,
                    $request->title,
                    $request->message,
                    $request->data ?? []
                );
                break;

            case 'topic':
                $sentCount = $this->pushService->sendToTopic(
                    $request->target,
                    $request->title,
                    $request->message,
                    $request->data ?? []
                );
                break;

            case 'all':
                $sentCount = $this->pushService->sendToAll(
                    $request->title,
                    $request->message,
                    $request->data ?? []
                );
                break;
        }

        return response()->json([
            'success' => true,
            'sent_count' => $sentCount,
            'message' => "Notification sent to {$sentCount} device(s)"
        ]);
    }

    /**
     * Health check endpoint.
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'package' => 'laravelwudel-notif',
            'version' => '1.0.0'
        ]);
    }
}
