<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class GroupsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/groups",
     *     tags={"Groups"},
     *     summary="Получить список групп",
     *     description="Возвращает список доступных групп",
     *     @OA\Response(
     *         response=200,
     *         description="Список групп",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Рыболовы Москвы"),
     *                 @OA\Property(property="description", type="string", example="Группа для московских рыболовов"),
     *                 @OA\Property(property="cover_url", type="string", example="https://example.com/cover.jpg"),
     *                 @OA\Property(property="privacy", type="string", example="public"),
     *                 @OA\Property(property="members_count", type="integer", example=150),
     *                 @OA\Property(property="owner", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Иван Иванов")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             ))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Временно возвращаем пустой массив, так как таблица groups не существует
        // TODO: Создать таблицу groups или использовать clubs
        return response()->json([]);
    }

    /**
     * @OA\Get(
     *     path="/groups/{id}",
     *     tags={"Groups"},
     *     summary="Получить детали группы",
     *     description="Возвращает подробную информацию о конкретной группе",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID группы",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Детали группы",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Рыболовы Москвы"),
     *                 @OA\Property(property="description", type="string", example="Группа для московских рыболовов"),
     *                 @OA\Property(property="cover_url", type="string", example="https://example.com/cover.jpg"),
     *                 @OA\Property(property="privacy", type="string", example="public"),
     *                 @OA\Property(property="members_count", type="integer", example=150),
     *                 @OA\Property(property="posts_count", type="integer", example=25),
     *                 @OA\Property(property="owner", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                     @OA\Property(property="username", type="string", example="ivan_ivanov")
     *                 ),
     *                 @OA\Property(property="members", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Петр Петров"),
     *                     @OA\Property(property="role", type="string", example="member"),
     *                     @OA\Property(property="joined_at", type="string", format="date-time")
     *                 )),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Группа не найдена"
     *     )
     * )
     */
    public function show($id)
    {
        // Временно возвращаем 404, так как таблица groups не существует
        // TODO: Создать таблицу groups или использовать clubs
        return response()->json(['error' => 'Groups not implemented yet'], 404);
    }

    /**
     * @OA\Post(
     *     path="/groups",
     *     tags={"Groups"},
     *     summary="Создать новую группу",
     *     description="Создает новую группу рыболовов",
     *     security={{"jwt": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Рыболовы Москвы", maxLength=191),
     *             @OA\Property(property="description", type="string", example="Группа для московских рыболовов"),
     *             @OA\Property(property="cover_url", type="string", example="https://example.com/cover.jpg", maxLength=512),
     *             @OA\Property(property="privacy", type="string", enum={"public", "private", "closed"}, example="public")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Группа успешно создана",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Group created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Рыболовы Москвы"),
     *                 @OA\Property(property="description", type="string", example="Группа для московских рыболовов"),
     *                 @OA\Property(property="privacy", type="string", example="public"),
     *                 @OA\Property(property="owner_id", type="integer", example=1),
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
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'cover_url' => 'nullable|string|max:512',
            'privacy' => 'in:public,private,closed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'fields' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['owner_id'] = $request->user()->id;

        $group = Group::create($data);

        // Add owner as admin member
        $group->members()->attach($request->user()->id, [
            'role' => 'admin',
            'is_active' => true
        ]);

        return response()->json($group, 201);
    }

    /**
     * @OA\Post(
     *     path="/groups/{id}/join",
     *     tags={"Groups"},
     *     summary="Присоединиться к группе",
     *     description="Позволяет пользователю присоединиться к группе рыболовов",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID группы",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешно присоединились к группе",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Joined group successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка присоединения",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Already a member")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Группа не найдена"
     *     )
     * )
     */
    public function join(Request $request, $id)
    {
        $group = Group::findOrFail($id);
        $user = $request->user();

        if ($group->isMember($user->id)) {
            return response()->json(['message' => 'Already a member'], 400);
        }

        if ($group->privacy === 'closed') {
            return response()->json(['message' => 'Group is closed'], 403);
        }

        $group->members()->attach($user->id, [
            'role' => 'member',
            'is_active' => true
        ]);

        $group->increment('members_count');

        return response()->json(['message' => 'Joined group successfully']);
    }

    /**
     * @OA\Post(
     *     path="/groups/{id}/leave",
     *     tags={"Groups"},
     *     summary="Покинуть группу",
     *     description="Позволяет пользователю покинуть группу рыболовов",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID группы",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешно покинули группу",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Left group successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка покидания",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not a member of this group")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Группа не найдена"
     *     )
     * )
     */
    public function leave(Request $request, $id)
    {
        $group = Group::findOrFail($id);
        $user = $request->user();

        if (!$group->isMember($user->id)) {
            return response()->json(['message' => 'Not a member'], 400);
        }

        if ($group->owner_id === $user->id) {
            return response()->json(['message' => 'Owner cannot leave group'], 403);
        }

        $group->members()->detach($user->id);
        $group->decrement('members_count');

        return response()->json(['message' => 'Left group successfully']);
    }
}

