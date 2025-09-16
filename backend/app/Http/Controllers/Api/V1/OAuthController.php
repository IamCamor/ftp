<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OAuthIdentity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class OAuthController extends Controller
{
    /**
     * Загружает аватарку из URL и сохраняет локально
     */
    private function downloadAndSaveAvatar($avatarUrl, $userId)
    {
        if (!$avatarUrl) {
            return null;
        }

        try {
            // Получаем содержимое файла
            $imageData = file_get_contents($avatarUrl);
            if ($imageData === false) {
                \Log::warning("Failed to download avatar from URL", ['url' => $avatarUrl]);
                return null;
            }

            // Определяем расширение файла
            $extension = 'jpg'; // по умолчанию
            $contentType = get_headers($avatarUrl, 1)['Content-Type'] ?? '';
            if (strpos($contentType, 'png') !== false) {
                $extension = 'png';
            } elseif (strpos($contentType, 'gif') !== false) {
                $extension = 'gif';
            } elseif (strpos($contentType, 'webp') !== false) {
                $extension = 'webp';
            }

            // Генерируем имя файла
            $filename = $userId . '_' . time() . '_' . Str::random(10) . '.' . $extension;
            $path = 'avatars/' . $filename;

            // Сохраняем файл
            Storage::disk('public')->put($path, $imageData);

            // Возвращаем полный URL для сохранения в базе
            $url = Storage::url($path);
            if (strpos($url, 'http') !== 0) {
                $url = config('app.url') . $url;
            }
            return $url;

        } catch (\Exception $e) {
            \Log::error("Error downloading avatar", [
                'url' => $avatarUrl,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function redirect($provider)
    {
        $validProviders = ['google', 'vk', 'yandex', 'apple', 'telegram'];
        
        if (!in_array($provider, $validProviders)) {
            return response()->json(['message' => 'Invalid provider'], 400);
        }

        // Special handling for Telegram
        if ($provider === 'telegram') {
            return $this->redirectToTelegram();
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider, Request $request)
    {
        try {
            \Log::info("OAuth callback for provider: {$provider}", $request->all());
            $socialUser = Socialite::driver($provider)->user();
            \Log::info("Social user data received", [
                'id' => $socialUser->getId(),
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
            ]);
            
            $oauthIdentity = OAuthIdentity::where('provider', $provider)
                ->where('provider_user_id', $socialUser->getId())
                ->first();

            if ($oauthIdentity) {
                $user = $oauthIdentity->user;
                
                // Загружаем аватарку локально
                $localAvatarPath = $this->downloadAndSaveAvatar($socialUser->getAvatar(), $user->id);
                
                // Обновляем данные пользователя при каждом входе через OAuth
                $user->update([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'handle' => $socialUser->getNickname() ?? $socialUser->getName(),
                    'avatar_path' => $localAvatarPath,
                ]);
                
                // Обновляем OAuth identity с новыми токенами
                $oauthIdentity->update([
                    'access_token' => $socialUser->token ?? null,
                    'refresh_token' => $socialUser->refreshToken ?? null,
                ]);
            } else {
                $user = User::where('email', $socialUser->getEmail())->first();
                
                if (!$user) {
                    // Создаем нового пользователя
                    $user = User::create([
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'handle' => $socialUser->getNickname() ?? $socialUser->getName(),
                        'avatar_path' => null, // Временно null, обновим после создания
                        'password' => bcrypt(uniqid() . time()), // Генерируем случайный пароль для OAuth пользователей
                    ]);
                    
                    // Загружаем аватарку локально после создания пользователя
                    $localAvatarPath = $this->downloadAndSaveAvatar($socialUser->getAvatar(), $user->id);
                    $user->update(['avatar_path' => $localAvatarPath]);
                } else {
                    // Загружаем аватарку локально для существующего пользователя
                    $localAvatarPath = $this->downloadAndSaveAvatar($socialUser->getAvatar(), $user->id);
                    
                    // Обновляем существующего пользователя
                    $user->update([
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'handle' => $socialUser->getNickname() ?? $socialUser->getName(),
                        'avatar_path' => $localAvatarPath,
                    ]);
                }

                OAuthIdentity::create([
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'provider_user_id' => $socialUser->getId(),
                    'access_token' => $socialUser->token ?? null,
                    'refresh_token' => $socialUser->refreshToken ?? null,
                ]);
            }

            // Используем упрощенную систему токенов
            $token = 'simple_token_' . $user->id . '_' . time();
            
            $redirectUrl = config('app.frontend_url', 'https://www.fishtrackpro.ru') . '/feed?token=' . $token;
            
            \Log::info("OAuth successful redirect", [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_handle' => $user->handle,
                'user_avatar' => $user->avatar_path,
                'token' => $token,
                'redirect_url' => $redirectUrl
            ]);
            
            return redirect($redirectUrl);
            
        } catch (\Exception $e) {
            \Log::error("OAuth callback error for provider {$provider}: " . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
            ]);
            return redirect(config('app.frontend_url', 'https://www.fishtrackpro.ru') . '/auth/login?error=oauth_failed');
        }
    }

    /**
     * Redirect to Telegram OAuth
     */
    private function redirectToTelegram()
    {
        $botUsername = config('telegram.bot_username', 'fishtrackpro_bot');
        $redirectUrl = config('app.frontend_url', 'https://www.fishtrackpro.ru') . '/auth/telegram/callback';
        
        $telegramUrl = "https://t.me/{$botUsername}?start=" . base64_encode($redirectUrl);
        
        return redirect($telegramUrl);
    }

    /**
     * Handle Telegram OAuth callback
     */
    public function telegramCallback(Request $request)
    {
        try {
            $data = $request->all();
            
            // Telegram sends data via POST with user information
            if (isset($data['id'])) {
                $telegramUser = $data;
            } else {
                // If no data, redirect to login with error
                return redirect(config('app.frontend_url', 'https://www.fishtrackpro.ru') . '/auth/login?error=telegram_auth_failed');
            }

            // Find or create user
            $oauthIdentity = OAuthIdentity::where('provider', 'telegram')
                ->where('provider_user_id', $telegramUser['id'])
                ->first();

            if ($oauthIdentity) {
                $user = $oauthIdentity->user;
                
                // Загружаем аватарку локально для существующего пользователя
                $localAvatarPath = $this->downloadAndSaveAvatar($telegramUser['photo_url'] ?? null, $user->id);
                $user->update(['avatar_path' => $localAvatarPath]);
            } else {
                // Create new user from Telegram data
                $user = User::create([
                    'name' => $telegramUser['first_name'] . ' ' . ($telegramUser['last_name'] ?? ''),
                    'email' => $telegramUser['id'] . '@telegram.local', // Telegram doesn't provide email
                    'handle' => $telegramUser['username'] ?? $telegramUser['first_name'],
                    'avatar_path' => null, // Временно null, обновим после создания
                    'password' => bcrypt(uniqid() . time()), // Генерируем случайный пароль для OAuth пользователей
                ]);
                
                // Загружаем аватарку локально после создания пользователя
                $localAvatarPath = $this->downloadAndSaveAvatar($telegramUser['photo_url'] ?? null, $user->id);
                $user->update(['avatar_path' => $localAvatarPath]);

                OAuthIdentity::create([
                    'user_id' => $user->id,
                    'provider' => 'telegram',
                    'provider_user_id' => $telegramUser['id'],
                    'access_token' => null, // Telegram doesn't use OAuth tokens
                    'refresh_token' => null,
                ]);
            }

            // Используем упрощенную систему токенов
            $token = 'simple_token_' . $user->id . '_' . time();
            
            $redirectUrl = config('app.frontend_url', 'https://www.fishtrackpro.ru') . '/feed?token=' . $token;
            
            \Log::info("OAuth successful redirect", [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_handle' => $user->handle,
                'user_avatar' => $user->avatar_path,
                'token' => $token,
                'redirect_url' => $redirectUrl
            ]);
            
            return redirect($redirectUrl);
            
        } catch (\Exception $e) {
            return redirect(config('app.frontend_url', 'https://www.fishtrackpro.ru') . '/auth/login?error=telegram_auth_failed');
        }
    }
}

