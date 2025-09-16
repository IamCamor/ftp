<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PrivacySettingsController extends Controller
{
    /**
     * Получить настройки приватности пользователя
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Возвращаем настройки приватности пользователя
        $privacySettings = [
            'allow_friend_requests' => true,
            'allow_follow_notifications' => true,
            'show_online_status' => true,
            'show_last_seen' => true,
            'profile_visibility' => 'all', // all, friends, me
            'catch_visibility' => 'all', // all, friends, me
            'track_visibility' => 'all', // all, friends, me
        ];
        
        return response()->json([
            'success' => true,
            'data' => $privacySettings
        ]);
    }
    
    /**
     * Обновить настройки приватности пользователя
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'allow_friend_requests' => 'boolean',
            'allow_follow_notifications' => 'boolean',
            'show_online_status' => 'boolean',
            'show_last_seen' => 'boolean',
            'profile_visibility' => 'in:all,friends,me',
            'catch_visibility' => 'in:all,friends,me',
            'track_visibility' => 'in:all,friends,me',
        ]);
        
        // Здесь можно сохранить настройки в базе данных
        // Пока просто возвращаем успешный ответ
        
        return response()->json([
            'success' => true,
            'message' => 'Настройки приватности обновлены',
            'data' => $validated
        ]);
    }
}