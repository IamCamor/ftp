<?php

namespace App\Listeners;

use App\Events\ContentModerationRequested;
use App\Events\ContentModerationCompleted;
use App\Services\AIModerationService;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessContentModeration implements ShouldQueue
{
    use InteractsWithQueue;

    private AIModerationService $moderationService;
    private TelegramService $telegramService;

    /**
     * Create the event listener.
     */
    public function __construct(
        AIModerationService $moderationService,
        TelegramService $telegramService
    ) {
        $this->moderationService = $moderationService;
        $this->telegramService = $telegramService;
    }

    /**
     * Handle the event.
     */
    public function handle(ContentModerationRequested $event): void
    {
        try {
            Log::info('Processing content moderation', [
                'content_type' => $event->contentType,
                'content_id' => $event->contentId,
                'format' => $event->format,
                'user_id' => $event->userId
            ]);

            $result = match ($event->format) {
                'text' => $this->moderationService->moderateText($event->content, $event->contentType),
                'image' => $this->moderationService->moderateImage($event->content, $event->contentType),
                default => throw new \Exception("Unsupported content format: {$event->format}")
            };

            // Fire completion event
            event(new ContentModerationCompleted(
                $event->contentType,
                $event->contentId,
                $result,
                $event->userId
            ));

            // Send notifications if needed
            $this->sendNotifications($event, $result);

            Log::info('Content moderation completed', [
                'content_type' => $event->contentType,
                'content_id' => $event->contentId,
                'approved' => $result['approved'],
                'confidence' => $result['confidence']
            ]);

        } catch (\Exception $e) {
            Log::error('Content moderation failed', [
                'content_type' => $event->contentType,
                'content_id' => $event->contentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Fire completion event with failure result
            event(new ContentModerationCompleted(
                $event->contentType,
                $event->contentId,
                [
                    'approved' => false,
                    'confidence' => 0.0,
                    'reason' => 'Moderation failed: ' . $e->getMessage(),
                    'categories' => ['moderation_error'],
                    'raw_response' => null,
                ],
                $event->userId
            ));
        }
    }

    /**
     * Send notifications based on moderation result
     */
    private function sendNotifications(ContentModerationRequested $event, array $result): void
    {
        $notifications = config('ai_moderation.notifications', []);
        
        if (!($notifications['enabled'] ?? true)) {
            return;
        }

        // Send Telegram notification for rejected content
        if (!$result['approved'] && ($notifications['telegram_notifications'] ?? true)) {
            $this->sendTelegramNotification($event, $result);
        }

        // Send admin notifications for manual review
        if ($this->requiresManualReview($result) && ($notifications['notify_admins'] ?? true)) {
            $this->sendAdminNotification($event, $result);
        }
    }

    /**
     * Send Telegram notification
     */
    private function sendTelegramNotification(ContentModerationRequested $event, array $result): void
    {
        try {
            $message = "🚫 *Content Moderation Alert*\n\n" .
                      "Content Type: {$event->contentType}\n" .
                      "Content ID: {$event->contentId}\n" .
                      "User ID: {$event->userId}\n" .
                      "Status: " . ($result['approved'] ? '✅ Approved' : '❌ Rejected') . "\n" .
                      "Confidence: " . round($result['confidence'] * 100, 1) . "%\n" .
                      "Reason: {$result['reason']}\n" .
                      "Categories: " . implode(', ', $result['categories']) . "\n" .
                      "Time: " . now()->format('Y-m-d H:i:s');

            $this->telegramService->sendMessage($message, config('telegram.admin_chat_id'));
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram moderation notification', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send admin notification
     */
    private function sendAdminNotification(ContentModerationRequested $event, array $result): void
    {
        // This would typically send email or other notifications to admins
        Log::info('Admin notification required for content moderation', [
            'content_type' => $event->contentType,
            'content_id' => $event->contentId,
            'result' => $result
        ]);
    }

    /**
     * Check if content requires manual review
     */
    private function requiresManualReview(array $result): bool
    {
        $thresholds = config('ai_moderation.thresholds', []);
        $manualReviewThreshold = $thresholds['manual_review_confidence'] ?? 0.7;
        
        return $result['confidence'] < $manualReviewThreshold && 
               in_array('pending_review', $result['categories']);
    }
}
