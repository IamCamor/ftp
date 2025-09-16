<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\CatchRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class TrackController extends Controller
{
    /**
     * @OA\Get(
     *     path="/tracks",
     *     tags={"Tracks"},
     *     summary="Получить список треков пользователя",
     *     description="Возвращает список треков рыбалки авторизованного пользователя",
     *     security={{"jwt": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список треков",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="type", type="string", example="track"),
     *                 @OA\Property(property="title", type="string", example="Рыбалка на Волге"),
     *                 @OA\Property(property="description", type="string", example="Утренняя рыбалка"),
     *                 @OA\Property(property="status", type="string", example="completed"),
     *                 @OA\Property(property="started_at", type="string", format="date-time"),
     *                 @OA\Property(property="ended_at", type="string", format="date-time"),
     *                 @OA\Property(property="duration_minutes", type="integer", example=120),
     *                 @OA\Property(property="total_distance", type="string", example="2.5"),
     *                 @OA\Property(property="total_catches", type="integer", example=5),
     *                 @OA\Property(property="total_weight", type="number", format="float", example=12.5),
     *                 @OA\Property(property="average_weight", type="number", format="float", example=2.5),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Иван Иванов")
     *                 ),
     *                 @OA\Property(property="catches", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="species", type="string", example="Щука"),
     *                     @OA\Property(property="weight", type="number", format="float", example=2.5),
     *                     @OA\Property(property="caught_at", type="string", format="date-time")
     *                 )),
     *                 @OA\Property(property="track_points", type="array", @OA\Items(
     *                     @OA\Property(property="lat", type="number", format="float", example=55.7558),
     *                     @OA\Property(property="lng", type="number", format="float", example=37.6176)
     *                 )),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Извлекаем user_id из токена
            $authHeader = $request->header('Authorization');
            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                return response()->json(['error' => 'Token required'], 401);
            }

            $token = substr($authHeader, 7);
            if (!preg_match('/^simple_token_(\d+)_\d+$/', $token, $matches)) {
                return response()->json(['error' => 'Invalid token'], 401);
            }

            $userId = (int) $matches[1];

            $tracks = Track::where('user_id', $userId)
                ->with(['catches' => function($query) {
                    $query->orderBy('caught_at', 'desc');
                }])
                ->orderBy('started_at', 'desc')
                ->get();

            $transformedTracks = $tracks->map(function ($track) {
                return $this->transformTrack($track);
            });

            return response()->json([
                'success' => true,
                'data' => $transformedTracks
            ]);

        } catch (\Exception $e) {
            Log::error('Track index error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch tracks'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/tracks",
     *     tags={"Tracks"},
     *     summary="Создать новый трек",
     *     description="Создает новый трек рыбалки для авторизованного пользователя",
     *     security={{"jwt": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Рыбалка на Волге", maxLength=191),
     *             @OA\Property(property="description", type="string", example="Утренняя рыбалка"),
     *             @OA\Property(property="start_lat", type="number", format="float", example=56.8586),
     *             @OA\Property(property="start_lng", type="number", format="float", example=35.9117),
     *             @OA\Property(property="started_at", type="string", format="date-time", example="2025-01-20T06:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Трек успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Track created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Рыбалка на Волге"),
     *                 @OA\Property(property="description", type="string", example="Утренняя рыбалка"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="started_at", type="string", format="date-time"),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            Log::info('Track store method called', ['request_data' => $request->all()]);
            
            // Извлекаем user_id из токена
            $authHeader = $request->header('Authorization');
            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                Log::error('No valid Authorization header');
                return response()->json(['error' => 'Token required'], 401);
            }

            $token = substr($authHeader, 7);
            if (!preg_match('/^simple_token_(\d+)_\d+$/', $token, $matches)) {
                Log::error('Invalid token format', ['token' => $token]);
                return response()->json(['error' => 'Invalid token'], 401);
            }

            $userId = (int) $matches[1];
            Log::info('User ID extracted', ['user_id' => $userId]);

            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
                'smartwatch_data' => 'nullable|array'
            ]);

            $track = Track::create([
                'user_id' => $userId,
                'title' => $validated['title'] ?? 'Трек ' . now()->format('d.m.Y H:i'),
                'description' => $validated['description'] ?? null,
                'started_at' => now(),
                'status' => 'active',
                'smartwatch_data' => $validated['smartwatch_data'] ?? null,
                'track_points' => [[
                    'lat' => $validated['lat'],
                    'lng' => $validated['lng'],
                    'timestamp' => now()->toISOString(),
                    'smartwatch_data' => $validated['smartwatch_data'] ?? null
                ]]
            ]);

            return response()->json([
                'success' => true,
                'data' => $this->transformTrack($track)
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Track store error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create track'], 500);
        }
    }

    /**
     * Получить детали трека
     */
    /**
     * @OA\Get(
     *     path="/tracks/{id}",
     *     tags={"Tracks"},
     *     summary="Получить детали трека",
     *     description="Возвращает подробную информацию о конкретном треке рыбалки",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID трека",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Детали трека",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Рыбалка на Волге"),
     *                 @OA\Property(property="description", type="string", example="Утренняя рыбалка"),
     *                 @OA\Property(property="status", type="string", example="completed"),
     *                 @OA\Property(property="started_at", type="string", format="date-time"),
     *                 @OA\Property(property="ended_at", type="string", format="date-time"),
     *                 @OA\Property(property="duration_minutes", type="integer", example=120),
     *                 @OA\Property(property="total_distance", type="string", example="2.5"),
     *                 @OA\Property(property="total_catches", type="integer", example=5),
     *                 @OA\Property(property="total_weight", type="number", format="float", example=12.5),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Иван Иванов")
     *                 ),
     *                 @OA\Property(property="catches", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="species", type="string", example="Щука"),
     *                     @OA\Property(property="weight", type="number", format="float", example=2.5),
     *                     @OA\Property(property="caught_at", type="string", format="date-time")
     *                 )),
     *                 @OA\Property(property="track_points", type="array", @OA\Items(
     *                     @OA\Property(property="lat", type="number", format="float", example=55.7558),
     *                     @OA\Property(property="lng", type="number", format="float", example=37.6176)
     *                 )),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Трек не найден"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $track = Track::with(['catches' => function($query) {
                $query->orderBy('caught_at', 'desc');
            }])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $this->transformTrack($track)
            ]);

        } catch (\Exception $e) {
            Log::error('Track show error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Track not found'], 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/tracks/{id}",
     *     tags={"Tracks"},
     *     summary="Обновить трек",
     *     description="Обновляет трек рыбалки: добавляет точки, завершает, приостанавливает или возобновляет",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID трека",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"action"},
     *             @OA\Property(property="action", type="string", enum={"add_point", "complete", "pause", "resume"}, example="add_point"),
     *             @OA\Property(property="lat", type="number", format="float", example=56.8586, description="Широта (обязательно для add_point)"),
     *             @OA\Property(property="lng", type="number", format="float", example=35.9117, description="Долгота (обязательно для add_point)"),
     *             @OA\Property(property="smartwatch_data", type="object", description="Данные с умных часов", example={"heart_rate": 75, "steps": 1500})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Трек успешно обновлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Track updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Нет прав на изменение трека"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Трек не найден"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $track = Track::findOrFail($id);

            $validated = $request->validate([
                'action' => 'required|in:add_point,complete,pause,resume',
                'lat' => 'required_if:action,add_point|numeric|between:-90,90',
                'lng' => 'required_if:action,add_point|numeric|between:-180,180',
                'smartwatch_data' => 'nullable|array'
            ]);

            switch ($validated['action']) {
                case 'add_point':
                    $track->addTrackPoint(
                        $validated['lat'],
                        $validated['lng'],
                        $validated['smartwatch_data'] ?? null
                    );
                    break;

                case 'complete':
                    $track->update([
                        'status' => 'completed',
                        'ended_at' => now()
                    ]);
                    break;

                case 'pause':
                    $track->update(['status' => 'paused']);
                    break;

                case 'resume':
                    $track->update(['status' => 'active']);
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $this->transformTrack($track->fresh())
            ]);

        } catch (\Exception $e) {
            Log::error('Track update error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to update track'], 500);
        }
    }

    /**
     * Быстрое добавление улова в трек
     */
    /**
     * @OA\Post(
     *     path="/tracks/{id}/catches",
     *     tags={"Tracks"},
     *     summary="Добавить улов к треку",
     *     description="Добавляет новый улов к активному треку рыбалки",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID трека",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"fish_type", "weight"},
     *             @OA\Property(property="fish_type", type="string", example="Щука", maxLength=100),
     *             @OA\Property(property="weight", type="number", format="float", example=2.5, minimum=0.1),
     *             @OA\Property(property="length", type="number", format="float", example=45.0, minimum=1),
     *             @OA\Property(property="bait", type="string", example="Воблер", maxLength=100),
     *             @OA\Property(property="weather_conditions", type="string", example="Ясно, безветренно"),
     *             @OA\Property(property="photo_url", type="string", example="https://example.com/catch.jpg"),
     *             @OA\Property(property="notes", type="string", example="Отличный улов!", maxLength=500),
     *             @OA\Property(property="caught_at", type="string", format="date-time", example="2025-01-20T10:30:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Улов успешно добавлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Catch added successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="fish_type", type="string", example="Щука"),
     *                 @OA\Property(property="weight", type="number", format="float", example=2.5),
     *                 @OA\Property(property="length", type="number", format="float", example=45.0),
     *                 @OA\Property(property="bait", type="string", example="Воблер"),
     *                 @OA\Property(property="weather_conditions", type="string", example="Ясно, безветренно"),
     *                 @OA\Property(property="photo_url", type="string", example="https://example.com/catch.jpg"),
     *                 @OA\Property(property="notes", type="string", example="Отличный улов!"),
     *                 @OA\Property(property="caught_at", type="string", format="date-time"),
     *                 @OA\Property(property="track_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Нет прав на добавление улова к треку"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Трек не найден"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function addCatch(Request $request, $id): JsonResponse
    {
        try {
            $track = Track::findOrFail($id);

            // Извлекаем user_id из токена
            $authHeader = $request->header('Authorization');
            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                return response()->json(['error' => 'Token required'], 401);
            }

            $token = substr($authHeader, 7);
            if (!preg_match('/^simple_token_(\d+)_\d+$/', $token, $matches)) {
                return response()->json(['error' => 'Invalid token'], 401);
            }

            $userId = (int) $matches[1];

            $validated = $request->validate([
                'species' => 'required|string|max:120',
                'weight' => 'required|numeric|min:0',
                'length' => 'required|numeric|min:0',
                'style' => 'nullable|string|max:120',
                'lure' => 'nullable|string|max:120',
                'tackle' => 'nullable|string|max:120',
                'notes' => 'nullable|string|max:1000',
                'lat' => 'nullable|numeric|between:-90,90',
                'lng' => 'nullable|numeric|between:-180,180'
            ]);

            // Получаем последний улов для подстановки данных
            $lastCatch = $track->lastCatch();

            $catch = CatchRecord::create([
                'user_id' => $userId,
                'track_id' => $track->id,
                'lat' => $validated['lat'] ?? $track->track_points[0]['lat'] ?? 0,
                'lng' => $validated['lng'] ?? $track->track_points[0]['lng'] ?? 0,
                'species' => $validated['species'],
                'weight' => $validated['weight'],
                'length' => $validated['length'],
                'style' => $validated['style'] ?? $lastCatch?->style,
                'lure' => $validated['lure'] ?? $lastCatch?->lure,
                'tackle' => $validated['tackle'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'caught_at' => now(),
                'privacy' => 'all',
                // Копируем погодные данные из последнего улова
                'temperature' => $lastCatch?->temperature,
                'pressure' => $lastCatch?->pressure,
                'wind_speed' => $lastCatch?->wind_speed,
                'cloudiness' => $lastCatch?->cloudiness,
                'precipitation' => $lastCatch?->precipitation,
                'wind_direction' => $lastCatch?->wind_direction,
            ]);

            // Обновляем статистику трека
            $track->updateStats();

            return response()->json([
                'success' => true,
                'data' => $this->transformCatch($catch)
            ], 201);

        } catch (\Exception $e) {
            Log::error('Track addCatch error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to add catch to track'], 500);
        }
    }

    /**
     * Трансформировать трек для API
     */
    private function transformTrack(Track $track): array
    {
        return [
            'id' => $track->id,
            'title' => $track->title,
            'description' => $track->description,
            'status' => $track->status,
            'started_at' => $track->started_at->toISOString(),
            'ended_at' => $track->ended_at?->toISOString(),
            'duration_minutes' => $track->getDurationInMinutes(),
            'total_distance' => $track->total_distance,
            'total_catches' => $track->total_catches,
            'total_weight' => $track->total_weight,
            'average_weight' => $track->getAverageWeight(),
            'track_points' => $track->track_points,
            'smartwatch_data' => $track->smartwatch_data,
            'catches' => $track->catches->map(function ($catch) {
                return $this->transformCatch($catch);
            }),
            'created_at' => $track->created_at->toISOString(),
            'updated_at' => $track->updated_at->toISOString()
        ];
    }

    /**
     * Трансформировать улов для API
     */
    private function transformCatch(CatchRecord $catch): array
    {
        return [
            'id' => $catch->id,
            'species' => $catch->species,
            'weight' => $catch->weight,
            'length' => $catch->length,
            'style' => $catch->style,
            'lure' => $catch->lure,
            'tackle' => $catch->getAttributes()['tackle'] ?? null,
            'notes' => $catch->notes,
            'lat' => $catch->lat,
            'lng' => $catch->lng,
            'caught_at' => $catch->caught_at?->toISOString(),
            'image_url' => $catch->image_url,
            'weather' => [
                'temperature' => $catch->temperature,
                'pressure' => $catch->pressure,
                'wind_speed' => $catch->wind_speed,
                'cloudiness' => $catch->cloudiness,
                'precipitation' => $catch->precipitation,
                'wind_direction' => $catch->wind_direction,
            ],
            'created_at' => $catch->created_at->toISOString()
        ];
    }
}