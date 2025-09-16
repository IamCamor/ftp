<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Point;
use App\Models\Bonus;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class PointsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/map/points",
     *     tags={"Points"},
     *     summary="Получить точки на карте",
     *     description="Возвращает точки уловов и других объектов для отображения на карте",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Максимальное количество точек",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=1000, default=100)
     *     ),
     *     @OA\Parameter(
     *         name="include_catches",
     *         in="query",
     *         description="Включить уловы в результат",
     *         required=false,
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список точек на карте",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                     @OA\Property(property="username", type="string", example="ivan_ivanov"),
     *                     @OA\Property(property="photo_url", type="string", example="https://example.com/photo.jpg")
     *                 ),
     *                 @OA\Property(property="lat", type="number", format="float", example=55.7558),
     *                 @OA\Property(property="lng", type="number", format="float", example=37.6176),
     *                 @OA\Property(property="title", type="string", example="Улов: Щука"),
     *                 @OA\Property(property="description", type="string", example="Отличный улов!"),
     *                 @OA\Property(property="cover_url", type="string", example="https://example.com/catch.jpg"),
     *                 @OA\Property(property="privacy", type="string", example="all"),
     *                 @OA\Property(property="media_count", type="integer", example=1),
     *                 @OA\Property(property="type", type="string", example="catch"),
     *                 @OA\Property(property="species", type="string", example="Щука"),
     *                 @OA\Property(property="weight", type="number", format="float", example=2.5),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             ))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 100);
        $includeCatches = $request->get('include_catches', false);

        $points = [];

        // Добавляем точки уловов с координатами
        if ($includeCatches) {
            $catches = \App\Models\CatchRecord::whereNotNull('lat')
                ->whereNotNull('lng')
                ->where('privacy', 'all')
                ->limit($limit)
                ->get();

            foreach ($catches as $catch) {
                $points[] = [
                    'id' => $catch->id,
                    'user' => [
                        'id' => $catch->user_id,
                        'name' => 'Пользователь',
                        'username' => '',
                        'photo_url' => '',
                    ],
                    'lat' => (float) $catch->lat,
                    'lng' => (float) $catch->lng,
                    'title' => $catch->species ? "Улов: {$catch->species}" : 'Улов',
                    'description' => $catch->notes ?? '',
                    'cover_url' => $catch->photo_url ?? '/uploads/default-catch.jpg',
                    'privacy' => $catch->privacy,
                    'media_count' => 1,
                    'created_at' => $catch->created_at,
                    'type' => 'catch',
                    'species' => $catch->species,
                    'weight' => $catch->weight,
                    'length' => $catch->length,
                    'caught_at' => $catch->caught_at,
                ];
            }
        }

        // Добавляем точки рыбалки
        $fishingPoints = DB::table('fishing_points')
            ->where('is_public', 1)
            ->where('status', 'active')
            ->limit($limit)
            ->get();

        foreach ($fishingPoints as $point) {
            $points[] = [
                'id' => 'point_' . $point->id,
                'user' => [
                    'id' => $point->user_id,
                    'name' => 'Пользователь',
                    'username' => '',
                    'photo_url' => '',
                ],
                'lat' => (float) $point->lat,
                'lng' => (float) $point->lng,
                'title' => $point->title,
                'description' => $point->description,
                'cover_url' => '/uploads/default-point.jpg',
                'privacy' => 'all',
                'media_count' => 0,
                'created_at' => $point->created_at,
                'type' => 'point',
                'category' => $point->category,
            ];
        }

        return response()->json(array_slice($points, 0, $limit));
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

