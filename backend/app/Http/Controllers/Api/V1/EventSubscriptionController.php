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

class EventSubscriptionController extends Controller
{
    /**
     * Subscribe to event
     */
    public function subscribe(Request $request, int $eventId): JsonResponse
    {
        try {
            $event = Event::findOrFail($eventId);

            // Check if already subscribed
            $existingSubscription = EventSubscription::where('user_id', Auth::id())
                ->where('event_id', $eventId)
                ->first();

            if ($existingSubscription) {
                if ($existingSubscription->status === 'subscribed') {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are already subscribed to this event'
                    ], 400);
                } else {
                    // Resubscribe
                    $existingSubscription->resubscribe();
                    $event->incrementSubscribers();
                }
            } else {
                // Create new subscription
                EventSubscription::create([
                    'user_id' => Auth::id(),
                    'event_id' => $eventId,
                    'status' => 'subscribed',
                    'notifications_enabled' => true,
                    'reminders_enabled' => true,
                    'news_enabled' => true,
                    'subscribed_at' => now(),
                ]);
                $event->incrementSubscribers();
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully subscribed to event'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to subscribe to event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsubscribe from event
     */
    public function unsubscribe(Request $request, int $eventId): JsonResponse
    {
        try {
            $subscription = EventSubscription::where('user_id', Auth::id())
                ->where('event_id', $eventId)
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not subscribed to this event'
                ], 400);
            }

            $subscription->unsubscribe();
            $subscription->event->decrementSubscribers();

            return response()->json([
                'success' => true,
                'message' => 'Successfully unsubscribed from event'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unsubscribe from event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hide event (user won't receive notifications but event remains in catalog)
     */
    public function hide(Request $request, int $eventId): JsonResponse
    {
        try {
            $subscription = EventSubscription::where('user_id', Auth::id())
                ->where('event_id', $eventId)
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not subscribed to this event'
                ], 400);
            }

            $subscription->hide();

            return response()->json([
                'success' => true,
                'message' => 'Event hidden successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to hide event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unhide event
     */
    public function unhide(Request $request, int $eventId): JsonResponse
    {
        try {
            $subscription = EventSubscription::where('user_id', Auth::id())
                ->where('event_id', $eventId)
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not subscribed to this event'
                ], 400);
            }

            $subscription->unhide();

            return response()->json([
                'success' => true,
                'message' => 'Event unhidden successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unhide event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update subscription settings
     */
    public function updateSettings(Request $request, int $eventId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notifications_enabled' => 'boolean',
            'reminders_enabled' => 'boolean',
            'news_enabled' => 'boolean',
            'reminder_hours_before' => 'integer|min:1|max:168',
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $subscription = EventSubscription::where('user_id', Auth::id())
                ->where('event_id', $eventId)
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not subscribed to this event'
                ], 400);
            }

            $subscription->update($request->only([
                'notifications_enabled',
                'reminders_enabled',
                'news_enabled',
                'reminder_hours_before',
                'email_notifications',
                'push_notifications',
                'sms_notifications',
                'notes'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Subscription settings updated successfully',
                'data' => [
                    'status' => $subscription->status,
                    'status_description' => $subscription->status_description,
                    'notifications_enabled' => $subscription->notifications_enabled,
                    'reminders_enabled' => $subscription->reminders_enabled,
                    'news_enabled' => $subscription->news_enabled,
                    'reminder_hours_before' => $subscription->reminder_hours_before,
                    'email_notifications' => $subscription->email_notifications,
                    'push_notifications' => $subscription->push_notifications,
                    'sms_notifications' => $subscription->sms_notifications,
                    'notes' => $subscription->notes,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subscription settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm attendance
     */
    public function confirmAttendance(Request $request, int $eventId): JsonResponse
    {
        try {
            $subscription = EventSubscription::where('user_id', Auth::id())
                ->where('event_id', $eventId)
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not subscribed to this event'
                ], 400);
            }

            $subscription->confirmAttendance();

            return response()->json([
                'success' => true,
                'message' => 'Attendance confirmed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel attendance
     */
    public function cancelAttendance(Request $request, int $eventId): JsonResponse
    {
        try {
            $subscription = EventSubscription::where('user_id', Auth::id())
                ->where('event_id', $eventId)
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not subscribed to this event'
                ], 400);
            }

            $subscription->cancelAttendance();

            return response()->json([
                'success' => true,
                'message' => 'Attendance cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's subscriptions
     */
    public function mySubscriptions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:subscribed,unsubscribed,hidden',
            'upcoming' => 'nullable|boolean',
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
            $query = EventSubscription::with(['event.organizer'])
                ->where('user_id', Auth::id());

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->upcoming) {
                $query->whereHas('event', function($q) {
                    $q->where('event_start', '>', now());
                });
            }

            $query->orderBy('subscribed_at', 'desc');

            $perPage = $request->per_page ?? 20;
            $subscriptions = $query->paginate($perPage);

            // Transform subscriptions
            $subscriptions->getCollection()->transform(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'status_description' => $subscription->status_description,
                    'notifications_enabled' => $subscription->notifications_enabled,
                    'reminders_enabled' => $subscription->reminders_enabled,
                    'news_enabled' => $subscription->news_enabled,
                    'is_attending' => $subscription->is_attending,
                    'subscribed_at' => $subscription->subscribed_at->toISOString(),
                    'event' => [
                        'id' => $subscription->event->id,
                        'title' => $subscription->event->title,
                        'type' => $subscription->event->type,
                        'type_description' => $subscription->event->type_description,
                        'organizer' => $subscription->event->organizer,
                        'city' => $subscription->event->city,
                        'region' => $subscription->event->region,
                        'event_start' => $subscription->event->event_start?->toISOString(),
                        'event_end' => $subscription->event->event_end?->toISOString(),
                        'cover_image' => $subscription->event->cover_image,
                        'is_upcoming' => $subscription->event->isUpcoming(),
                        'is_ongoing' => $subscription->event->isOngoing(),
                        'is_completed' => $subscription->event->isCompleted(),
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $subscriptions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscriptions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
