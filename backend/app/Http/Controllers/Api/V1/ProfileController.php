<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class ProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/profile/me",
     *     tags={"Profile"},
     *     summary="Получить профиль текущего пользователя",
     *     description="Возвращает информацию о профиле авторизованного пользователя",
     *     security={{"jwt": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Профиль пользователя",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Иван Иванов"),
     *             @OA\Property(property="username", type="string", example="ivan_ivanov"),
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="photo_url", type="string", example="https://example.com/photo.jpg"),
     *             @OA\Property(property="role", type="string", example="user"),
     *             @OA\Property(property="is_premium", type="boolean", example=false),
     *             @OA\Property(property="crown_icon_url", type="string", example=null),
     *             @OA\Property(property="followers_count", type="integer", example=150),
     *             @OA\Property(property="following_count", type="integer", example=75),
     *             @OA\Property(property="is_online", type="boolean", example=true),
     *             @OA\Property(property="last_seen_at", type="string", format="date-time"),
     *             @OA\Property(property="bio", type="string", example="Люблю рыбалку"),
     *             @OA\Property(property="created_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Пользователь не найден"
     *     )
     * )
     */
    public function me(Request $request)
    {
        // Получаем токен из заголовка
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
            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            
            $photoUrl = $user->avatar_path ?? '';
            if ($photoUrl) {
                // Если URL относительный, делаем его абсолютным
                if (strpos($photoUrl, 'http') !== 0) {
                    $photoUrl = config('app.url') . $photoUrl;
                }
                $photoUrl .= '?v=' . time(); // Добавляем timestamp для обновления кэша
            }
            
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->handle ?? '',
                'email' => $user->email,
                'photo_url' => $photoUrl,
                'role' => 'user',
                'bio' => $user->bio ?? '',
                'location' => $user->location ?? '',
                'website' => $user->website ?? '',
                'total_bonuses' => 0,
                'average_rating' => 0,
                'created_at' => $user->created_at,
            ]);
        }
        
        return response()->json(['error' => 'Invalid token'], 401);
    }

    /**
     * @OA\Put(
     *     path="/profile/me",
     *     tags={"Profile"},
     *     summary="Обновить профиль пользователя",
     *     description="Обновляет информацию профиля авторизованного пользователя",
     *     security={{"jwt": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Иван Иванов", description="Имя пользователя"),
     *             @OA\Property(property="username", type="string", example="ivan_ivanov", description="Имя пользователя"),
     *             @OA\Property(property="bio", type="string", example="Люблю рыбалку", description="Биография"),
     *             @OA\Property(property="location", type="string", example="Москва", description="Местоположение"),
     *             @OA\Property(property="website", type="string", example="https://example.com", description="Веб-сайт")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Профиль успешно обновлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                 @OA\Property(property="username", type="string", example="ivan_ivanov"),
     *                 @OA\Property(property="bio", type="string", example="Люблю рыбалку"),
     *                 @OA\Property(property="location", type="string", example="Москва"),
     *                 @OA\Property(property="website", type="string", example="https://example.com"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'bio' => 'sometimes|string|max:1000',
            'location' => 'sometimes|string|max:255',
            'website' => 'sometimes|string|max:255',
        ]);

        $user->update($request->only([
            'name', 'username', 'bio', 'location', 'website'
        ]));

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'photo_url' => $user->photo_url,
            'role' => $user->role,
            'bio' => $user->bio,
            'location' => $user->location,
            'website' => $user->website,
            'total_bonuses' => $user->total_bonuses,
            'average_rating' => $user->average_rating,
            'created_at' => $user->created_at,
        ]);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        // Получаем токен из заголовка
        $token = $request->header('Authorization');
        if ($token) {
            $token = str_replace('Bearer ', '', $token);
        }
        
        // Если токен не найден, возвращаем ошибку
        if (!$token) {
            return response()->json(['error' => 'Token required'], 401);
        }
        
        // Извлекаем user_id из токена (формат: simple_token_{user_id}_{timestamp})
        if (!preg_match('/^simple_token_(\d+)_\d+$/', $token, $matches)) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
        
        $userId = $matches[1];
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        try {
            // Delete old avatar if exists
            if ($user->avatar_path) {
                $oldPath = str_replace('/storage/', '', $user->avatar_path);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Store new avatar
            $file = $request->file('avatar');
            $filename = $user->id . '_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            $path = $file->storeAs('avatars', $filename, 'public');
            
            // Update user avatar_path
            $avatarUrl = Storage::url($path);
            $user->update([
                'avatar_path' => $avatarUrl
            ]);

            \Log::info("Avatar uploaded successfully", [
                'user_id' => $user->id,
                'file_path' => $path,
                'avatar_url' => $avatarUrl,
                'avatar_path_in_db' => $user->fresh()->avatar_path
            ]);

            // Если URL относительный, делаем его абсолютным
            $fullAvatarUrl = $avatarUrl;
            if (strpos($avatarUrl, 'http') !== 0) {
                $fullAvatarUrl = config('app.url') . $avatarUrl;
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Аватарка успешно загружена',
                'photo_url' => $fullAvatarUrl . '?v=' . time() // Добавляем timestamp для обновления кэша
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при загрузке аватарки',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        // Получаем токен из заголовка
        $token = $request->header('Authorization');
        if ($token) {
            $token = str_replace('Bearer ', '', $token);
        }
        
        // Если токен не найден, возвращаем ошибку
        if (!$token) {
            return response()->json(['error' => 'Token required'], 401);
        }
        
        // Извлекаем user_id из токена (формат: simple_token_{user_id}_{timestamp})
        if (!preg_match('/^simple_token_(\d+)_\d+$/', $token, $matches)) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
        
        $userId = $matches[1];
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        try {
            // Delete avatar file if exists
            if ($user->avatar_path) {
                $path = str_replace('/storage/', '', $user->avatar_path);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            // Update user avatar_path to null
            $user->update([
                'avatar_path' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Аватарка успешно удалена'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении аватарки',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

