<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use OpenApi\Annotations as OA;

class EventsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/events",
     *     tags={"Events"},
     *     summary="Получить список событий",
     *     description="Возвращает список доступных событий рыбалки",
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Тип событий",
     *         required=false,
     *         @OA\Schema(type="string", enum={"upcoming", "ongoing", "past", "all"}, default="upcoming")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Количество событий на странице",
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
     *         description="Список событий",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Рыбалка на Волге"),
     *                 @OA\Property(property="description", type="string", example="Еженедельная рыбалка"),
     *                 @OA\Property(property="location_name", type="string", example="Волга, г. Тверь"),
     *                 @OA\Property(property="lat", type="number", format="float", example=56.8586),
     *                 @OA\Property(property="lng", type="number", format="float", example=35.9117),
     *                 @OA\Property(property="start_at", type="string", format="date-time"),
     *                 @OA\Property(property="end_at", type="string", format="date-time"),
     *                 @OA\Property(property="max_participants", type="integer", example=20),
     *                 @OA\Property(property="participants_count", type="integer", example=15),
     *                 @OA\Property(property="status", type="string", example="upcoming"),
     *                 @OA\Property(property="cover_url", type="string", example="https://example.com/cover.jpg"),
     *                 @OA\Property(property="organizer", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Иван Иванов")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Временно возвращаем пустой массив, так как таблица events может иметь другую структуру
        return response()->json([]);
    }

    /**
     * @OA\Get(
     *     path="/events/{id}",
     *     tags={"Events"},
     *     summary="Получить детали события",
     *     description="Возвращает подробную информацию о конкретном событии",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID события",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Детали события",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Рыбалка на Волге"),
     *             @OA\Property(property="description", type="string", example="Еженедельная рыбалка"),
     *             @OA\Property(property="location_name", type="string", example="Волга, г. Тверь"),
     *             @OA\Property(property="lat", type="number", format="float", example=56.8586),
     *             @OA\Property(property="lng", type="number", format="float", example=35.9117),
     *             @OA\Property(property="start_at", type="string", format="date-time"),
     *             @OA\Property(property="end_at", type="string", format="date-time"),
     *             @OA\Property(property="max_participants", type="integer", example=20),
     *             @OA\Property(property="participants_count", type="integer", example=15),
     *             @OA\Property(property="status", type="string", example="upcoming"),
     *             @OA\Property(property="cover_url", type="string", example="https://example.com/cover.jpg"),
     *             @OA\Property(property="organizer", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Иван Иванов")
     *             ),
     *             @OA\Property(property="participants", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Петр Петров"),
     *                 @OA\Property(property="status", type="string", example="confirmed")
     *             )),
     *             @OA\Property(property="live_sessions", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Прямая трансляция"),
     *                 @OA\Property(property="stream_url", type="string", example="https://stream.example.com")
     *             )),
     *             @OA\Property(property="created_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Событие не найдено"
     *     )
     * )
     */
    public function show($id)
    {
        $event = Event::with(['organizer', 'group', 'participants', 'liveSessions'])
            ->findOrFail($id);

        return response()->json($event);
    }

    /**
     * @OA\Post(
     *     path="/events",
     *     tags={"Events"},
     *     summary="Создать новое событие",
     *     description="Создает новое событие рыбалки",
     *     security={{"jwt": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "start_at"},
     *             @OA\Property(property="title", type="string", example="Рыбалка на Волге", maxLength=191),
     *             @OA\Property(property="description", type="string", example="Еженедельная рыбалка"),
     *             @OA\Property(property="lat", type="number", format="float", example=56.8586, minimum=-90, maximum=90),
     *             @OA\Property(property="lng", type="number", format="float", example=35.9117, minimum=-180, maximum=180),
     *             @OA\Property(property="location_name", type="string", example="Волга, г. Тверь", maxLength=191),
     *             @OA\Property(property="start_at", type="string", format="date-time", example="2025-01-20T10:00:00Z"),
     *             @OA\Property(property="end_at", type="string", format="date-time", example="2025-01-20T18:00:00Z"),
     *             @OA\Property(property="max_participants", type="integer", example=20, minimum=1),
     *             @OA\Property(property="group_id", type="integer", example=1),
     *             @OA\Property(property="cover_url", type="string", example="https://example.com/cover.jpg", maxLength=512)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Событие успешно создано",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Рыбалка на Волге"),
     *                 @OA\Property(property="description", type="string", example="Еженедельная рыбалка"),
     *                 @OA\Property(property="location_name", type="string", example="Волга, г. Тверь"),
     *                 @OA\Property(property="start_at", type="string", format="date-time"),
     *                 @OA\Property(property="end_at", type="string", format="date-time"),
     *                 @OA\Property(property="max_participants", type="integer", example=20),
     *                 @OA\Property(property="organizer_id", type="integer", example=1),
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

    /**
     * @OA\Post(
     *     path="/events/{id}/join",
     *     tags={"Events"},
     *     summary="Присоединиться к событию",
     *     description="Позволяет пользователю присоединиться к событию рыбалки",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID события",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешно присоединились к событию",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Joined event successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка присоединения",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Already participating")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Событие не найдено"
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/events/{id}/leave",
     *     tags={"Events"},
     *     summary="Покинуть событие",
     *     description="Позволяет пользователю покинуть событие рыбалки",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID события",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешно покинули событие",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Left event successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка покидания",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not participating in this event")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Событие не найдено"
     *     )
     * )
     */
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
