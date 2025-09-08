<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LandingPageController extends Controller
{
    /**
     * Get all published landing pages.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'featured' => 'nullable|boolean',
            'search' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = LandingPage::published();

        if ($request->boolean('featured')) {
            $query->featured();
        }

        if ($request->search) {
            $query->search($request->search);
        }

        $pages = $query->orderBy('is_featured', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $pages
        ]);
    }

    /**
     * Get landing page by slug.
     */
    public function show(LandingPage $landingPage): JsonResponse
    {
        if (!$landingPage->isPublished()) {
            return response()->json([
                'success' => false,
                'message' => 'Страница не найдена'
            ], 404);
        }

        $landingPage->incrementViewCount();

        return response()->json([
            'success' => true,
            'data' => $landingPage
        ]);
    }

    /**
     * Get featured landing pages.
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        
        $pages = LandingPage::published()
            ->featured()
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pages
        ]);
    }

    /**
     * Create new landing page (admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:landing_pages,slug',
            'description' => 'nullable|string|max:1000',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'featured_image' => 'nullable|string|max:500',
            'gallery' => 'nullable|array',
            'template' => 'nullable|string|max:100',
            'custom_fields' => 'nullable|array',
            'status' => 'nullable|string|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $landingPage = LandingPage::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'content' => $request->content,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'featured_image' => $request->featured_image,
            'gallery' => $request->gallery,
            'template' => $request->template ?? 'default',
            'custom_fields' => $request->custom_fields,
            'status' => $request->status ?? 'draft',
            'is_featured' => $request->boolean('is_featured'),
            'published_at' => $request->published_at,
            'author_id' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Страница создана успешно',
            'data' => $landingPage
        ], 201);
    }

    /**
     * Update landing page (admin only).
     */
    public function update(Request $request, LandingPage $landingPage): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:landing_pages,slug,' . $landingPage->id,
            'description' => 'nullable|string|max:1000',
            'content' => 'sometimes|required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'featured_image' => 'nullable|string|max:500',
            'gallery' => 'nullable|array',
            'template' => 'nullable|string|max:100',
            'custom_fields' => 'nullable|array',
            'status' => 'nullable|string|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $landingPage->update($request->only([
            'title', 'slug', 'description', 'content', 'meta_title',
            'meta_description', 'meta_keywords', 'featured_image',
            'gallery', 'template', 'custom_fields', 'status',
            'is_featured', 'published_at'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Страница обновлена успешно',
            'data' => $landingPage
        ]);
    }

    /**
     * Delete landing page (admin only).
     */
    public function destroy(LandingPage $landingPage): JsonResponse
    {
        $landingPage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Страница удалена успешно'
        ]);
    }

    /**
     * Search landing pages.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $request->query;
        $limit = $request->get('limit', 20);

        $pages = LandingPage::published()
            ->search($query)
            ->orderBy('is_featured', 'desc')
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pages
        ]);
    }
}
