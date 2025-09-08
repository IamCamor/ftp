<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingsController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|in:catch,point,user',
            'entity_id' => 'required|integer',
            'value' => 'required|integer|between:1,5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'fields' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['user_id'] = $request->user()->id;

        // Check if rating already exists
        $existingRating = Rating::where('entity_type', $data['entity_type'])
            ->where('entity_id', $data['entity_id'])
            ->where('user_id', $data['user_id'])
            ->first();

        if ($existingRating) {
            $existingRating->update(['value' => $data['value']]);
            $rating = $existingRating;
        } else {
            $rating = Rating::create($data);
        }

        return response()->json($rating, 201);
    }
}

