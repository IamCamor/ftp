<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventNews;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EventNewsController extends Controller
{
    /**
     * Get event news
     */
    public function index(Request $request, int $eventId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|in:announcement,update,reminder,result,photo_report,other',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'pinned' => 'nullable|boolean',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $event = Event::findOrFail($eventId);

            $query = EventNews::with(['user'])
                ->where('event_id', $eventId)
                ->published()
                ->approved();

            if ($request->type) {
                $query->byType($request->type);
            }

            if ($request->priority) {
                $query->byPriority($request->priority);
            }

            if ($request->pinned) {
                $query->pinned();
            }

            $query->orderBy('is_pinned', 'desc')
                  ->orderBy('published_at', 'desc');

            $perPage = $request->per_page ?? 20;
            $news = $query->paginate($perPage);

            // Transform news
            $news->getCollection()->transform(function ($newsItem) {
                return $this->transformNews($newsItem);
            });

            return response()->json([
                'success' => true,
                'data' => $news
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch event news',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single news item
     */
    public function show(Request $request, int $eventId, int $newsId): JsonResponse
    {
        try {
            $news = EventNews::with(['user', 'event'])
                ->where('event_id', $eventId)
                ->where('id', $newsId)
                ->published()
                ->approved()
                ->firstOrFail();

            // Increment views count
            $news->incrementViews();

            return response()->json([
                'success' => true,
                'data' => $this->transformNews($news, true)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'News not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create news (Premium users only)
     */
    public function store(Request $request, int $eventId): JsonResponse
    {
        // Check if user is premium
        if (Auth::user()->role !== 'premium') {
            return response()->json([
                'success' => false,
                'message' => 'Only premium users can create event news'
            ], 403);
        }

        try {
            $event = Event::findOrFail($eventId);

            // Check if user is the organizer
            if ($event->organizer_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create news for your own events'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string|max:10000',
                'excerpt' => 'nullable|string|max:500',
                'cover_image' => 'nullable|string|max:500',
                'gallery' => 'nullable|array',
                'gallery.*' => 'string|max:500',
                'attachments' => 'nullable|array',
                'attachments.*' => 'string|max:500',
                'type' => 'required|in:announcement,update,reminder,result,photo_report,other',
                'priority' => 'required|in:low,normal,high,urgent',
                'scheduled_at' => 'nullable|date|after:now',
                'is_pinned' => 'boolean',
                'allow_comments' => 'boolean',
                'allow_sharing' => 'boolean',
                'send_notifications' => 'boolean',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $news = EventNews::create([
                'event_id' => $eventId,
                'user_id' => Auth::id(),
                'title' => $request->title,
                'content' => $request->content,
                'excerpt' => $request->excerpt,
                'cover_image' => $request->cover_image,
                'gallery' => $request->gallery,
                'attachments' => $request->attachments,
                'type' => $request->type,
                'priority' => $request->priority,
                'status' => 'published',
                'moderation_status' => 'pending',
                'published_at' => $request->scheduled_at ? null : now(),
                'scheduled_at' => $request->scheduled_at,
                'is_pinned' => $request->is_pinned ?? false,
                'allow_comments' => $request->allow_comments ?? true,
                'allow_sharing' => $request->allow_sharing ?? true,
                'send_notifications' => $request->send_notifications ?? true,
                'tags' => $request->tags,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'News created successfully',
                'data' => $this->transformNews($news)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create news',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update news (Premium users only)
     */
    public function update(Request $request, int $eventId, int $newsId): JsonResponse
    {
        try {
            $news = EventNews::where('event_id', $eventId)
                ->where('id', $newsId)
                ->firstOrFail();

            // Check if user is the author or organizer
            if ($news->user_id !== Auth::id() && $news->event->organizer_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only edit your own news'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string|max:10000',
                'excerpt' => 'nullable|string|max:500',
                'cover_image' => 'nullable|string|max:500',
                'gallery' => 'nullable|array',
                'gallery.*' => 'string|max:500',
                'attachments' => 'nullable|array',
                'attachments.*' => 'string|max:500',
                'type' => 'sometimes|required|in:announcement,update,reminder,result,photo_report,other',
                'priority' => 'sometimes|required|in:low,normal,high,urgent',
                'scheduled_at' => 'nullable|date',
                'is_pinned' => 'boolean',
                'allow_comments' => 'boolean',
                'allow_sharing' => 'boolean',
                'send_notifications' => 'boolean',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $news->update($request->only([
                'title', 'content', 'excerpt', 'cover_image', 'gallery', 'attachments',
                'type', 'priority', 'scheduled_at', 'is_pinned', 'allow_comments',
                'allow_sharing', 'send_notifications', 'tags'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'News updated successfully',
                'data' => $this->transformNews($news)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update news',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete news (Premium users only)
     */
    public function destroy(Request $request, int $eventId, int $newsId): JsonResponse
    {
        try {
            $news = EventNews::where('event_id', $eventId)
                ->where('id', $newsId)
                ->firstOrFail();

            // Check if user is the author or organizer
            if ($news->user_id !== Auth::id() && $news->event->organizer_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own news'
                ], 403);
            }

            $news->delete();

            return response()->json([
                'success' => true,
                'message' => 'News deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete news',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pin news (Premium users only)
     */
    public function pin(Request $request, int $eventId, int $newsId): JsonResponse
    {
        try {
            $news = EventNews::where('event_id', $eventId)
                ->where('id', $newsId)
                ->firstOrFail();

            // Check if user is the organizer
            if ($news->event->organizer_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only pin news for your own events'
                ], 403);
            }

            $news->pin();

            return response()->json([
                'success' => true,
                'message' => 'News pinned successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to pin news',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unpin news (Premium users only)
     */
    public function unpin(Request $request, int $eventId, int $newsId): JsonResponse
    {
        try {
            $news = EventNews::where('event_id', $eventId)
                ->where('id', $newsId)
                ->firstOrFail();

            // Check if user is the organizer
            if ($news->event->organizer_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only unpin news for your own events'
                ], 403);
            }

            $news->unpin();

            return response()->json([
                'success' => true,
                'message' => 'News unpinned successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unpin news',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transform news data
     */
    private function transformNews(EventNews $news, bool $detailed = false): array
    {
        $data = [
            'id' => $news->id,
            'title' => $news->title,
            'content' => $news->content,
            'excerpt' => $news->getExcerptOrGenerated(),
            'cover_image' => $news->cover_image,
            'gallery' => $news->gallery,
            'attachments' => $news->attachments,
            'type' => $news->type,
            'type_description' => $news->type_description,
            'priority' => $news->priority,
            'priority_description' => $news->priority_description,
            'status' => $news->status,
            'status_description' => $news->status_description,
            'moderation_status' => $news->moderation_status,
            'moderation_status_description' => $news->moderation_status_description,
            'published_at' => $news->published_at?->toISOString(),
            'scheduled_at' => $news->scheduled_at?->toISOString(),
            'is_pinned' => $news->is_pinned,
            'views_count' => $news->views_count,
            'likes_count' => $news->likes_count,
            'shares_count' => $news->shares_count,
            'comments_count' => $news->comments_count,
            'allow_comments' => $news->allow_comments,
            'allow_sharing' => $news->allow_sharing,
            'tags' => $news->tags,
            'reading_time' => $news->reading_time,
            'is_published' => $news->isPublished(),
            'is_scheduled' => $news->isScheduled(),
            'is_approved' => $news->isApproved(),
            'created_at' => $news->created_at->toISOString(),
            'updated_at' => $news->updated_at->toISOString(),
            'author' => [
                'id' => $news->user->id,
                'name' => $news->user->name,
                'username' => $news->user->username,
                'photo_url' => $news->user->photo_url,
            ],
        ];

        // Add detailed information if requested
        if ($detailed) {
            $data['event'] = [
                'id' => $news->event->id,
                'title' => $news->event->title,
                'type' => $news->event->type,
                'type_description' => $news->event->type_description,
                'organizer' => $news->event->organizer,
                'city' => $news->event->city,
                'region' => $news->event->region,
                'event_start' => $news->event->event_start?->toISOString(),
                'event_end' => $news->event->event_end?->toISOString(),
            ];
        }

        return $data;
    }
}
