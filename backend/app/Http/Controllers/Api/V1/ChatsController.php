<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class ChatsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/chats",
     *     tags={"Chats"},
     *     summary="Получить список чатов",
     *     description="Возвращает список чатов пользователя с последними сообщениями",
     *     security={{"jwt": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список чатов",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="type", type="string", example="private"),
     *                 @OA\Property(property="name", type="string", example="Чат с Иваном"),
     *                 @OA\Property(property="unread_count", type="integer", example=3),
     *                 @OA\Property(property="last_message_at", type="string", format="date-time"),
     *                 @OA\Property(property="latest_message", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="content", type="string", example="Привет! Как дела?"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                         @OA\Property(property="username", type="string", example="ivan_ivanov")
     *                     )
     *                 ),
     *                 @OA\Property(property="participants", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                     @OA\Property(property="username", type="string", example="ivan_ivanov"),
     *                     @OA\Property(property="avatar_url", type="string", example="https://example.com/avatar.jpg")
     *                 ))
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
        $user = $request->user();
        
        $chats = Chat::with(['latestMessage.user', 'group', 'event'])
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($chats);
    }

    public function show($id)
    {
        $chat = Chat::with(['messages.user', 'group', 'event'])
            ->findOrFail($id);

        return response()->json($chat);
    }

    public function messages(Request $request, $id)
    {
        $limit = $request->get('limit', 50);
        $offset = $request->get('offset', 0);

        $messages = ChatMessage::with('user')
            ->where('chat_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    /**
     * @OA\Post(
     *     path="/chats/{id}/messages",
     *     tags={"Chats"},
     *     summary="Отправить сообщение в чат",
     *     description="Отправляет новое сообщение в чат",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID чата",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="Привет! Как дела?", maxLength=1000),
     *             @OA\Property(property="attachment_url", type="string", example="https://example.com/image.jpg", maxLength=512),
     *             @OA\Property(property="attachment_type", type="string", enum={"image", "file", "location"}, example="image")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Сообщение отправлено",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Message sent successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="content", type="string", example="Привет! Как дела?"),
     *                 @OA\Property(property="attachment_url", type="string", example="https://example.com/image.jpg"),
     *                 @OA\Property(property="attachment_type", type="string", example="image"),
     *                 @OA\Property(property="chat_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
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
     *         response=403,
     *         description="Нет прав на отправку сообщений в чат"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Чат не найден"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function sendMessage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'attachment_url' => 'nullable|string|max:512',
            'attachment_type' => 'nullable|in:image,file,location',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'fields' => $validator->errors()
            ], 422);
        }

        $chat = Chat::findOrFail($id);
        $user = $request->user();

        // Check if user is participant
        if (!$chat->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Not a participant'], 403);
        }

        $message = ChatMessage::create([
            'chat_id' => $id,
            'user_id' => $user->id,
            'message' => $request->message,
            'attachment_url' => $request->attachment_url,
            'attachment_type' => $request->attachment_type,
        ]);

        $message->load('user');

        return response()->json($message, 201);
    }

    public function markAsRead(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);
        $user = $request->user();

        // Mark all messages in chat as read for this user
        ChatMessage::where('chat_id', $id)
            ->where('user_id', '!=', $user->id)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Messages marked as read']);
    }
}

