<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CatchRecord;
use App\Models\Point;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class AdminController extends Controller
{
    /**
     * @OA\Get(
     *     path="/admin/dashboard",
     *     tags={"Admin"},
     *     summary="Получить статистику админ-панели",
     *     description="Возвращает общую статистику системы для администраторов",
     *     security={{"jwt": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Статистика админ-панели",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="users", type="object",
     *                     @OA\Property(property="total", type="integer", example=1250),
     *                     @OA\Property(property="active", type="integer", example=1200),
     *                     @OA\Property(property="blocked", type="integer", example=50),
     *                     @OA\Property(property="new_today", type="integer", example=15)
     *                 ),
     *                 @OA\Property(property="catches", type="object",
     *                     @OA\Property(property="total", type="integer", example=5670),
     *                     @OA\Property(property="active", type="integer", example=5600),
     *                     @OA\Property(property="blocked", type="integer", example=70),
     *                     @OA\Property(property="new_today", type="integer", example=45)
     *                 ),
     *                 @OA\Property(property="points", type="object",
     *                     @OA\Property(property="total", type="integer", example=890),
     *                     @OA\Property(property="active", type="integer", example=850),
     *                     @OA\Property(property="blocked", type="integer", example=40),
     *                     @OA\Property(property="new_today", type="integer", example=8)
     *                 ),
     *                 @OA\Property(property="reports", type="object",
     *                     @OA\Property(property="total", type="integer", example=120),
     *                     @OA\Property(property="pending", type="integer", example=25),
     *                     @OA\Property(property="resolved", type="integer", example=95),
     *                     @OA\Property(property="new_today", type="integer", example=3)
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
     *     )
     * )
     */
    public function dashboard(): JsonResponse
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'active' => User::active()->count(),
                'blocked' => User::blocked()->count(),
                'new_today' => User::whereDate('created_at', today())->count(),
            ],
            'catches' => [
                'total' => CatchRecord::count(),
                'active' => CatchRecord::active()->count(),
                'blocked' => CatchRecord::blocked()->count(),
                'new_today' => CatchRecord::whereDate('created_at', today())->count(),
            ],
            'points' => [
                'total' => Point::count(),
                'active' => Point::active()->count(),
                'blocked' => Point::blocked()->count(),
                'new_today' => Point::whereDate('created_at', today())->count(),
            ],
            'reports' => [
                'total' => Report::count(),
                'pending' => Report::pending()->count(),
                'resolved' => Report::resolved()->count(),
                'new_today' => Report::whereDate('created_at', today())->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * @OA\Get(
     *     path="/admin/activity",
     *     tags={"Admin"},
     *     summary="Получить последнюю активность",
     *     description="Возвращает последние действия пользователей, уловы и жалобы для админ-панели",
     *     security={{"jwt": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Последняя активность",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="recent_users", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                     @OA\Property(property="username", type="string", example="ivan_ivanov"),
     *                     @OA\Property(property="email", type="string", example="ivan@example.com"),
     *                     @OA\Property(property="is_blocked", type="boolean", example=false),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )),
     *                 @OA\Property(property="recent_catches", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="fish_type", type="string", example="Щука"),
     *                     @OA\Property(property="weight", type="number", format="float", example=2.5),
     *                     @OA\Property(property="is_blocked", type="boolean", example=false),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                         @OA\Property(property="username", type="string", example="ivan_ivanov")
     *                     ),
     *                     @OA\Property(property="point", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Волга, г. Тверь")
     *                     )
     *                 )),
     *                 @OA\Property(property="recent_reports", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="type", type="string", example="spam"),
     *                     @OA\Property(property="reason", type="string", example="Неуместный контент"),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="reporter", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Петр Петров"),
     *                         @OA\Property(property="username", type="string", example="petr_petrov")
     *                     )
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
     *     )
     * )
     */
    public function recentActivity(): JsonResponse
    {
        $recentUsers = User::latest()->limit(5)->get(['id', 'name', 'username', 'email', 'created_at', 'is_blocked']);
        $recentCatches = CatchRecord::with(['user:id,name,username', 'point:id,name'])
            ->latest()
            ->limit(5)
            ->get(['id', 'user_id', 'point_id', 'fish_type', 'weight', 'created_at', 'is_blocked']);
        $recentReports = Report::with(['reporter:id,name,username', 'reportable'])
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'recent_users' => $recentUsers,
                'recent_catches' => $recentCatches,
                'recent_reports' => $recentReports,
            ]
        ]);
    }
}
