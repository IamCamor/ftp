<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserCodeController extends Controller
{
    /**
     * Get user's invite code
     */
    public function getInviteCode(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }
        
        // Генерируем случайный пригласительный код
        $inviteCode = 'INV' . strtoupper(substr(md5(uniqid($user->id, true)), 0, 8));
        
        return response()->json([
            'data' => [
                'code' => $inviteCode,
                'user_id' => $user->id,
                'created_at' => now()->toISOString(),
                'expires_at' => now()->addYear()->toISOString(),
                'uses_count' => 0,
                'max_uses' => 10,
                'is_active' => true
            ]
        ]);
    }
    
    /**
     * Get user's promo code
     */
    public function getPromoCode(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }
        
        // Генерируем случайный промо-код
        $promoCode = 'PROMO' . strtoupper(substr(md5(uniqid($user->id, true)), 0, 6));
        
        return response()->json([
            'data' => [
                'code' => $promoCode,
                'user_id' => $user->id,
                'discount_percent' => 10,
                'discount_amount' => 100,
                'created_at' => now()->toISOString(),
                'expires_at' => now()->addMonth()->toISOString(),
                'uses_count' => 0,
                'max_uses' => 5,
                'is_active' => true
            ]
        ]);
    }
}
