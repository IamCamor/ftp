<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * Get events list with filters
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|in:exhibition,competition,workshop,meeting',
            'status' => 'nullable|in:draft,published,cancelled,completed',
            'city' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius_km' => 'nullable|integer|min:1|max:1000',
            'upcoming' => 'nullable|boolean',
            'search' => 'nullable|string|max:255',
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
            $query = Event::with(['organizer', 'subscriptions' => function($q) {
                $q->where('user_id', Auth::id());
            }]);

            // Apply filters
            if ($request->type) {
                $query->byType($request->type);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            } else {
                $query->published()->approved();
            }

            if ($request->city) {
                $query->byCity($request->city);
            }

            if ($request->region) {
                $query->byRegion($request->region);
            }

            if ($request->latitude && $request->longitude) {
                $radius = $request->radius_km ?? 50;
                $query->inRadius($request->latitude, $request->longitude, $radius);
            }

            if ($request->upcoming) {
                $query->upcoming();
            }

            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%')
                      ->orWhere('organizer', 'like', '%' . $request->search . '%');
                });
            }

            // Sort by event start date
            $query->orderBy('event_start', 'asc');

            $perPage = $request->per_page ?? 20;
            $events = $query->paginate($perPage);

            // Transform events
            $transformedData = $events->toArray();
            $transformedData['data'] = collect($transformedData['data'])->map(function ($event) {
                return $this->transformEvent((object)$event);
            })->toArray();

            return response()->json([
                'success' => true,
                'data' => $transformedData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single event
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $event = Event::with(['organizer', 'subscriptions' => function($q) {
                $q->where('user_id', Auth::id());
            }])->findOrFail($id);

            // Increment views count
            $event->incrementViews();

            return response()->json([
                'success' => true,
                'data' => $this->transformEvent($event, true)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new event (Premium users only)
     */
    public function store(Request $request): JsonResponse
    {
        // Check if user is premium
        if (Auth::user()->role !== 'premium') {
            return response()->json([
                'success' => false,
                'message' => 'Only premium users can create events'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'type' => 'required|in:exhibition,competition,workshop,meeting',
            'organizer' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_km' => 'nullable|integer|min:1|max:1000',
            'registration_start' => 'nullable|date|after:now',
            'registration_end' => 'nullable|date|after:registration_start',
            'event_start' => 'required|date|after:now',
            'event_end' => 'required|date|after:event_start',
            'is_all_day' => 'boolean',
            'max_participants' => 'nullable|integer|min:1',
            'entry_fee' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'requires_registration' => 'boolean',
            'is_public' => 'boolean',
            'cover_image' => 'nullable|string|max:500',
            'gallery' => 'nullable|array',
            'gallery.*' => 'string|max:500',
            'documents' => 'nullable|array',
            'documents.*' => 'string|max:500',
            'rules' => 'nullable|string|max:5000',
            'prizes' => 'nullable|string|max:5000',
            'schedule' => 'nullable|string|max:5000',
            'notifications_enabled' => 'boolean',
            'reminders_enabled' => 'boolean',
            'allow_comments' => 'boolean',
            'allow_sharing' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $event = Event::create([
                'title' => $request->title,
                'description' => $request->description,
                'type' => $request->type,
                'organizer' => $request->organizer,
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
                'website' => $request->website,
                'address' => $request->address,
                'city' => $request->city,
                'region' => $request->region,
                'country' => $request->country,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius_km' => $request->radius_km ?? 50,
                'registration_start' => $request->registration_start,
                'registration_end' => $request->registration_end,
                'event_start' => $request->event_start,
                'event_end' => $request->event_end,
                'is_all_day' => $request->is_all_day ?? false,
                'max_participants' => $request->max_participants,
                'entry_fee' => $request->entry_fee,
                'currency' => $request->currency ?? 'RUB',
                'requires_registration' => $request->requires_registration ?? false,
                'is_public' => $request->is_public ?? true,
                'cover_image' => $request->cover_image,
                'gallery' => $request->gallery,
                'documents' => $request->documents,
                'rules' => $request->rules,
                'prizes' => $request->prizes,
                'schedule' => $request->schedule,
                'notifications_enabled' => $request->notifications_enabled ?? true,
                'reminders_enabled' => $request->reminders_enabled ?? true,
                'allow_comments' => $request->allow_comments ?? true,
                'allow_sharing' => $request->allow_sharing ?? true,
                'tags' => $request->tags,
                'categories' => $request->categories,
                'organizer_id' => Auth::id(),
                'status' => 'published',
                'moderation_status' => 'pending',
            ]);

            // Auto-subscribe creator to the event
            EventSubscription::create([
                'user_id' => Auth::id(),
                'event_id' => $event->id,
                'status' => 'subscribed',
                'notifications_enabled' => true,
                'reminders_enabled' => true,
                'news_enabled' => true,
                'is_attending' => true,
                'subscribed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully',
                'data' => $this->transformEvent($event)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transform event data
     */
    private function transformEvent(Event $event, bool $detailed = false): array
    {
        $data = [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'type' => $event->type,
            'type_description' => $event->type_description,
            'organizer' => $event->organizer,
            'contact_email' => $event->contact_email,
            'contact_phone' => $event->contact_phone,
            'website' => $event->website,
            'address' => $event->address,
            'city' => $event->city,
            'region' => $event->region,
            'country' => $event->country,
            'latitude' => $event->latitude,
            'longitude' => $event->longitude,
            'radius_km' => $event->radius_km,
            'event_start' => $event->event_start?->toISOString(),
            'event_end' => $event->event_end?->toISOString(),
            'is_all_day' => $event->is_all_day,
            'max_participants' => $event->max_participants,
            'current_participants' => $event->current_participants,
            'available_spots' => $event->available_spots,
            'entry_fee' => $event->entry_fee,
            'currency' => $event->currency,
            'requires_registration' => $event->requires_registration,
            'is_public' => $event->is_public,
            'cover_image' => $event->cover_image,
            'gallery' => $event->gallery,
            'documents' => $event->documents,
            'status' => $event->status,
            'status_description' => $event->status_description,
            'moderation_status' => $event->moderation_status,
            'views_count' => $event->views_count,
            'subscribers_count' => $event->subscribers_count,
            'shares_count' => $event->shares_count,
            'rating' => $event->rating,
            'reviews_count' => $event->reviews_count,
            'notifications_enabled' => $event->notifications_enabled,
            'reminders_enabled' => $event->reminders_enabled,
            'allow_comments' => $event->allow_comments,
            'allow_sharing' => $event->allow_sharing,
            'tags' => $event->tags,
            'categories' => $event->categories,
            'is_upcoming' => $event->isUpcoming(),
            'is_ongoing' => $event->isOngoing(),
            'is_completed' => $event->isCompleted(),
            'is_registration_open' => $event->isRegistrationOpen(),
            'has_available_spots' => $event->hasAvailableSpots(),
            'created_at' => $event->created_at->toISOString(),
            'updated_at' => $event->updated_at->toISOString(),
        ];

        // Add user-specific data
        if (Auth::check()) {
            $subscription = $event->subscriptions->first();
            $data['user_subscription'] = $subscription ? [
                'status' => $subscription->status,
                'status_description' => $subscription->status_description,
                'notifications_enabled' => $subscription->notifications_enabled,
                'reminders_enabled' => $subscription->reminders_enabled,
                'news_enabled' => $subscription->news_enabled,
                'is_attending' => $subscription->is_attending,
                'subscribed_at' => $subscription->subscribed_at->toISOString(),
            ] : null;
        }

        // Add detailed information if requested
        if ($detailed) {
            $data['rules'] = $event->rules;
            $data['prizes'] = $event->prizes;
            $data['schedule'] = $event->schedule;
            $data['registration_start'] = $event->registration_start?->toISOString();
            $data['registration_end'] = $event->registration_end?->toISOString();
            $data['organizer_info'] = [
                'id' => $event->organizer_id,
                'name' => $event->organizer,
                'email' => $event->contact_email,
                'phone' => $event->contact_phone,
            ];
        }

        return $data;
    }
}
