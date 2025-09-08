<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WeatherFav;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WeatherFavController extends Controller
{
    public function index(Request $request)
    {
        $favs = WeatherFav::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($favs);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'label' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'fields' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['user_id'] = $request->user()->id;

        $fav = WeatherFav::create($data);

        return response()->json($fav, 201);
    }
}

