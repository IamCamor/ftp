<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatchRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class CatchManagementController extends Controller
{
    /**
     * @OA\Get(
     *     path="/admin/catches",
     *     tags={"Admin - Catches"},
     *     summary="Получить список уловов",
     *     description="Возвращает список всех уловов с пагинацией и фильтрами для админ-панели",
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
     *         description="Количество уловов на странице",
     *         @OA\Schema(type="integer", example=20, minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Поиск по виду рыбы или пользователю",
     *         @OA\Schema(type="string", example="Щука")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Фильтр по статусу",
     *         @OA\Schema(type="string", enum={"active", "blocked"}, example="active")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Фильтр по пользователю",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Сортировка",
     *         @OA\Schema(type="string", enum={"created_at", "weight", "fish_type"}, example="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Порядок сортировки",
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список уловов",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="fish_type", type="string", example="Щука"),
     *                 @OA\Property(property="weight", type="number", format="float", example=2.5),
     *                 @OA\Property(property="length", type="number", format="float", example=45.0),
     *                 @OA\Property(property="bait", type="string", example="Воблер"),
     *                 @OA\Property(property="is_blocked", type="boolean", example=false),
     *                 @OA\Property(property="block_reason", type="string", example="Нарушение правил"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                     @OA\Property(property="username", type="string", example="ivan_ivanov")
     *                 ),
     *                 @OA\Property(property="point", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Волга, г. Тверь")
     *                 ),
     *                 @OA\Property(property="blocked_by", type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Админ Админов")
     *                 )
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
        $query = CatchRecord::with(['user:id,name,username', 'point:id,name', 'blockedBy:id,name,username', 'editedByAdmin:id,name,username']);

        // Filters
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'blocked') {
                $query->blocked();
            } elseif ($request->status === 'edited_by_admin') {
                $query->editedByAdmin();
            }
        }

        if ($request->has('fish_type')) {
            $query->where('fish_type', 'like', "%{$request->fish_type}%");
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fish_type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        $catches = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $catches
        ]);
    }

    /**
     * Get catch details.
     */
    public function show(CatchRecord $catch): JsonResponse
    {
        $catch->load([
            'user:id,name,username,email',
            'point:id,name,latitude,longitude',
            'blockedBy:id,name,username',
            'editedByAdmin:id,name,username',
            'likes.user:id,name,username',
            'comments.user:id,name,username',
            'reports.reporter:id,name,username'
        ]);

        return response()->json([
            'success' => true,
            'data' => $catch
        ]);
    }

    /**
     * Block/unblock catch.
     */
    public function toggleBlock(Request $request, CatchRecord $catch): JsonResponse
    {
        $request->validate([
            'block_reason' => 'required_if:is_blocked,true|string|max:500',
        ]);

        $admin = $request->user();

        $catch->update([
            'is_blocked' => !$catch->is_blocked,
            'blocked_at' => !$catch->is_blocked ? now() : null,
            'block_reason' => $request->block_reason,
            'blocked_by' => $admin->id,
        ]);

        $action = $catch->is_blocked ? 'заблокирован' : 'разблокирован';

        return response()->json([
            'success' => true,
            'message' => "Улов {$action}",
            'data' => $catch->fresh(['blockedBy'])
        ]);
    }

    /**
     * Update catch information.
     */
    public function update(Request $request, CatchRecord $catch): JsonResponse
    {
        $request->validate([
            'fish_type' => 'sometimes|string|max:255',
            'weight' => 'sometimes|numeric|min:0|max:1000',
            'length' => 'sometimes|numeric|min:0|max:500',
            'bait' => 'sometimes|string|max:255',
            'weather' => 'sometimes|string|max:50',
            'temperature' => 'sometimes|integer|min:-50|max:50',
            'description' => 'sometimes|string|max:2000',
            'is_public' => 'sometimes|boolean',
        ]);

        $admin = $request->user();

        $catch->update(array_merge(
            $request->only([
                'fish_type', 'weight', 'length', 'bait', 'weather', 
                'temperature', 'description', 'is_public'
            ]),
            [
                'is_edited_by_admin' => true,
                'edited_by_admin_at' => now(),
                'edited_by_admin_id' => $admin->id,
            ]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Информация об улове обновлена',
            'data' => $catch->fresh(['editedByAdmin'])
        ]);
    }

    /**
     * Delete catch.
     */
    public function destroy(CatchRecord $catch): JsonResponse
    {
        $catch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Улов удален'
        ]);
    }
}
