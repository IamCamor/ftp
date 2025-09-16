<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\WeatherPoint;
use Illuminate\Support\Facades\Validator;

class WeatherPointController extends Controller
{
    /**
     * Получить все точки погоды пользователя
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $points = WeatherPoint::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $points
        ]);
    }

    /**
     * Создать новую точку погоды
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Проверяем лимит точек (максимум 5)
        $existingCount = WeatherPoint::where('user_id', $user->id)->count();
        if ($existingCount >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Максимальное количество точек погоды: 5'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $point = WeatherPoint::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'city' => $request->city,
            'country' => $request->country
        ]);

        return response()->json([
            'success' => true,
            'data' => $point
        ], 201);
    }

    /**
     * Обновить точку погоды
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $point = WeatherPoint::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$point) {
            return response()->json([
                'success' => false,
                'message' => 'Точка погоды не найдена'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'lat' => 'sometimes|required|numeric|between:-90,90',
            'lng' => 'sometimes|required|numeric|between:-180,180',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $point->update($request->only(['name', 'lat', 'lng', 'city', 'country']));

        return response()->json([
            'success' => true,
            'data' => $point
        ]);
    }

    /**
     * Удалить точку погоды
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $point = WeatherPoint::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$point) {
            return response()->json([
                'success' => false,
                'message' => 'Точка погоды не найдена'
            ], 404);
        }

        $point->delete();

        return response()->json([
            'success' => true,
            'message' => 'Точка погоды удалена'
        ]);
    }
}

