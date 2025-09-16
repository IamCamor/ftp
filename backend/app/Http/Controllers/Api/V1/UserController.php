<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/users",
     *     tags={"Users"},
     *     summary="Получить список пользователей",
     *     description="Возвращает список пользователей с пагинацией",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список пользователей",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                     @OA\Property(property="username", type="string", example="ivan_ivanov"),
     *                     @OA\Property(property="email", type="string", example="user@example.com"),
     *                     @OA\Property(property="photo_url", type="string", example="https://example.com/photo.jpg"),
     *                     @OA\Property(property="role", type="string", example="user"),
     *                     @OA\Property(property="is_premium", type="boolean", example=false),
     *                     @OA\Property(property="is_online", type="boolean", example=true),
     *                     @OA\Property(property="catch_records_count", type="integer", example=25),
     *                     @OA\Property(property="followers_count", type="integer", example=150),
     *                     @OA\Property(property="following_count", type="integer", example=75)
     *                 )),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="total", type="integer", example=100)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::select(['id', 'name', 'username', 'email', 'photo_url', 'role', 'is_premium', 'crown_icon_url', 'is_online', 'last_seen_at', 'created_at'])
            ->withCount(['catchRecords', 'followers', 'following'])
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * @OA\Get(
     *     path="/users/{user}",
     *     tags={"Users"},
     *     summary="Получить информацию о пользователе",
     *     description="Возвращает подробную информацию о конкретном пользователе",
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID пользователя",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация о пользователе",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                 @OA\Property(property="username", type="string", example="ivan_ivanov"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="photo_url", type="string", example="https://example.com/photo.jpg"),
     *                 @OA\Property(property="role", type="string", example="user"),
     *                 @OA\Property(property="is_premium", type="boolean", example=false),
     *                 @OA\Property(property="followers_count", type="integer", example=150),
     *                 @OA\Property(property="following_count", type="integer", example=75),
     *                 @OA\Property(property="is_online", type="boolean", example=true),
     *                 @OA\Property(property="last_seen_at", type="string", format="date-time"),
     *                 @OA\Property(property="bio", type="string", example="Люблю рыбалку"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Пользователь не найден"
     *     )
     * )
     */
    public function show(User $user): JsonResponse
    {
        // Упрощаем ответ, убираем несуществующие связи
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->handle ?? '',
                'email' => $user->email,
                'photo_url' => $user->avatar_path ?? '',
                'role' => 'user',
                'is_premium' => false,
                'crown_icon_url' => null,
                'followers_count' => 0,
                'following_count' => 0,
                'is_online' => false,
                'last_seen_at' => null,
                'bio' => $user->bio ?? '',
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Get current user profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadCount(['catchRecords', 'followers', 'following']);
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Get user's following list
     */
    public function following($userId)
    {
        // For now, return empty array since following system is not fully implemented
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    /**
     * Get user's followers list
     */
    public function followers($userId)
    {
        // For now, return empty array since following system is not fully implemented
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    /**
     * Get user statistics
     */
    public function stats($userId, Request $request)
    {
        $period = $request->get('period', 'month');
        
        // For now, return mock stats since the system is not fully implemented
        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'total_catches' => 0,
                'total_weight' => 0,
                'average_weight' => 0,
                'biggest_catch' => null,
                'favorite_species' => null,
                'fishing_days' => 0,
                'total_distance' => 0,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ]
        ]);
    }
}
