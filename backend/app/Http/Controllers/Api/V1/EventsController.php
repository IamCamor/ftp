<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EventsController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);
        $status = $request->get('status', 'published');

        $query = Event::with(['organizer', 'group', 'participants'])
            ->where('status', $status)
            ->where('start_at', '>=', \Carbon\Carbon::now());

        if ($request->has('group_id')) {
            $query->where('group_id', $request->get('group_id'));
        }

        $events = $query->orderBy('start_at', 'asc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return response()->json($events);
    }

    public function show($id)
    {
        $event = Event::with(['organizer', 'group', 'participants', 'liveSessions'])
            ->findOrFail($id);

        return response()->json($event);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'location_name' => 'nullable|string|max:191',
            'start_at' => 'required|date|after:now',
            'end_at' => 'nullable|date|after:start_at',
            'max_participants' => 'nullable|integer|min:1',
            'group_id' => 'nullable|exists:groups,id',
            'cover_url' => 'nullable|string|max:512',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'fields' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['organizer_id'] = $request->user()->id;

        $event = Event::create($data);

        // Add organizer as confirmed participant
        $event->participants()->attach($request->user()->id, [
            'status' => 'confirmed'
        ]);

        // Create chat for event
        $event->chat()->create([
            'name' => "Чат события: {$event->title}",
            'type' => 'event'
        ]);

        return response()->json($event, 201);
    }

    public function join(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $user = $request->user();

        if ($event->isParticipant($user->id)) {
            return response()->json(['message' => 'Already participating'], 400);
        }

        if ($event->max_participants && $event->participants_count >= $event->max_participants) {
            return response()->json(['message' => 'Event is full'], 400);
        }

        $event->participants()->attach($user->id, [
            'status' => 'confirmed'
        ]);

        return response()->json(['message' => 'Joined event successfully']);
    }

    public function leave(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $user = $request->user();

        if (!$event->isParticipant($user->id)) {
            return response()->json(['message' => 'Not participating'], 400);
        }

        if ($event->isOrganizer($user->id)) {
            return response()->json(['message' => 'Organizer cannot leave event'], 403);
        }

        $event->participants()->detach($user->id);

        return response()->json(['message' => 'Left event successfully']);
    }
}
