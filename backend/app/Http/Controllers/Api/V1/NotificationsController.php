<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class NotificationsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/notifications",
     *     tags={"Notifications"},
     *     summary="Получить список уведомлений",
     *     description="Возвращает список уведомлений для авторизованного пользователя",
     *     security={{"jwt": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список уведомлений",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="type", type="string", example="like"),
     *                 @OA\Property(property="title", type="string", example="Новый лайк"),
     *                 @OA\Property(property="message", type="string", example="Пользователь поставил лайк вашему улову"),
     *                 @OA\Property(property="is_read", type="boolean", example=false),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="data", type="object",
     *                     @OA\Property(property="user_id", type="integer", example=2),
     *                     @OA\Property(property="catch_id", type="integer", example=5)
     *                 )
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Временно возвращаем пустой массив, так как таблица notifications может не существовать
        return response()->json([]);
    }

    /**
     * @OA\Post(
     *     path="/notifications/{id}/read",
     *     tags={"Notifications"},
     *     summary="Отметить уведомление как прочитанное",
     *     description="Отмечает уведомление как прочитанное",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID уведомления",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Уведомление отмечено как прочитанное",
     *         @OA\JsonContent(
     *             @OA\Property(property="ok", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Уведомление не найдено"
     *     )
     * )
     */
    public function read(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['ok' => true]);
    }
}

