<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CatchRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class CatchController extends Controller
{
    /**
     * @OA\Get(
     *     path="/catch/{id}",
     *     tags={"Catches"},
     *     summary="Получить детали улова",
     *     description="Возвращает подробную информацию об улове по ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID улова",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное получение улова",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                     @OA\Property(property="username", type="string", example="ivan_ivanov"),
     *                     @OA\Property(property="photo_url", type="string", example="https://example.com/photo.jpg"),
     *                     @OA\Property(property="role", type="string", example="user"),
     *                     @OA\Property(property="is_premium", type="boolean", example=false),
     *                     @OA\Property(property="followers_count", type="integer", example=150)
     *                 ),
     *                 @OA\Property(property="species", type="string", example="Щука"),
     *                 @OA\Property(property="weight", type="number", format="float", example=2.5),
     *                 @OA\Property(property="length", type="number", format="float", example=45.0),
     *                 @OA\Property(property="style", type="string", example="Спиннинг"),
     *                 @OA\Property(property="lure", type="string", example="Воблер"),
     *                 @OA\Property(property="tackle", type="string", example="Плетенка 0.25"),
     *                 @OA\Property(property="notes", type="string", example="Отличный улов!"),
     *                 @OA\Property(property="photo_url", type="string", example="https://example.com/catch.jpg"),
     *                 @OA\Property(property="caught_at", type="string", format="date-time"),
     *                 @OA\Property(property="likes_count", type="integer", example=15),
     *                 @OA\Property(property="comments_count", type="integer", example=3),
     *                 @OA\Property(property="liked_by_me", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Улов не найден"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $catch = CatchRecord::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $catch->id,
                    'user' => [
                        'id' => $catch->user_id,
                        'name' => 'Пользователь',
                        'username' => '',
                        'photo_url' => '',
                        'role' => 'user',
                        'is_premium' => false,
                        'crown_icon_url' => null,
                        'followers_count' => 0,
                        'is_online' => false,
                        'last_seen_at' => null,
                    ],
                    'species' => $catch->species,
                    'weight' => $catch->weight,
                    'length' => $catch->length,
                    'style' => $catch->style,
                    'lure' => $catch->lure,
                    'tackle' => $catch->tackle,
                    'notes' => $catch->notes,
                    'photo_url' => $catch->photo_url,
                    'additional_photos' => $catch->additional_photos,
                    'caught_at' => $catch->caught_at,
                    // Поля погоды
                    'weather' => [
                        'temperature' => $catch->temperature,
                        'pressure' => $catch->pressure,
                        'wind_speed' => $catch->wind_speed,
                        'cloudiness' => $catch->cloudiness,
                        'precipitation' => $catch->precipitation,
                        'wind_direction' => $catch->wind_direction,
                    ],
                    'likes_count' => 0,
                    'comments_count' => 0,
                    'privacy' => $catch->privacy,
                    'point' => null,
                    'fish_species' => null,
                    'fishing_method' => null,
                    'fishing_location' => null,
                    'liked_by_me' => false,
                    'created_at' => $catch->created_at,
                    'updated_at' => $catch->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get catch comments.
     */
    public function comments($id): JsonResponse
    {
        $catch = CatchRecord::findOrFail($id);
        
        // Используем прямые запросы к базе данных
        $comments = \DB::table('catch_comments')
            ->join('users', 'catch_comments.user_id', '=', 'users.id')
            ->where('catch_comments.catch_id', $id)
            ->where('catch_comments.status', 'approved')
            ->orderBy('catch_comments.created_at', 'desc')
            ->select('catch_comments.*', 'users.name as user_name', 'users.handle as user_handle', 'users.avatar_path as user_avatar')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'body' => $comment->text,
                    'user' => [
                        'id' => $comment->user_id,
                        'name' => $comment->user_name,
                        'username' => $comment->user_handle ?? '',
                        'photo_url' => $comment->user_avatar ?? '',
                    ],
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                ];
            })
        ]);
    }

    /**
     * Add comment to catch.
     */
    public function addComment(Request $request, $id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $catch = CatchRecord::findOrFail($id);

        $request->validate([
            'body' => 'required|string|max:1000'
        ]);

        $comment = $catch->comments()->create([
            'user_id' => $user->id,
            'body' => $request->body,
            'is_approved' => true, // Auto-approve for now
            'moderation_status' => 'approved'
        ]);

        $comment->load('user');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $comment->id,
                'body' => $comment->body,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'username' => $comment->user->username,
                    'photo_url' => $comment->user->photo_url,
                ],
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
            ]
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/catch/{id}/like",
     *     tags={"Catches"},
     *     summary="Лайк/анлайк улова",
     *     description="Ставит или убирает лайк с улова",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID улова",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Лайк успешно поставлен/убран",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="liked", type="boolean", example=true),
     *             @OA\Property(property="likes_count", type="integer", example=15)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Улов не найден"
     *     )
     * )
     */
    public function like($id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $catch = CatchRecord::findOrFail($id);

        $existingLike = $catch->likes()->where('user_id', $user->id)->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            $catch->likes()->create(['user_id' => $user->id]);
            $liked = true;
        }

        $likesCount = $catch->likes()->count();

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $likesCount
        ]);
    }

    /**
     * Save base64 image to storage
     */
    private function saveBase64Image($base64String, $folder = 'catches')
    {
        try {
            // Check if it's a valid base64 image
            if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
                $data = substr($base64String, strpos($base64String, ',') + 1);
                $data = base64_decode($data);
                
                if ($data === false) {
                    return null;
                }
                
                $extension = $type[1];
                $filename = uniqid() . '.' . $extension;
                $path = $folder . '/' . $filename;
                
                // Save to storage
                \Storage::disk('public')->put($path, $data);
                
                return \Storage::disk('public')->url($path);
            }
            
            return null;
        } catch (\Exception $e) {
            \Log::error('Error saving base64 image: ' . $e->getMessage());
            return null;
        }
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
                'username' => $catch->user->handle ?? '',
                'photo_url' => $catch->user->avatar_path ?? '',
                'role' => 'user',
                'is_premium' => false,
                'crown_icon_url' => null,
                'followers_count' => 0,
                'is_online' => false,
                'last_seen_at' => null,
            ],
            'species' => $catch->species,
            'weight' => $catch->weight,
            'length' => $catch->length,
            'style' => $catch->style,
            'lure' => $catch->lure,
            'tackle' => $catch->tackle,
            'notes' => $catch->notes,
            'photo_url' => $catch->photo_url,
            'additional_photos' => $catch->additional_photos,
            'caught_at' => $catch->caught_at,
            'likes_count' => $catch->likes()->count(),
            'comments_count' => $catch->comments()->count(),
            'privacy' => $catch->privacy,
            'point' => null,
            'fish_species' => null,
            'fishing_method' => null,
            'fishing_location' => null,
            'liked_by_me' => false,
            'created_at' => $catch->created_at,
            'updated_at' => $catch->updated_at,
        ];
    }

    /**
     * Update a catch.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::guard('api')->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $catch = CatchRecord::findOrFail($id);
            
            // Check if user owns this catch
            if ($catch->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only edit your own catches'
                ], 403);
            }

            $validated = $request->validate([
                'lat' => 'nullable|numeric|between:-90,90',
                'lng' => 'nullable|numeric|between:-180,180',
                'species' => 'nullable|string|max:120',
                'length' => 'nullable|numeric|min:0',
                'weight' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
                'style' => 'nullable|string|max:120',
                'lure' => 'nullable|string|max:120',
                'tackle' => 'nullable|string|max:120',
                'caught_at' => 'nullable|date',
                'privacy' => 'nullable|string|in:all,friends,me',
            ]);

            // Update only provided fields
            foreach ($validated as $field => $value) {
                if ($value !== null) {
                    $catch->$field = $value;
                }
            }
            
            $catch->save();

            return response()->json([
                'success' => true,
                'message' => 'Catch updated successfully',
                'data' => $this->transformCatch($catch, $user)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update catch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a catch.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::guard('api')->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $catch = CatchRecord::findOrFail($id);
            
            // Check if user owns this catch
            if ($catch->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own catches'
                ], 403);
            }

            $catch->delete();

            return response()->json([
                'success' => true,
                'message' => 'Catch deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete catch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/catch",
     *     tags={"Catches"},
     *     summary="Создать новый улов",
     *     description="Создает новый улов для авторизованного пользователя",
     *     security={{"jwt": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"lat", "lng"},
     *             @OA\Property(property="lat", type="number", format="float", example=55.7558, description="Широта"),
     *             @OA\Property(property="lng", type="number", format="float", example=37.6176, description="Долгота"),
     *             @OA\Property(property="species", type="string", example="Щука", description="Вид рыбы"),
     *             @OA\Property(property="weight", type="number", format="float", example=2.5, description="Вес в кг"),
     *             @OA\Property(property="length", type="number", format="float", example=45.0, description="Длина в см"),
     *             @OA\Property(property="style", type="string", example="Спиннинг", description="Способ ловли"),
     *             @OA\Property(property="lure", type="string", example="Воблер", description="Приманка"),
     *             @OA\Property(property="tackle", type="string", example="Плетенка 0.25", description="Снасть"),
     *             @OA\Property(property="notes", type="string", example="Отличный улов!", description="Заметки"),
     *             @OA\Property(property="caught_at", type="string", format="date-time", description="Время поимки"),
     *             @OA\Property(property="privacy", type="string", enum={"all", "friends", "private"}, example="all", description="Приватность")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Улов успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Catch created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="species", type="string", example="Щука"),
     *                 @OA\Property(property="weight", type="number", format="float", example=2.5),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Требуется аутентификация"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $validated = $request->validate([
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
                'species' => 'nullable|string|max:120',
                'length' => 'nullable|numeric|min:0',
                'weight' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
                'images' => 'nullable|array|max:10',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
                'photo_url' => 'nullable|string',
                'additional_photos' => 'nullable|string',
                'style' => 'nullable|string|max:120',
                'lure' => 'nullable|string|max:120',
                'tackle' => 'nullable|string|max:120',
                'caught_at' => 'nullable|date',
                'privacy' => 'string|in:all,friends,me',
                'track_id' => 'nullable|integer|exists:tracks,id',
                // Поля погоды
                'temperature' => 'nullable|numeric|between:-50,50',
                'pressure' => 'nullable|numeric|between:700,800',
                'wind_speed' => 'nullable|numeric|min:0|max:50',
                'cloudiness' => 'nullable|string|in:ясно,малооблачно,облачно,пасмурно',
                'precipitation' => 'nullable|string|in:без осадков,дождь,снег,град,туман',
                'wind_direction' => 'nullable|string|in:С,СВ,В,ЮВ,Ю,ЮЗ,З,СЗ',
            ]);

            $catch = new CatchRecord();
            $catch->user_id = $user->id;
            $catch->lat = $validated['lat'];
            $catch->lng = $validated['lng'];
            $catch->species = $validated['species'] ?? null;
            $catch->length = $validated['length'] ?? null;
            $catch->weight = $validated['weight'] ?? null;
            $catch->notes = $validated['notes'] ?? null;
            $catch->style = $validated['style'] ?? null;
            $catch->lure = $validated['lure'] ?? null;
            $catch->tackle = $validated['tackle'] ?? null;
            $catch->caught_at = $validated['caught_at'] ?? now();
            $catch->privacy = $validated['privacy'] ?? 'all';
            $catch->track_id = $validated['track_id'] ?? null;
            
            // Поля погоды
            $catch->temperature = $validated['temperature'] ?? null;
            $catch->pressure = $validated['pressure'] ?? null;
            $catch->wind_speed = $validated['wind_speed'] ?? null;
            $catch->cloudiness = $validated['cloudiness'] ?? null;
            $catch->precipitation = $validated['precipitation'] ?? null;
            $catch->wind_direction = $validated['wind_direction'] ?? null;
            
            // Handle base64 images
            if (!empty($validated['photo_url'])) {
                $photoUrl = $this->saveBase64Image($validated['photo_url'], 'catches');
                if ($photoUrl) {
                    $catch->photo_url = $photoUrl;
                }
            }
            
            if (!empty($validated['additional_photos'])) {
                $additionalPhotos = json_decode($validated['additional_photos'], true);
                if (is_array($additionalPhotos)) {
                    $savedPhotos = [];
                    foreach ($additionalPhotos as $photo) {
                        $savedUrl = $this->saveBase64Image($photo, 'catches');
                        if ($savedUrl) {
                            $savedPhotos[] = $savedUrl;
                        }
                    }
                    $catch->additional_photos = json_encode($savedPhotos);
                }
            }
            
            $catch->save();

            // Handle file uploads if provided
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('catches', 'public');
                    $catch->images()->create([
                        'url' => $path,
                        'alt_text' => $catch->species ?? 'Catch photo'
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Catch created successfully',
                'data' => $this->transformCatch($catch, $user)
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create catch',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}