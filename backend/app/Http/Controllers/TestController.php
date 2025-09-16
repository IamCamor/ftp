<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class TestController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0'
        ]);
    }
    
    public function test(): JsonResponse
    {
        return response()->json([
            'message' => 'Test endpoint working!',
            'time' => now()->toISOString()
        ]);
    }
}
