<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CatchRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class SearchController extends Controller
{
    /**
     * @OA\Get(
     *     path="/search/users",
     *     tags={"Search"},
     *     summary="Поиск пользователей",
     *     description="Поиск пользователей по имени или username",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Поисковый запрос",
     *         required=true,
     *         @OA\Schema(type="string", example="Иван")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Количество результатов",
     *         @OA\Schema(type="integer", example=20, minimum=1, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Результаты поиска пользователей",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                 @OA\Property(property="username", type="string", example="ivan_ivanov"),
     *                 @OA\Property(property="avatar_url", type="string", example="https://example.com/avatar.jpg"),
     *                 @OA\Property(property="followers_count", type="integer", example=150),
     *                 @OA\Property(property="catches_count", type="integer", example=25),
     *                 @OA\Property(property="is_following", type="boolean", example=false)
     *             )),
     *             @OA\Property(property="total", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Пустой поисковый запрос"
     *     )
     * )
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $users = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('username', 'LIKE', "%{$query}%")
            ->where('is_blocked', false)
            ->limit(20)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'photo_url' => $user->photo_url,
                    'is_premium' => $user->is_premium,
                    'crown_icon_url' => $user->crown_icon_url,
                    'followers_count' => $user->followers_count,
                    'is_online' => $user->is_online,
                    'last_seen_at' => $user->last_seen_at,
                ];
            });

        return response()->json($users);
    }

    /**
     * Search catches
     */
    public function searchCatches(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $catches = CatchRecord::with(['user'])
            ->where(function ($q) use ($query) {
                $q->where('species', 'LIKE', "%{$query}%")
                  ->orWhere('notes', 'LIKE', "%{$query}%")
                  ->orWhere('style', 'LIKE', "%{$query}%")
                  ->orWhere('lure', 'LIKE', "%{$query}%")
                  ->orWhere('tackle', 'LIKE', "%{$query}%");
            })
            ->where('privacy', 'all')
            ->where('is_blocked', false)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($catch) {
                return [
                    'id' => $catch->id,
                    'user' => [
                        'id' => $catch->user->id,
                        'name' => $catch->user->name,
                        'username' => $catch->user->username,
                        'photo_url' => $catch->user->photo_url,
                        'is_premium' => $catch->user->is_premium,
                        'crown_icon_url' => $catch->user->crown_icon_url,
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
                    'main_photo' => $catch->main_photo,
                    'privacy' => $catch->privacy,
                    'likes_count' => $catch->likes_count,
                    'comments_count' => $catch->comments_count,
                    'liked_by_me' => $catch->liked_by_me,
                    'fish_species' => null,
                    'fishing_method' => null,
                    'fishing_location' => null,
                    'created_at' => $catch->created_at,
                ];
            });

        return response()->json($catches);
    }
}
