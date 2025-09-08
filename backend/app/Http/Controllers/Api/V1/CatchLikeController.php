<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CatchLike;
use App\Models\CatchRecord;
use App\Models\Bonus;
use App\Models\Notification;
use Illuminate\Http\Request;

class CatchLikeController extends Controller
{
    public function toggle(Request $request, $id)
    {
        $catch = CatchRecord::findOrFail($id);
        $user = $request->user();

        $like = CatchLike::where('catch_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            CatchLike::create([
                'catch_id' => $id,
                'user_id' => $user->id,
            ]);
            $liked = true;

            // Award bonus to catch owner for receiving like
            if ($catch->user_id !== $user->id) {
                Bonus::create([
                    'user_id' => $catch->user_id,
                    'action' => 'like_received',
                    'amount' => 2,
                    'meta' => ['catch_id' => $id, 'liker_id' => $user->id]
                ]);

                Notification::create([
                    'user_id' => $catch->user_id,
                    'type' => 'new_like',
                    'title' => 'Новый лайк!',
                    'body' => $user->name . ' поставил лайк вашему улову.'
                ]);
            }
        }

        $likesCount = CatchLike::where('catch_id', $id)->count();

        return response()->json([
            'liked' => $liked,
            'likes_count' => $likesCount
        ]);
    }
}

