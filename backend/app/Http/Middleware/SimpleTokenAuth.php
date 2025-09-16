<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SimpleTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');
        
        if ($token) {
            $token = str_replace('Bearer ', '', $token);
        }
        
        // Если токен не найден, возвращаем ошибку
        if (!$token) {
            return response()->json(['error' => 'Token required'], 401);
        }
        
        // Извлекаем user_id из токена (формат: simple_token_{user_id}_{timestamp})
        if (preg_match('/^simple_token_(\d+)_\d+$/', $token, $matches)) {
            $userId = $matches[1];
            
            // Проверяем, существует ли пользователь
            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            
            // Добавляем пользователя в запрос для использования в контроллерах
            $request->merge(['auth_user_id' => $userId]);
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
            
            return $next($request);
        }
        
        return response()->json(['error' => 'Invalid token'], 401);
    }
}

