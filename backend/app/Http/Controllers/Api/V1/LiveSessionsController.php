<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LiveSessionsController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);
        $status = $request->get('status', 'live');

        $sessions = LiveSession::with(['user', 'event'])
            ->where('status', $status)
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return response()->json($sessions);
    }

    public function show($id)
    {
        $session = LiveSession::with(['user', 'event', 'viewers'])
            ->findOrFail($id);

        return response()->json($session);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'event_id' => 'nullable|exists:events,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'fields' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'live';
        $data['started_at'] = \Carbon\Carbon::now();

        $session = LiveSession::create($data);

        return response()->json($session, 201);
    }

    public function start(Request $request, $id)
    {
        $session = LiveSession::findOrFail($id);
        $user = $request->user();

        if ($session->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($session->status !== 'scheduled') {
            return response()->json(['message' => 'Session cannot be started'], 400);
        }

        $session->update([
            'status' => 'live',
            'started_at' => \Carbon\Carbon::now()
        ]);

        return response()->json($session);
    }

    public function end(Request $request, $id)
    {
        $session = LiveSession::findOrFail($id);
        $user = $request->user();

        if ($session->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($session->status !== 'live') {
            return response()->json(['message' => 'Session is not live'], 400);
        }

        $session->update([
            'status' => 'ended',
            'ended_at' => \Carbon\Carbon::now()
        ]);

        return response()->json($session);
    }

    public function join(Request $request, $id)
    {
        $session = LiveSession::findOrFail($id);
        $user = $request->user();

        if ($session->status !== 'live') {
            return response()->json(['message' => 'Session is not live'], 400);
        }

        // Check if user is already viewing
        $existingViewer = $session->viewers()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if ($existingViewer) {
            return response()->json(['message' => 'Already viewing'], 400);
        }

        // Add viewer
        $session->viewers()->attach($user->id, [
            'joined_at' => \Carbon\Carbon::now()
        ]);

        // Update viewers count
        $session->increment('viewers_count');

        return response()->json(['message' => 'Joined live session']);
    }

    public function leave(Request $request, $id)
    {
        $session = LiveSession::findOrFail($id);
        $user = $request->user();

        $viewer = $session->viewers()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if (!$viewer) {
            return response()->json(['message' => 'Not viewing'], 400);
        }

        // Calculate watch duration
        $watchDuration = \Carbon\Carbon::now()->diffInSeconds($viewer->pivot->joined_at);

        // Update viewer record
        $session->viewers()->updateExistingPivot($user->id, [
            'left_at' => \Carbon\Carbon::now(),
            'watch_duration' => $watchDuration
        ]);

        // Update viewers count
        $session->decrement('viewers_count');

        return response()->json(['message' => 'Left live session']);
    }
}
