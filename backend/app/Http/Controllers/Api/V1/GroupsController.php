<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupsController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);

        $groups = Group::with(['owner', 'members'])
            ->where('privacy', 'public')
            ->orderBy('members_count', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return response()->json($groups);
    }

    public function show($id)
    {
        $group = Group::with(['owner', 'members', 'events'])
            ->findOrFail($id);

        return response()->json($group);
    }

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

