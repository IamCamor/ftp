<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DetectLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $language = $this->detectLanguage($request);
        
        // Set the application locale
        App::setLocale($language);
        
        // Store in session for future requests
        Session::put('locale', $language);
        
        // Store in cookie for persistence
        if (config('languages.storage.cookie', true)) {
            cookie()->queue('locale', $language, 60 * 24 * 30); // 30 days
        }
        
        return $next($request);
    }

    /**
     * Detect the appropriate language for the request
     */
    private function detectLanguage(Request $request): string
    {
        $sources = config('languages.detection_sources', []);
        $supportedLanguages = array_keys(config('languages.supported', []));
        $defaultLanguage = config('languages.default', 'en');

        foreach ($sources as $source) {
            $language = match ($source) {
                'user_preference' => $this->getUserPreferenceLanguage(),
                'url_parameter' => $this->getUrlParameterLanguage($request),
                'session' => $this->getSessionLanguage(),
                'browser_header' => $this->getBrowserLanguage($request),
                'default' => $defaultLanguage,
                default => null,
            };

            if ($language && in_array($language, $supportedLanguages)) {
                return $language;
            }
        }

        return $defaultLanguage;
    }

    /**
     * Get language from user's saved preference
     */
    private function getUserPreferenceLanguage(): ?string
    {
        if (!Auth::check() || !config('languages.storage.user_preference', true)) {
            return null;
        }

        $user = Auth::user();
        return $user->language ?? null;
    }

    /**
     * Get language from URL parameter
     */
    private function getUrlParameterLanguage(Request $request): ?string
    {
        $lang = $request->query('lang');
        
        if (!$lang) {
            return null;
        }

        // Map browser language codes to application codes
        $mapping = config('languages.browser_mapping', []);
        return $mapping[$lang] ?? $lang;
    }

    /**
     * Get language from session
     */
    private function getSessionLanguage(): ?string
    {
        if (!config('languages.storage.session', true)) {
            return null;
        }

        return Session::get('locale');
    }

    /**
     * Get language from browser Accept-Language header
     */
    private function getBrowserLanguage(Request $request): ?string
    {
        if (!config('languages.auto_detect', true)) {
            return null;
        }

        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header
        $languages = $this->parseAcceptLanguage($acceptLanguage);
        $mapping = config('languages.browser_mapping', []);
        $supportedLanguages = array_keys(config('languages.supported', []));

        foreach ($languages as $lang => $quality) {
            // Try direct match
            if (in_array($lang, $supportedLanguages)) {
                return $lang;
            }

            // Try mapped version
            $mappedLang = $mapping[$lang] ?? null;
            if ($mappedLang && in_array($mappedLang, $supportedLanguages)) {
                return $mappedLang;
            }

            // Try language code without region (e.g., 'en' from 'en-US')
            $baseLang = explode('-', $lang)[0];
            if (in_array($baseLang, $supportedLanguages)) {
                return $baseLang;
            }
        }

        return null;
    }

    /**
     * Parse Accept-Language header
     */
    private function parseAcceptLanguage(string $acceptLanguage): array
    {
        $languages = [];
        $parts = explode(',', $acceptLanguage);

        foreach ($parts as $part) {
            $part = trim($part);
            $quality = 1.0;

            // Check for quality value
            if (str_contains($part, ';q=')) {
                [$lang, $q] = explode(';q=', $part, 2);
                $quality = (float) $q;
                $lang = trim($lang);
            }

            $languages[$lang] = $quality;
        }

        // Sort by quality (highest first)
        arsort($languages);

        return $languages;
    }
}