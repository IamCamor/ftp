<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LanguageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    private LanguageService $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * Get all supported languages
     */
    public function index(): JsonResponse
    {
        $languages = $this->languageService->getSupportedLanguages();
        
        return response()->json([
            'success' => true,
            'data' => [
                'languages' => $languages,
                'current_language' => $this->languageService->getCurrentLanguage(),
                'default_language' => config('languages.default', 'en'),
            ]
        ]);
    }

    /**
     * Get languages grouped by regions
     */
    public function byRegion(): JsonResponse
    {
        $languages = $this->languageService->getLanguagesByRegion();
        
        return response()->json([
            'success' => true,
            'data' => [
                'languages_by_region' => $languages,
                'current_language' => $this->languageService->getCurrentLanguage(),
            ]
        ]);
    }

    /**
     * Get language switcher data
     */
    public function switcher(): JsonResponse
    {
        $data = $this->languageService->getLanguageSwitcherData();
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get current language info
     */
    public function current(): JsonResponse
    {
        $currentLang = $this->languageService->getCurrentLanguage();
        $config = $this->languageService->getLanguageConfig($currentLang);
        
        return response()->json([
            'success' => true,
            'data' => [
                'code' => $currentLang,
                'config' => $config,
                'is_rtl' => $this->languageService->isCurrentLanguageRtl(),
            ]
        ]);
    }

    /**
     * Set language
     */
    public function set(Request $request): JsonResponse
    {
        $request->validate([
            'language' => 'required|string|max:5',
        ]);

        $language = $request->input('language');
        
        if (!$this->languageService->isLanguageSupported($language)) {
            return response()->json([
                'success' => false,
                'message' => 'Language not supported'
            ], 400);
        }

        $success = $this->languageService->setLanguage($language);
        
        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set language'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Language updated successfully',
            'data' => [
                'language' => $language,
                'config' => $this->languageService->getLanguageConfig($language),
                'is_rtl' => $this->languageService->isCurrentLanguageRtl(),
            ]
        ]);
    }

    /**
     * Get user's language preference
     */
    public function userPreference(): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $userLanguage = $this->languageService->getUserLanguage();
        $config = $userLanguage ? $this->languageService->getLanguageConfig($userLanguage) : null;
        
        return response()->json([
            'success' => true,
            'data' => [
                'language' => $userLanguage,
                'config' => $config,
                'is_rtl' => $config ? ($config['rtl'] ?? false) : false,
            ]
        ]);
    }

    /**
     * Get browser language detection info
     */
    public function detect(Request $request): JsonResponse
    {
        $info = $this->languageService->getBrowserLanguageInfo($request);
        
        return response()->json([
            'success' => true,
            'data' => $info
        ]);
    }

    /**
     * Get RTL languages
     */
    public function rtl(): JsonResponse
    {
        $rtlLanguages = $this->languageService->getRtlLanguages();
        
        return response()->json([
            'success' => true,
            'data' => [
                'rtl_languages' => $rtlLanguages,
                'current_is_rtl' => $this->languageService->isCurrentLanguageRtl(),
            ]
        ]);
    }

    /**
     * Get language statistics (admin only)
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('admin');
        
        $stats = $this->languageService->getLanguageStatistics();
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get language configuration
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'default_language' => config('languages.default', 'en'),
                'fallback_language' => config('languages.fallback', 'en'),
                'auto_detect' => config('languages.auto_detect', true),
                'detection_sources' => config('languages.detection_sources', []),
                'storage' => config('languages.storage', []),
                'rtl_support' => config('languages.rtl_support', true),
                'switcher' => config('languages.switcher', []),
                'translation' => config('languages.translation', []),
            ]
        ]);
    }

    /**
     * Clear language cache (admin only)
     */
    public function clearCache(): JsonResponse
    {
        $this->authorize('admin');
        
        $this->languageService->clearCache();
        
        return response()->json([
            'success' => true,
            'message' => 'Language cache cleared successfully'
        ]);
    }
}