<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CatchComment;
use App\Models\CatchRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class CatchCommentController extends Controller
{
    /**
     * Get comments for a catch.
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
    public function store(Request $request, int $catchId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $catch = CatchRecord::findOrFail($catchId);

        $comment = CatchComment::create([
            'user_id' => $request->user()->id,
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
            ->where('user_id', $request->user()->id)
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
            ->where('user_id', $request->user()->id)
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
