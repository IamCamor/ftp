<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AIModerationService;
use App\Events\ContentModerationRequested;
use App\Models\CatchRecord;
use App\Models\CatchComment;
use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ModerationController extends Controller
{
    private AIModerationService $moderationService;

    public function __construct(AIModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
    }

    /**
     * Get moderation statistics (admin only)
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('admin');

        $stats = $this->moderationService->getModerationStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Moderate text content
     */
    public function moderateText(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:10000',
            'content_type' => 'required|string|in:catch_comments,point_comments,catch_descriptions,point_descriptions,user_bio',
        ]);

        try {
            $result = $this->moderationService->moderateText(
                $request->text,
                $request->content_type
            );

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Text moderation failed', [
                'error' => $e->getMessage(),
                'content_type' => $request->content_type
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Moderation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Moderate image content
     */
    public function moderateImage(Request $request): JsonResponse
    {
        $request->validate([
            'image_path' => 'required|string',
            'content_type' => 'required|string|in:catch_photos,point_photos,user_avatar',
        ]);

        try {
            $result = $this->moderationService->moderateImage(
                $request->image_path,
                $request->content_type
            );

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Image moderation failed', [
                'error' => $e->getMessage(),
                'content_type' => $request->content_type,
                'image_path' => $request->image_path
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Moderation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request moderation for content
     */
    public function requestModeration(Request $request): JsonResponse
    {
        $request->validate([
            'content_type' => 'required|string',
            'content_id' => 'required|string',
            'content' => 'required|string',
            'format' => 'required|string|in:text,image',
        ]);

        try {
            event(new ContentModerationRequested(
                $request->content_type,
                $request->content_id,
                $request->content,
                $request->format,
                Auth::id()
            ));

            return response()->json([
                'success' => true,
                'message' => 'Moderation requested successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to request moderation', [
                'error' => $e->getMessage(),
                'content_type' => $request->content_type,
                'content_id' => $request->content_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to request moderation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending moderation items (admin only)
     */
    public function pending(): JsonResponse
    {
        $this->authorize('admin');

        $pendingCatches = CatchRecord::where('moderation_status', 'pending_review')
            ->with(['user', 'point'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $pendingComments = CatchComment::where('moderation_status', 'pending_review')
            ->with(['user', 'catchRecord'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $pendingPoints = Point::where('moderation_status', 'pending_review')
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'catches' => $pendingCatches,
                'comments' => $pendingComments,
                'points' => $pendingPoints,
                'total_pending' => $pendingCatches->count() + $pendingComments->count() + $pendingPoints->count()
            ]
        ]);
    }

    /**
     * Approve content (admin only)
     */
    public function approve(Request $request): JsonResponse
    {
        $this->authorize('admin');

        $request->validate([
            'content_type' => 'required|string|in:catch,comment,point',
            'content_id' => 'required|integer',
        ]);

        try {
            $content = $this->getContentModel($request->content_type, $request->content_id);
            
            if (!$content) {
                return response()->json([
                    'success' => false,
                    'message' => 'Content not found'
                ], 404);
            }

            $content->update([
                'moderation_status' => 'approved',
                'moderated_by' => Auth::id(),
                'moderated_at' => now(),
            ]);

            Log::info('Content approved by admin', [
                'content_type' => $request->content_type,
                'content_id' => $request->content_id,
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Content approved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to approve content', [
                'error' => $e->getMessage(),
                'content_type' => $request->content_type,
                'content_id' => $request->content_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject content (admin only)
     */
    public function reject(Request $request): JsonResponse
    {
        $this->authorize('admin');

        $request->validate([
            'content_type' => 'required|string|in:catch,comment,point',
            'content_id' => 'required|integer',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $content = $this->getContentModel($request->content_type, $request->content_id);
            
            if (!$content) {
                return response()->json([
                    'success' => false,
                    'message' => 'Content not found'
                ], 404);
            }

            $content->update([
                'moderation_status' => 'rejected',
                'moderated_by' => Auth::id(),
                'moderated_at' => now(),
                'moderation_result' => [
                    'approved' => false,
                    'confidence' => 1.0,
                    'reason' => $request->reason ?? 'Rejected by admin',
                    'categories' => ['admin_rejection'],
                    'raw_response' => null,
                ]
            ]);

            Log::info('Content rejected by admin', [
                'content_type' => $request->content_type,
                'content_id' => $request->content_id,
                'admin_id' => Auth::id(),
                'reason' => $request->reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Content rejected successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reject content', [
                'error' => $e->getMessage(),
                'content_type' => $request->content_type,
                'content_id' => $request->content_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get content model by type and ID
     */
    private function getContentModel(string $contentType, int $contentId)
    {
        return match ($contentType) {
            'catch' => CatchRecord::find($contentId),
            'comment' => CatchComment::find($contentId),
            'point' => Point::find($contentId),
            default => null,
        };
    }

    /**
     * Clear moderation cache (admin only)
     */
    public function clearCache(): JsonResponse
    {
        $this->authorize('admin');

        try {
            $this->moderationService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Moderation cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear moderation cache', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get moderation configuration (admin only)
     */
    public function config(): JsonResponse
    {
        $this->authorize('admin');

        $config = config('ai_moderation', []);

        // Remove sensitive information
        unset($config['providers']['yandexgpt']['api_key']);
        unset($config['providers']['gigachat']['api_key']);
        unset($config['providers']['chatgpt']['api_key']);
        unset($config['providers']['deepseek']['api_key']);

        return response()->json([
            'success' => true,
            'data' => $config
        ]);
    }

    /**
     * Test AI provider connection (admin only)
     */
    public function testProvider(Request $request): JsonResponse
    {
        $this->authorize('admin');

        $request->validate([
            'provider' => 'required|string|in:yandexgpt,gigachat,chatgpt,deepseek',
        ]);

        try {
            $testText = 'This is a test message for AI moderation.';
            $result = $this->moderationService->moderateText($testText, 'catch_comments');

            return response()->json([
                'success' => true,
                'message' => 'Provider test successful',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('AI provider test failed', [
                'provider' => $request->provider,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Provider test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}