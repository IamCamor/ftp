<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CatchRecord;
use App\Models\Bonus;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CatchController extends Controller
{
    public function show($id)
    {
        $catch = CatchRecord::with(['user', 'likes', 'comments.user'])
            ->findOrFail($id);

        return response()->json([
            'id' => $catch->id,
            'user' => [
                'id' => $catch->user->id,
                'name' => $catch->user->name,
                'username' => $catch->user->username,
                'photo_url' => $catch->user->photo_url,
            ],
            'lat' => $catch->lat,
            'lng' => $catch->lng,
            'species' => $catch->species,
            'length' => $catch->length,
            'weight' => $catch->weight,
            'style' => $catch->style,
            'lure' => $catch->lure,
            'tackle' => $catch->tackle,
            'notes' => $catch->notes,
            'photo_url' => $catch->photo_url,
            'privacy' => $catch->privacy,
            'caught_at' => $catch->caught_at,
            'likes_count' => $catch->likes_count,
            'comments_count' => $catch->comments_count,
            'comments' => $catch->comments->where('is_approved', true)->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'username' => $comment->user->username,
                        'photo_url' => $comment->user->photo_url,
                    ],
                    'created_at' => $comment->created_at,
                ];
            }),
            'created_at' => $catch->created_at,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'species' => 'nullable|string|max:120',
            'length' => 'nullable|numeric|min:0|max:999.99',
            'weight' => 'nullable|numeric|min:0|max:999.99',
            'style' => 'nullable|string|max:120',
            'lure' => 'nullable|string|max:120',
            'tackle' => 'nullable|string|max:120',
            'notes' => 'nullable|string',
            'photo_url' => 'nullable|string|max:512',
            'privacy' => 'in:all,friends,me',
            'caught_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'fields' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['user_id'] = $request->user()->id;

        // Parse caught_at if provided
        if ($request->has('caught_at') && $request->caught_at) {
            try {
                $data['caught_at'] = Carbon::parse($request->caught_at)->toDateTimeString();
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Invalid date format for caught_at'
                ], 422);
            }
        }

        $catch = CatchRecord::create($data);

        // Award bonus for adding catch
        Bonus::create([
            'user_id' => $request->user()->id,
            'action' => 'add_catch',
            'amount' => 10,
            'meta' => ['catch_id' => $catch->id]
        ]);

        // Create notification for the user
        Notification::create([
            'user_id' => $request->user()->id,
            'type' => 'catch_added',
            'title' => 'Улов добавлен!',
            'body' => 'Вы получили 10 бонусов за добавление улова.'
        ]);

        return response()->json($catch, 201);
    }
}

