<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CatchComment;
use App\Models\CatchRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class CatchCommentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/catches/{catchId}/comments",
     *     tags={"Catch Comments"},
     *     summary="Получить комментарии к улову",
     *     description="Возвращает список комментариев к конкретному улову",
     *     @OA\Parameter(
     *         name="catchId",
     *         in="path",
     *         description="ID улова",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Количество комментариев на странице",
     *         @OA\Schema(type="integer", example=20, minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         description="Смещение для пагинации",
     *         @OA\Schema(type="integer", example=0, minimum=0)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список комментариев",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="content", type="string", example="Отличный улов!"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                     @OA\Property(property="username", type="string", example="ivan_ivanov"),
     *                     @OA\Property(property="avatar_url", type="string", example="https://example.com/avatar.jpg")
     *                 )
     *             )),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="limit", type="integer", example=20),
     *                 @OA\Property(property="offset", type="integer", example=0)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Улов не найден"
     *     )
     * )
     */
    public function index(Request $request, int $catchId): JsonResponse
    {
        $request->validate([
            'limit' => 'integer|min:1|max:100',
            'offset' => 'integer|min:0',
        ]);

        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);

        $comments = CatchComment::where('catch_id', $catchId)
            ->with(['user:id,name,username,avatar_url'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }

    /**
     * Store a new comment.
     */
    /**
     * @OA\Post(
     *     path="/catches/{catchId}/comments",
     *     tags={"Catch Comments"},
     *     summary="Добавить комментарий к улову",
     *     description="Добавляет новый комментарий к улову",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="catchId",
     *         in="path",
     *         description="ID улова",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="Отличный улов!", maxLength=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Комментарий успешно добавлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Комментарий добавлен"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="content", type="string", example="Отличный улов!"),
     *                 @OA\Property(property="catch_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                     @OA\Property(property="username", type="string", example="ivan_ivanov"),
     *                     @OA\Property(property="avatar_url", type="string", example="https://example.com/avatar.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Улов не найден"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function store(Request $request, int $catchId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $catch = CatchRecord::findOrFail($catchId);

        $comment = CatchComment::create([
            'user_id' => Auth::id(),
            'catch_id' => $catchId,
            'content' => $request->content,
        ]);

        $comment->load(['user:id,name,username,avatar_url']);

        // Update comments count
        $catch->increment('comments_count');

        return response()->json([
            'success' => true,
            'message' => 'Комментарий добавлен',
            'data' => $comment
        ], 201);
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, int $catchId, int $commentId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = CatchComment::where('catch_id', $catchId)
            ->where('id', $commentId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $comment->update([
            'content' => $request->content,
        ]);

        $comment->load(['user:id,name,username,avatar_url']);

        return response()->json([
            'success' => true,
            'message' => 'Комментарий обновлен',
            'data' => $comment
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroy(Request $request, int $catchId, int $commentId): JsonResponse
    {
        $comment = CatchComment::where('catch_id', $catchId)
            ->where('id', $commentId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $comment->delete();

        // Update comments count
        $catch = CatchRecord::findOrFail($catchId);
        $catch->decrement('comments_count');

        return response()->json([
            'success' => true,
            'message' => 'Комментарий удален'
        ]);
    }
}
