<?php

namespace App\Listeners;

use App\Events\ContentModerationCompleted;
use App\Models\CatchRecord;
use App\Models\CatchComment;
use App\Models\Point;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleModerationResult implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ContentModerationCompleted $event): void
    {
        try {
            $result = $event->moderationResult;
            $contentType = $event->contentType;
            $contentId = $event->contentId;

            Log::info('Handling moderation result', [
                'content_type' => $contentType,
                'content_id' => $contentId,
                'approved' => $result['approved'],
                'confidence' => $result['confidence']
            ]);

            // Update content status based on moderation result
            $this->updateContentStatus($contentType, $contentId, $result);

            // Log moderation action
            $this->logModerationAction($contentType, $contentId, $result, $event->userId);

        } catch (\Exception $e) {
            Log::error('Failed to handle moderation result', [
                'content_type' => $event->contentType,
                'content_id' => $event->contentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Update content status based on moderation result
     */
    private function updateContentStatus(string $contentType, string $contentId, array $result): void
    {
        $status = $this->determineContentStatus($result);
        
        match ($contentType) {
            'catch_photos', 'catch_descriptions' => $this->updateCatchStatus($contentId, $status, $result),
            'catch_comments' => $this->updateCommentStatus($contentId, $status, $result),
            'point_descriptions', 'point_comments' => $this->updatePointStatus($contentId, $status, $result),
            default => Log::warning('Unknown content type for moderation', [
                'content_type' => $contentType,
                'content_id' => $contentId
            ])
        };
    }

    /**
     * Determine content status from moderation result
     */
    private function determineContentStatus(array $result): string
    {
        $thresholds = config('ai_moderation.thresholds', []);
        $autoApproveThreshold = $thresholds['auto_approve_confidence'] ?? 0.9;
        $autoRejectThreshold = $thresholds['auto_reject_confidence'] ?? 0.8;

        if ($result['approved'] && $result['confidence'] >= $autoApproveThreshold) {
            return 'approved';
        }

        if (!$result['approved'] && $result['confidence'] >= $autoRejectThreshold) {
            return 'rejected';
        }

        return 'pending_review';
    }

    /**
     * Update catch record status
     */
    private function updateCatchStatus(string $catchId, string $status, array $result): void
    {
        try {
            $catch = CatchRecord::find($catchId);
            if (!$catch) {
                Log::warning('Catch record not found for moderation', ['catch_id' => $catchId]);
                return;
            }

            $catch->update([
                'moderation_status' => $status,
                'moderation_result' => $result,
                'moderated_at' => now(),
            ]);

            Log::info('Catch record moderation status updated', [
                'catch_id' => $catchId,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update catch status', [
                'catch_id' => $catchId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update comment status
     */
    private function updateCommentStatus(string $commentId, string $status, array $result): void
    {
        try {
            $comment = CatchComment::find($commentId);
            if (!$comment) {
                Log::warning('Comment not found for moderation', ['comment_id' => $commentId]);
                return;
            }

            $comment->update([
                'moderation_status' => $status,
                'moderation_result' => $result,
                'moderated_at' => now(),
            ]);

            Log::info('Comment moderation status updated', [
                'comment_id' => $commentId,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update comment status', [
                'comment_id' => $commentId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update point status
     */
    private function updatePointStatus(string $pointId, string $status, array $result): void
    {
        try {
            $point = Point::find($pointId);
            if (!$point) {
                Log::warning('Point not found for moderation', ['point_id' => $pointId]);
                return;
            }

            $point->update([
                'moderation_status' => $status,
                'moderation_result' => $result,
                'moderated_at' => now(),
            ]);

            Log::info('Point moderation status updated', [
                'point_id' => $pointId,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update point status', [
                'point_id' => $pointId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log moderation action
     */
    private function logModerationAction(string $contentType, string $contentId, array $result, ?int $userId): void
    {
        try {
            // This would typically log to a moderation_actions table
            Log::info('Moderation action logged', [
                'content_type' => $contentType,
                'content_id' => $contentId,
                'user_id' => $userId,
                'approved' => $result['approved'],
                'confidence' => $result['confidence'],
                'reason' => $result['reason'],
                'categories' => $result['categories'],
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log moderation action', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
