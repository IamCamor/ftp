<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();
        
        $user->load(['bonuses', 'ratings']);
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'photo_url' => $user->photo_url,
            'role' => $user->role,
            'total_bonuses' => $user->total_bonuses,
            'average_rating' => $user->average_rating,
            'created_at' => $user->created_at,
        ]);
    }
}

