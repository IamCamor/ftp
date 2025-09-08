<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannersController extends Controller
{
    public function index(Request $request)
    {
        $slot = $request->get('slot');

        $query = Banner::active();

        if ($slot) {
            $query->forSlot($slot);
        }

        $banners = $query->get();

        return response()->json($banners);
    }
}

