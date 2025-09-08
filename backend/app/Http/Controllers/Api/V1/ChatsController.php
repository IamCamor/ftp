<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatsController extends Controller
{
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

