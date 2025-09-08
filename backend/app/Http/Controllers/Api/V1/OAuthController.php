<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OAuthIdentity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class OAuthController extends Controller
{
    public function redirect($provider)
    {
        $validProviders = ['google', 'vk', 'yandex', 'apple'];
        
        if (!in_array($provider, $validProviders)) {
            return response()->json(['message' => 'Invalid provider'], 400);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider, Request $request)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            $oauthIdentity = OAuthIdentity::where('provider', $provider)
                ->where('provider_user_id', $socialUser->getId())
                ->first();

            if ($oauthIdentity) {
                $user = $oauthIdentity->user;
            } else {
                $user = User::where('email', $socialUser->getEmail())->first();
                
                if (!$user) {
                    $user = User::create([
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'photo_url' => $socialUser->getAvatar(),
                    ]);
                }

                OAuthIdentity::create([
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'provider_user_id' => $socialUser->getId(),
                    'access_token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                ]);
            }

            $token = JWTAuth::fromUser($user);
            
            $redirectUrl = config('app.frontend_url', 'https://www.fishtrackpro.ru') . '/feed?token=' . $token;
            
            return redirect($redirectUrl);
            
        } catch (\Exception $e) {
            return redirect(config('app.frontend_url', 'https://www.fishtrackpro.ru') . '/auth/login?error=oauth_failed');
        }
    }
}

