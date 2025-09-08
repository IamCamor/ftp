<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CatchRecord;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);

        $catches = CatchRecord::with(['user', 'likes', 'comments'])
            ->where('privacy', 'all')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(function ($catch) {
                return [
                    'id' => $catch->id,
                    'user' => [
                        'id' => $catch->user->id,
                        'name' => $catch->user->name,
                        'username' => $catch->user->username,
                        'photo_url' => $catch->user->photo_url,
                    ],
                    'species' => $catch->species,
                    'length' => $catch->length,
                    'weight' => $catch->weight,
                    'style' => $catch->style,
                    'lure' => $catch->lure,
                    'tackle' => $catch->tackle,
                    'notes' => $catch->notes,
                    'photo_url' => $catch->photo_url,
                    'caught_at' => $catch->caught_at,
                    'likes_count' => $catch->likes_count,
                    'comments_count' => $catch->comments_count,
                    'liked_by_me' => false, // Will be set based on auth user
                    'created_at' => $catch->created_at,
                ];
            });

        return response()->json($catches);
    }
}

