<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class UserManagementController extends Controller
{
    /**
     * @OA\Get(
     *     path="/admin/users",
     *     tags={"Admin - Users"},
     *     summary="Получить список пользователей",
     *     description="Возвращает список всех пользователей с пагинацией и фильтрами для админ-панели",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         @OA\Schema(type="integer", example=1, minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество пользователей на странице",
     *         @OA\Schema(type="integer", example=20, minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Поиск по имени или email",
     *         @OA\Schema(type="string", example="Иван")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Фильтр по статусу",
     *         @OA\Schema(type="string", enum={"active", "blocked"}, example="active")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Сортировка",
     *         @OA\Schema(type="string", enum={"created_at", "name", "email"}, example="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Порядок сортировки",
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список пользователей",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                 @OA\Property(property="username", type="string", example="ivan_ivanov"),
     *                 @OA\Property(property="email", type="string", example="ivan@example.com"),
     *                 @OA\Property(property="is_blocked", type="boolean", example=false),
     *                 @OA\Property(property="catches_count", type="integer", example=25),
     *                 @OA\Property(property="followers_count", type="integer", example=150),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="last_login_at", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="last_page", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Недостаточно прав доступа"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Filters
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'blocked') {
                $query->blocked();
            }
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->with(['blockedBy:id,name,username'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get user details.
     */
    /**
     * @OA\Get(
     *     path="/admin/users/{user}",
     *     tags={"Admin - Users"},
     *     summary="Получить детали пользователя",
     *     description="Возвращает подробную информацию о пользователе для админ-панели",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID пользователя",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Детали пользователя",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                 @OA\Property(property="username", type="string", example="ivan_ivanov"),
     *                 @OA\Property(property="email", type="string", example="ivan@example.com"),
     *                 @OA\Property(property="is_blocked", type="boolean", example=false),
     *                 @OA\Property(property="blocked_at", type="string", format="date-time"),
     *                 @OA\Property(property="blocked_by", type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Админ Админов"),
     *                     @OA\Property(property="username", type="string", example="admin")
     *                 ),
     *                 @OA\Property(property="catches_count", type="integer", example=25),
     *                 @OA\Property(property="followers_count", type="integer", example=150),
     *                 @OA\Property(property="points_count", type="integer", example=5),
     *                 @OA\Property(property="reports_count", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="last_login_at", type="string", format="date-time"),
     *                 @OA\Property(property="recent_catches", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="fish_type", type="string", example="Щука"),
     *                     @OA\Property(property="weight", type="number", format="float", example=2.5),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )),
     *                 @OA\Property(property="recent_points", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Волга, г. Тверь"),
     *                     @OA\Property(property="lat", type="number", format="float", example=56.8586),
     *                     @OA\Property(property="lng", type="number", format="float", example=35.9117),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )),
     *                 @OA\Property(property="recent_reports", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="type", type="string", example="spam"),
     *                     @OA\Property(property="reason", type="string", example="Неуместный контент"),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Недостаточно прав доступа"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Пользователь не найден"
     *     )
     * )
     */
    public function show(User $user): JsonResponse
    {
        $user->load([
            'blockedBy:id,name,username',
            'catchRecords' => function ($query) {
                $query->latest()->limit(10);
            },
            'points' => function ($query) {
                $query->latest()->limit(10);
            },
            'reports' => function ($query) {
                $query->latest()->limit(5);
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * @OA\Post(
     *     path="/admin/users/{user}/toggle-block",
     *     tags={"Admin - Users"},
     *     summary="Заблокировать/разблокировать пользователя",
     *     description="Блокирует или разблокирует пользователя с указанием причины",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID пользователя",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"is_blocked"},
     *             @OA\Property(property="is_blocked", type="boolean", example=true, description="Заблокировать пользователя"),
     *             @OA\Property(property="block_reason", type="string", example="Нарушение правил сообщества", maxLength=500, description="Причина блокировки (обязательно при блокировке)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Статус пользователя изменен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User blocked successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                 @OA\Property(property="is_blocked", type="boolean", example=true),
     *                 @OA\Property(property="block_reason", type="string", example="Нарушение правил сообщества"),
     *                 @OA\Property(property="blocked_at", type="string", format="date-time"),
     *                 @OA\Property(property="blocked_by", type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Админ Админов")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Недостаточно прав доступа"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Пользователь не найден"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function toggleBlock(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'block_reason' => 'required_if:is_blocked,true|string|max:500',
        ]);

        $admin = $request->user();

        if ($user->id === $admin->id) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя заблокировать самого себя'
            ], 400);
        }

        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя заблокировать администратора'
            ], 400);
        }

        $user->update([
            'is_blocked' => !$user->is_blocked,
            'blocked_at' => !$user->is_blocked ? now() : null,
            'block_reason' => $request->block_reason,
            'blocked_by' => $admin->id,
        ]);

        $action = $user->is_blocked ? 'заблокирован' : 'разблокирован';

        return response()->json([
            'success' => true,
            'message' => "Пользователь {$action}",
            'data' => $user->fresh(['blockedBy'])
        ]);
    }

    /**
     * Update user information.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'sometimes|in:user,admin',
            'bio' => 'sometimes|string|max:1000',
            'location' => 'sometimes|string|max:255',
        ]);

        $user->update($request->only([
            'name', 'username', 'email', 'role', 'bio', 'location'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Информация о пользователе обновлена',
            'data' => $user
        ]);
    }

    /**
     * Delete user.
     */
    public function destroy(User $user): JsonResponse
    {
        $admin = request()->user();

        if ($user->id === $admin->id) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя удалить самого себя'
            ], 400);
        }

        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя удалить администратора'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Пользователь удален'
        ]);
    }
}
