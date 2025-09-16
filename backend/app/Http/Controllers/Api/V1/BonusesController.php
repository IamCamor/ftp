<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BonusesController extends Controller
{
    public function index(Request $request)
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
            
            // Получаем бонусы из таблицы bonus_ledger
            $bonuses = \DB::table('bonus_ledger')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json($bonuses);
        }
        
        return response()->json(['error' => 'Invalid token'], 401);
    }
}

