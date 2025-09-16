<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannersController extends Controller
{
    public function index(Request $request)
    {
        // For now, return empty array since banners table doesn't exist
        return response()->json([]);
    }
}

