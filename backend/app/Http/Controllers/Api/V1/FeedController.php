<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CatchRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class FeedController extends Controller
{
    /**
     * @OA\Get(
     *     path="/feed",
     *     tags={"Feed"},
     *     summary="Получить ленту уловов",
     *     description="Возвращает ленту уловов с возможностью фильтрации",
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Тип ленты",
     *         required=false,
     *         @OA\Schema(type="string", enum={"all", "following", "nearby"}, default="all")
     *     ),
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         description="Широта для фильтра nearby",
     *         required=false,
     *         @OA\Schema(type="number", format="float", minimum=-90, maximum=90)
     *     ),
     *     @OA\Parameter(
     *         name="longitude",
     *         in="query",
     *         description="Долгота для фильтра nearby",
     *         required=false,
     *         @OA\Schema(type="number", format="float", minimum=-180, maximum=180)
     *     ),
     *     @OA\Parameter(
     *         name="radius",
     *         in="query",
     *         description="Радиус поиска в км",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=1000, default=50)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Количество записей на странице",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=20)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное получение ленты",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                     @OA\Property(property="username", type="string", example="ivan_ivanov")
     *                 ),
     *                 @OA\Property(property="species", type="string", example="Щука"),
     *                 @OA\Property(property="weight", type="number", format="float", example=2.5),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="total", type="integer", example=100)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация для типа 'following'"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации параметров"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'nullable|string|in:all,following,nearby',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:1|max:1000', // km
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $user = Auth::guard('api')->user();
        $type = $request->get('type', 'all');
        $limit = $request->get('limit', 20);

        // Группируем уловы по трекам
        $query = CatchRecord::with(['user', 'track'])
            ->whereNotNull('user_id')
            ->whereNotNull('track_id')
            ->active();

        // Apply filters based on type
        switch ($type) {
            case 'following':
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Authentication required for following feed'
                    ], 401);
                }
                // For now, just return all catches since following system is not fully implemented
                break;
                
            case 'nearby':
                // For now, just return all catches since location system is not fully implemented
                break;
                
            case 'all':
            default:
                // No additional filters for 'all'
                break;
        }

        // Получаем уловы с треками
        $catches = $query->orderBy('created_at', 'desc')
                        ->paginate($limit * 3); // Увеличиваем лимит, так как будем группировать

        // Группируем уловы по трекам
        $groupedCatches = $catches->getCollection()->groupBy('track_id');
        
        // Преобразуем в формат треков с уловами
        $trackData = $groupedCatches->map(function ($trackCatches, $trackId) use ($user) {
            $firstCatch = $trackCatches->first();
            $track = $firstCatch->track;
            
            return [
                'id' => $track->id,
                'type' => 'track',
                'title' => $track->title,
                'description' => $track->description,
                'status' => $track->status,
                'started_at' => $track->started_at,
                'ended_at' => $track->ended_at,
                'duration_minutes' => $track->getDurationInMinutes(),
                'total_distance' => $track->total_distance,
                'total_catches' => $trackCatches->count(),
                'total_weight' => $trackCatches->sum('weight'),
                'average_weight' => $trackCatches->avg('weight'),
                'user' => [
                    'id' => $firstCatch->user->id,
                    'name' => $firstCatch->user->name,
                    'username' => $firstCatch->user->username ?? '',
                    'photo_url' => $firstCatch->user->photo_url ?? '',
                    'role' => $firstCatch->user->role ?? 'user',
                    'is_premium' => false,
                    'crown_icon_url' => null,
                    'followers_count' => 0,
                    'is_online' => false,
                    'last_seen_at' => null,
                ],
                'catches' => $trackCatches->map(function ($catch) use ($user) {
                    return $this->transformCatch($catch, $user);
                })->values(),
                'track_points' => $track->track_points ?? [],
                'smartwatch_data' => $track->smartwatch_data ?? [],
                'created_at' => $firstCatch->created_at,
                'updated_at' => $firstCatch->updated_at,
            ];
        })->values();

        // Применяем пагинацию к сгруппированным данным
        $totalTracks = $trackData->count();
        $perPage = $limit;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedTracks = $trackData->slice($offset, $perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $paginatedTracks,
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $totalTracks,
                'last_page' => ceil($totalTracks / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $totalTracks),
            ]
        ]);
    }

    /**
     * Get user's personal feed.
     */
    public function personal(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $limit = $request->get('limit', 20);

        $catches = CatchRecord::with([
            'user'
        ])->where('user_id', $user->id)
          ->active()
          ->orderBy('created_at', 'desc')
          ->paginate($limit);

        $catches->getCollection()->transform(function ($catch) use ($user) {
            return $this->transformCatch($catch, $user);
        });

        return response()->json([
            'success' => true,
            'data' => $catches
        ]);
    }

    /**
     * Get nearby catches.
     */
    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:1|max:1000',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $radius = $request->get('radius', 50); // Default 50km
        $limit = $request->get('limit', 20);

        $user = Auth::guard('api')->user();
        $catches = CatchRecord::with([
            'user'
        ])->active()->visible($user ? $user->id : null)
          ->orderBy('created_at', 'desc')
          ->paginate($limit);
        $catches->getCollection()->transform(function ($catch) use ($user) {
            return $this->transformCatch($catch, $user);
        });

        return response()->json([
            'success' => true,
            'data' => $catches
        ]);
    }

    /**
     * Get following feed.
     */
    public function following(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $limit = $request->get('limit', 20);

        $followingIds = $user->following()->pluck('users.id')->toArray();
        $followingIds[] = $user->id; // Include own posts

        $catches = CatchRecord::with([
            'user'
        ])->whereIn('user_id', $followingIds)
          ->active()->visible($user->id)
          ->orderBy('created_at', 'desc')
          ->paginate($limit);

        $catches->getCollection()->transform(function ($catch) use ($user) {
            return $this->transformCatch($catch, $user);
        });

        return response()->json([
            'success' => true,
            'data' => $catches
        ]);
    }

    /**
     * Transform catch record for API response.
     */
    private function transformCatch(CatchRecord $catch, ?User $user): array
    {
        return [
            'id' => $catch->id,
            'user' => [
                'id' => $catch->user->id,
                'name' => $catch->user->name,
                'username' => $catch->user->username ?? '',
                'photo_url' => $catch->user->photo_url ?? '',
                'role' => $catch->user->role ?? 'user',
                'is_premium' => false,
                'crown_icon_url' => null,
                'followers_count' => 0,
                'is_online' => false,
                'last_seen_at' => null,
            ],
            'species' => $catch->species,
            'weight' => $catch->weight,
            'length' => $catch->length,
            'style' => $catch->style,
            'lure' => $catch->lure,
            'tackle' => $catch->tackle,
            'notes' => $catch->notes,
            'photo_url' => $catch->photo_url,
            'additional_photos' => $catch->additional_photos,
            'caught_at' => $catch->caught_at,
            'likes_count' => $catch->likes()->count(),
            'comments_count' => $catch->comments()->count(),
            'privacy' => $catch->privacy,
            'point' => null,
            'fish_species' => null,
            'fishing_method' => null,
            'fishing_location' => null,
            'weather' => [
                'temperature' => $catch->temperature,
                'pressure' => $catch->pressure,
                'wind_speed' => $catch->wind_speed,
                'cloudiness' => $catch->cloudiness,
                'precipitation' => $catch->precipitation,
                'wind_direction' => $catch->wind_direction,
            ],
            'liked_by_me' => $user ? $catch->likes()->where('user_id', $user->id)->exists() : false,
            'created_at' => $catch->created_at,
            'updated_at' => $catch->updated_at,
        ];
    }
}

