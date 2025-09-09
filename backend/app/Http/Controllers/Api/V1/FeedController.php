<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CatchRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    /**
     * Get feed with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'nullable|string|in:all,following,nearby',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:1|max:1000', // km
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $user = Auth::user();
        $type = $request->get('type', 'all');
        $limit = $request->get('limit', 20);

        $query = CatchRecord::with([
            'user', 
            'point', 
            'fishSpecies', 
            'fishingMethod',
            'fishingLocation'
        ])->active();

        // Apply filters based on type
        switch ($type) {
            case 'following':
                $followingIds = $user->following()->pluck('users.id')->toArray();
                $followingIds[] = $user->id; // Include own posts
                $query->whereIn('user_id', $followingIds);
                break;
                
            case 'nearby':
                if ($request->latitude && $request->longitude) {
                    $radius = $request->get('radius', 50); // Default 50km
                    $query->whereHas('point', function ($q) use ($request, $radius) {
                        $q->whereRaw(
                            "ST_Distance_Sphere(POINT(longitude, latitude), POINT(?, ?)) <= ?",
                            [$request->longitude, $request->latitude, $radius * 1000]
                        );
                    });
                }
                break;
                
            case 'all':
            default:
                // No additional filters for 'all'
                break;
        }

        $catches = $query->orderBy('created_at', 'desc')
                        ->paginate($limit);

        // Transform data
        $catches->getCollection()->transform(function ($catch) use ($user) {
            return $this->transformCatch($catch, $user);
        });

        return response()->json([
            'success' => true,
            'data' => $catches
        ]);
    }

    /**
     * Get user's personal feed.
     */
    public function personal(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = $request->get('limit', 20);

        $catches = CatchRecord::with([
            'user', 
            'point', 
            'fishSpecies', 
            'fishingMethod',
            'fishingLocation'
        ])->where('user_id', $user->id)
          ->active()
          ->orderBy('created_at', 'desc')
          ->paginate($limit);

        $catches->getCollection()->transform(function ($catch) use ($user) {
            return $this->transformCatch($catch, $user);
        });

        return response()->json([
            'success' => true,
            'data' => $catches
        ]);
    }

    /**
     * Get nearby catches.
     */
    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:1|max:1000',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $radius = $request->get('radius', 50); // Default 50km
        $limit = $request->get('limit', 20);

        $catches = CatchRecord::with([
            'user', 
            'point', 
            'fishSpecies', 
            'fishingMethod',
            'fishingLocation'
        ])->whereHas('point', function ($q) use ($request, $radius) {
            $q->whereRaw(
                "ST_Distance_Sphere(POINT(longitude, latitude), POINT(?, ?)) <= ?",
                [$request->longitude, $request->latitude, $radius * 1000]
            );
        })->active()
          ->orderBy('created_at', 'desc')
          ->paginate($limit);

        $user = Auth::user();
        $catches->getCollection()->transform(function ($catch) use ($user) {
            return $this->transformCatch($catch, $user);
        });

        return response()->json([
            'success' => true,
            'data' => $catches
        ]);
    }

    /**
     * Get following feed.
     */
    public function following(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = $request->get('limit', 20);

        $followingIds = $user->following()->pluck('users.id')->toArray();
        $followingIds[] = $user->id; // Include own posts

        $catches = CatchRecord::with([
            'user', 
            'point', 
            'fishSpecies', 
            'fishingMethod',
            'fishingLocation'
        ])->whereIn('user_id', $followingIds)
          ->active()
          ->orderBy('created_at', 'desc')
          ->paginate($limit);

        $catches->getCollection()->transform(function ($catch) use ($user) {
            return $this->transformCatch($catch, $user);
        });

        return response()->json([
            'success' => true,
            'data' => $catches
        ]);
    }

    /**
     * Transform catch record for API response.
     */
    private function transformCatch(CatchRecord $catch, ?User $user): array
    {
        return [
            'id' => $catch->id,
            'user' => [
                'id' => $catch->user->id,
                'name' => $catch->user->name,
                'username' => $catch->user->username,
                'photo_url' => $catch->user->photo_url,
                'role' => $catch->user->role,
                'is_premium' => $catch->user->is_premium,
                'crown_icon_url' => $catch->user->getCrownIconUrl(),
                'followers_count' => $catch->user->followers_count,
                'is_online' => $catch->user->is_online,
            ],
            'fish_type' => $catch->fish_type,
            'weight' => $catch->weight,
            'length' => $catch->length,
            'bait' => $catch->bait,
            'weather' => $catch->weather,
            'temperature' => $catch->temperature,
            'description' => $catch->description,
            'photos' => $catch->photos ?? [],
            'videos' => $catch->videos ?? [],
            'main_photo' => $catch->main_photo,
            'main_video' => $catch->main_video,
            'media_count' => $catch->media_count,
            'caught_at' => $catch->caught_at,
            'likes_count' => $catch->likes_count,
            'comments_count' => $catch->comments_count,
            'is_public' => $catch->is_public,
            'point' => $catch->point ? [
                'id' => $catch->point->id,
                'name' => $catch->point->name,
                'latitude' => $catch->point->latitude,
                'longitude' => $catch->point->longitude,
            ] : null,
            'fish_species' => $catch->fishSpecies ? [
                'id' => $catch->fishSpecies->id,
                'name' => $catch->fishSpecies->name,
                'slug' => $catch->fishSpecies->slug,
            ] : null,
            'fishing_method' => $catch->fishingMethod ? [
                'id' => $catch->fishingMethod->id,
                'name' => $catch->fishingMethod->name,
                'slug' => $catch->fishingMethod->slug,
            ] : null,
            'fishing_location' => $catch->fishingLocation ? [
                'id' => $catch->fishingLocation->id,
                'name' => $catch->fishingLocation->name,
                'slug' => $catch->fishingLocation->slug,
            ] : null,
            'liked_by_me' => $user ? $catch->likes()->where('user_id', $user->id)->exists() : false,
            'created_at' => $catch->created_at,
            'updated_at' => $catch->updated_at,
        ];
    }
}

