<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PushNotificationController extends Controller
{
    /**
     * Get user's notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Register device token for push notifications.
     */
    public function registerToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|max:500',
            'platform' => 'required|string|in:web,ios,android',
            'device_id' => 'nullable|string|max:255',
            'device_model' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:50',
            'capabilities' => 'nullable|array',
        ]);

        $user = Auth::user();

        // Deactivate old tokens for this device
        if ($request->device_id) {
            DeviceToken::where('user_id', $user->id)
                ->where('device_id', $request->device_id)
                ->update(['is_active' => false]);
        }

        // Create or update device token
        $deviceToken = DeviceToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'token' => $request->token,
            ],
            [
                'platform' => $request->platform,
                'device_id' => $request->device_id,
                'device_model' => $request->device_model,
                'app_version' => $request->app_version,
                'capabilities' => $request->capabilities,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Токен устройства зарегистрирован',
            'data' => $deviceToken
        ]);
    }

    /**
     * Unregister device token.
     */
    public function unregisterToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = Auth::user();

        $deviceToken = DeviceToken::where('user_id', $user->id)
            ->where('token', $request->token)
            ->first();

        if ($deviceToken) {
            $deviceToken->deactivate();
        }

        return response()->json([
            'success' => true,
            'message' => 'Токен устройства удален'
        ]);
    }

    /**
     * Send push notification (admin only).
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'type' => 'nullable|string|in:general,catch,event,subscription,system,promotion',
            'data' => 'nullable|array',
            'image_url' => 'nullable|string|max:500',
            'action_url' => 'nullable|string|max:500',
            'action_text' => 'nullable|string|max:100',
            'target_users' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $notification = PushNotification::create([
            'title' => $request->title,
            'body' => $request->body,
            'type' => $request->type ?? 'general',
            'data' => $request->data,
            'image_url' => $request->image_url,
            'action_url' => $request->action_url,
            'action_text' => $request->action_text,
            'target_users' => $request->target_users,
            'status' => $request->scheduled_at ? 'scheduled' : 'scheduled',
            'scheduled_at' => $request->scheduled_at ?? now(),
            'created_by' => Auth::id(),
        ]);

        // If immediate send, process the notification
        if (!$request->scheduled_at) {
            $this->processNotification($notification);
        }

        return response()->json([
            'success' => true,
            'message' => $request->scheduled_at ? 'Уведомление запланировано' : 'Уведомление отправлено',
            'data' => $notification
        ]);
    }

    /**
     * Get notification statistics (admin only).
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_notifications' => PushNotification::count(),
            'sent_notifications' => PushNotification::sent()->count(),
            'scheduled_notifications' => PushNotification::scheduled()->count(),
            'failed_notifications' => PushNotification::failed()->count(),
            'total_device_tokens' => DeviceToken::active()->count(),
            'web_tokens' => DeviceToken::active()->web()->count(),
            'ios_tokens' => DeviceToken::active()->ios()->count(),
            'android_tokens' => DeviceToken::active()->android()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Process push notification.
     */
    private function processNotification(PushNotification $notification): void
    {
        try {
            $deviceTokens = $this->getTargetDeviceTokens($notification);
            
            if ($deviceTokens->isEmpty()) {
                $notification->markAsFailed();
                return;
            }

            $sentCount = 0;
            $failedCount = 0;

            foreach ($deviceTokens as $deviceToken) {
                if ($this->sendToDevice($deviceToken, $notification)) {
                    $sentCount++;
                } else {
                    $failedCount++;
                }
            }

            $notification->markAsSent($sentCount, $failedCount);

        } catch (\Exception $e) {
            Log::error('Push notification processing failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
            
            $notification->markAsFailed();
        }
    }

    /**
     * Get target device tokens for notification.
     */
    private function getTargetDeviceTokens(PushNotification $notification)
    {
        $query = DeviceToken::active();

        if ($notification->target_users) {
            $query->whereIn('user_id', $notification->target_users);
        }

        return $query->get();
    }

    /**
     * Send notification to specific device.
     */
    private function sendToDevice(DeviceToken $deviceToken, PushNotification $notification): bool
    {
        try {
            // Here you would integrate with actual push notification services
            // For now, we'll simulate successful sending
            
            $payload = [
                'title' => $notification->title,
                'body' => $notification->body,
                'data' => $notification->data,
                'image' => $notification->image_url,
                'action_url' => $notification->action_url,
                'action_text' => $notification->action_text,
            ];

            // Simulate sending based on platform
            switch ($deviceToken->platform) {
                case 'web':
                    // Web Push API
                    $this->sendWebPush($deviceToken->token, $payload);
                    break;
                case 'ios':
                    // Apple Push Notification Service
                    $this->sendIOSPush($deviceToken->token, $payload);
                    break;
                case 'android':
                    // Firebase Cloud Messaging
                    $this->sendAndroidPush($deviceToken->token, $payload);
                    break;
            }

            $deviceToken->updateLastUsed();
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send push notification to device', [
                'device_token_id' => $deviceToken->id,
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Send web push notification.
     */
    private function sendWebPush(string $token, array $payload): void
    {
        // Implement web push sending logic
        // This would typically use a service like Pusher, OneSignal, or Firebase
        Log::info('Sending web push notification', ['token' => $token, 'payload' => $payload]);
    }

    /**
     * Send iOS push notification.
     */
    private function sendIOSPush(string $token, array $payload): void
    {
        // Implement iOS push sending logic
        // This would typically use Apple Push Notification Service
        Log::info('Sending iOS push notification', ['token' => $token, 'payload' => $payload]);
    }

    /**
     * Send Android push notification.
     */
    private function sendAndroidPush(string $token, array $payload): void
    {
        // Implement Android push sending logic
        // This would typically use Firebase Cloud Messaging
        Log::info('Sending Android push notification', ['token' => $token, 'payload' => $payload]);
    }
}
