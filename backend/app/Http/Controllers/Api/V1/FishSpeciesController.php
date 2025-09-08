<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FishSpecies;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FishSpeciesController extends Controller
{
    /**
     * Get all fish species.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'nullable|string|in:freshwater,saltwater,both',
            'search' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = FishSpecies::active();

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->search) {
            $query->search($request->search);
        }

        $fishSpecies = $query->orderBy('view_count', 'desc')
            ->orderBy('name')
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $fishSpecies
        ]);
    }

    /**
     * Get fish species by slug.
     */
    public function show(FishSpecies $fishSpecies): JsonResponse
    {
        if (!$fishSpecies->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Вид рыбы не найден'
            ], 404);
        }

        $fishSpecies->incrementViewCount();

        return response()->json([
            'success' => true,
            'data' => $fishSpecies
        ]);
    }

    /**
     * Get popular fish species.
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        
        $fishSpecies = FishSpecies::active()
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $fishSpecies
        ]);
    }

    /**
     * Get fish species by category.
     */
    public function byCategory(string $category): JsonResponse
    {
        $validCategories = ['freshwater', 'saltwater', 'both'];
        
        if (!in_array($category, $validCategories)) {
            return response()->json([
                'success' => false,
                'message' => 'Неверная категория'
            ], 400);
        }

        $fishSpecies = FishSpecies::active()
            ->where('category', $category)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $fishSpecies
        ]);
    }

    /**
     * Get protected fish species.
     */
    public function protected(): JsonResponse
    {
        $fishSpecies = FishSpecies::active()
            ->protected()
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $fishSpecies
        ]);
    }

    /**
     * Search fish species.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $request->query;
        $limit = $request->get('limit', 20);

        $fishSpecies = FishSpecies::active()
            ->search($query)
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $fishSpecies
        ]);
    }
}
