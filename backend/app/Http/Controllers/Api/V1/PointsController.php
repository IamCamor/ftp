<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Point;
use App\Models\Bonus;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PointsController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 100);
        $bbox = $request->get('bbox');

        $query = Point::with(['user', 'media'])
            ->where('privacy', 'all');

        if ($bbox) {
            $coords = explode(',', $bbox);
            if (count($coords) === 4) {
                [$lng1, $lat1, $lng2, $lat2] = array_map('floatval', $coords);
                $query->whereBetween('lat', [min($lat1, $lat2), max($lat1, $lat2)])
                      ->whereBetween('lng', [min($lng1, $lng2), max($lng1, $lng2)]);
            }
        }

        $points = $query->limit($limit)->get()->map(function ($point) {
            return [
                'id' => $point->id,
                'user' => [
                    'id' => $point->user->id,
                    'name' => $point->user->name,
                    'username' => $point->user->username,
                    'photo_url' => $point->user->photo_url,
                ],
                'lat' => $point->lat,
                'lng' => $point->lng,
                'title' => $point->title,
                'description' => $point->description,
                'cover_url' => $point->cover_url,
                'privacy' => $point->privacy,
                'media_count' => $point->media->count(),
                'created_at' => $point->created_at,
            ];
        });

        return response()->json($points);
    }

    public function show($id)
    {
        $point = Point::with(['user', 'media'])->findOrFail($id);

        return response()->json([
            'id' => $point->id,
            'user' => [
                'id' => $point->user->id,
                'name' => $point->user->name,
                'username' => $point->user->username,
                'photo_url' => $point->user->photo_url,
            ],
            'lat' => $point->lat,
            'lng' => $point->lng,
            'title' => $point->title,
            'description' => $point->description,
            'cover_url' => $point->cover_url,
            'privacy' => $point->privacy,
            'media' => $point->media->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->url,
                    'created_at' => $media->created_at,
                ];
            }),
            'created_at' => $point->created_at,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'cover_url' => 'nullable|string|max:512',
            'privacy' => 'in:all,friends,me',
            'media' => 'nullable|array',
            'media.*' => 'string|max:512',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'fields' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['user_id'] = $request->user()->id;

        $point = Point::create($data);

        // Add media if provided
        if ($request->has('media') && is_array($request->media)) {
            foreach ($request->media as $mediaUrl) {
                $point->media()->create(['url' => $mediaUrl]);
            }
        }

        // Award bonus for adding point
        Bonus::create([
            'user_id' => $request->user()->id,
            'action' => 'add_point',
            'amount' => 15,
            'meta' => ['point_id' => $point->id]
        ]);

        Notification::create([
            'user_id' => $request->user()->id,
            'type' => 'point_added',
            'title' => 'Точка добавлена!',
            'body' => 'Вы получили 15 бонусов за добавление точки.'
        ]);

        return response()->json($point, 201);
    }

    public function media($id)
    {
        $point = Point::findOrFail($id);
        $media = $point->media;

        return response()->json($media);
    }
}

