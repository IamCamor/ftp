<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FishSpecies;
use App\Models\FishingKnot;
use App\Models\Boat;
use App\Models\FishingMethod;
use App\Models\FishingTackle;
use App\Models\BoatEngine;
use App\Models\FishingLocation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReferenceController extends Controller
{
    /**
     * Get all reference types with counts.
     */
    public function index(): JsonResponse
    {
        $references = [
            'fish_species' => [
                'name' => 'Виды рыб',
                'count' => FishSpecies::active()->count(),
                'icon' => 'fish',
                'description' => 'Справочник видов рыб с описаниями и характеристиками'
            ],
            'fishing_knots' => [
                'name' => 'Рыболовные узлы',
                'count' => FishingKnot::active()->count(),
                'icon' => 'knot',
                'description' => 'Коллекция рыболовных узлов с инструкциями'
            ],
            'boats' => [
                'name' => 'Лодки',
                'count' => Boat::active()->count(),
                'icon' => 'boat',
                'description' => 'Каталог лодок для рыбалки'
            ],
            'fishing_methods' => [
                'name' => 'Способы ловли',
                'count' => FishingMethod::active()->count(),
                'icon' => 'fishing',
                'description' => 'Методы и техники рыбной ловли'
            ],
            'fishing_tackle' => [
                'name' => 'Снасти',
                'count' => FishingTackle::active()->count(),
                'icon' => 'tackle',
                'description' => 'Рыболовные снасти и оборудование'
            ],
            'boat_engines' => [
                'name' => 'Моторы',
                'count' => BoatEngine::active()->count(),
                'icon' => 'engine',
                'description' => 'Лодочные моторы и двигатели'
            ],
            'fishing_locations' => [
                'name' => 'Места ловли',
                'count' => FishingLocation::active()->count(),
                'icon' => 'location',
                'description' => 'Популярные места для рыбалки'
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $references
        ]);
    }

    /**
     * Search across all reference types.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100',
            'type' => 'nullable|string|in:fish_species,fishing_knots,boats,fishing_methods,fishing_tackle,boat_engines,fishing_locations',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $request->query;
        $type = $request->type;
        $limit = $request->get('limit', 20);

        $results = [];

        if (!$type || $type === 'fish_species') {
            $results['fish_species'] = FishSpecies::active()
                ->search($query)
                ->limit($limit)
                ->get()
                ->map(fn($item) => $this->formatFishSpecies($item));
        }

        if (!$type || $type === 'fishing_knots') {
            $results['fishing_knots'] = FishingKnot::active()
                ->search($query)
                ->limit($limit)
                ->get()
                ->map(fn($item) => $this->formatFishingKnot($item));
        }

        if (!$type || $type === 'boats') {
            $results['boats'] = Boat::active()
                ->search($query)
                ->limit($limit)
                ->get()
                ->map(fn($item) => $this->formatBoat($item));
        }

        if (!$type || $type === 'fishing_methods') {
            $results['fishing_methods'] = FishingMethod::active()
                ->search($query)
                ->limit($limit)
                ->get()
                ->map(fn($item) => $this->formatFishingMethod($item));
        }

        if (!$type || $type === 'fishing_tackle') {
            $results['fishing_tackle'] = FishingTackle::active()
                ->search($query)
                ->limit($limit)
                ->get()
                ->map(fn($item) => $this->formatFishingTackle($item));
        }

        if (!$type || $type === 'boat_engines') {
            $results['boat_engines'] = BoatEngine::active()
                ->search($query)
                ->limit($limit)
                ->get()
                ->map(fn($item) => $this->formatBoatEngine($item));
        }

        if (!$type || $type === 'fishing_locations') {
            $results['fishing_locations'] = FishingLocation::active()
                ->search($query)
                ->limit($limit)
                ->get()
                ->map(fn($item) => $this->formatFishingLocation($item));
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * Format fish species for API response.
     */
    private function formatFishSpecies(FishSpecies $fish): array
    {
        return [
            'id' => $fish->id,
            'name' => $fish->name,
            'scientific_name' => $fish->scientific_name,
            'slug' => $fish->slug,
            'description' => $fish->description,
            'photo_url' => $fish->photo_url,
            'category' => $fish->category,
            'category_display_name' => $fish->category_display_name,
            'size_range' => $fish->size_range,
            'weight_range' => $fish->weight_range,
            'is_protected' => $fish->is_protected,
            'view_count' => $fish->view_count,
        ];
    }

    /**
     * Format fishing knot for API response.
     */
    private function formatFishingKnot(FishingKnot $knot): array
    {
        return [
            'id' => $knot->id,
            'name' => $knot->name,
            'slug' => $knot->slug,
            'description' => $knot->description,
            'purpose' => $knot->purpose,
            'difficulty' => $knot->difficulty,
            'difficulty_display_name' => $knot->difficulty_display_name,
            'strength_display' => $knot->strength_display,
            'photo_url' => $knot->photo_url,
            'view_count' => $knot->view_count,
        ];
    }

    /**
     * Format boat for API response.
     */
    private function formatBoat(Boat $boat): array
    {
        return [
            'id' => $boat->id,
            'name' => $boat->name,
            'brand' => $boat->brand,
            'model' => $boat->model,
            'slug' => $boat->slug,
            'description' => $boat->description,
            'type' => $boat->type,
            'type_display_name' => $boat->type_display_name,
            'dimensions' => $boat->dimensions,
            'capacity' => $boat->capacity,
            'price_range' => $boat->price_range,
            'photo_url' => $boat->photo_url,
            'view_count' => $boat->view_count,
        ];
    }

    /**
     * Format fishing method for API response.
     */
    private function formatFishingMethod(FishingMethod $method): array
    {
        return [
            'id' => $method->id,
            'name' => $method->name,
            'slug' => $method->slug,
            'description' => $method->description,
            'technique' => $method->technique,
            'difficulty' => $method->difficulty,
            'difficulty_display_name' => $method->difficulty_display_name,
            'season' => $method->season,
            'season_display_name' => $method->season_display_name,
            'photo_url' => $method->photo_url,
            'view_count' => $method->view_count,
        ];
    }

    /**
     * Format fishing tackle for API response.
     */
    private function formatFishingTackle(FishingTackle $tackle): array
    {
        return [
            'id' => $tackle->id,
            'name' => $tackle->name,
            'brand' => $tackle->brand,
            'model' => $tackle->model,
            'slug' => $tackle->slug,
            'description' => $tackle->description,
            'type' => $tackle->type,
            'type_display_name' => $tackle->type_display_name,
            'category' => $tackle->category,
            'price_range' => $tackle->price_range,
            'photo_url' => $tackle->photo_url,
            'view_count' => $tackle->view_count,
        ];
    }

    /**
     * Format boat engine for API response.
     */
    private function formatBoatEngine(BoatEngine $engine): array
    {
        return [
            'id' => $engine->id,
            'name' => $engine->name,
            'brand' => $engine->brand,
            'model' => $engine->model,
            'slug' => $engine->slug,
            'description' => $engine->description,
            'type' => $engine->type,
            'type_display_name' => $engine->type_display_name,
            'fuel_type' => $engine->fuel_type,
            'fuel_type_display_name' => $engine->fuel_type_display_name,
            'power_display' => $engine->power_display,
            'price_range' => $engine->price_range,
            'photo_url' => $engine->photo_url,
            'view_count' => $engine->view_count,
        ];
    }

    /**
     * Format fishing location for API response.
     */
    private function formatFishingLocation(FishingLocation $location): array
    {
        return [
            'id' => $location->id,
            'name' => $location->name,
            'slug' => $location->slug,
            'description' => $location->description,
            'type' => $location->type,
            'type_display_name' => $location->type_display_name,
            'water_type' => $location->water_type,
            'water_type_display_name' => $location->water_type_display_name,
            'region' => $location->region,
            'country' => $location->country,
            'full_name' => $location->full_name,
            'coordinates' => $location->coordinates,
            'photo_url' => $location->photo_url,
            'view_count' => $location->view_count,
        ];
    }
}
