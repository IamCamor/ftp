<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LanguageService
{
    /**
     * Get all supported languages
     */
    public function getSupportedLanguages(): array
    {
        return Cache::remember('supported_languages', 3600, function () {
            $languages = config('languages.supported', []);
            
            return array_filter($languages, function ($config) {
                return $config['enabled'] ?? true;
            });
        });
    }

    /**
     * Get language configuration
     */
    public function getLanguageConfig(string $code): ?array
    {
        $languages = $this->getSupportedLanguages();
        return $languages[$code] ?? null;
    }

    /**
     * Check if language is supported
     */
    public function isLanguageSupported(string $code): bool
    {
        return array_key_exists($code, $this->getSupportedLanguages());
    }

    /**
     * Get current language
     */
    public function getCurrentLanguage(): string
    {
        return App::getLocale();
    }

    /**
     * Set language for current user
     */
    public function setLanguage(string $code): bool
    {
        if (!$this->isLanguageSupported($code)) {
            return false;
        }

        // Set application locale
        App::setLocale($code);

        // Store in session
        if (config('languages.storage.session', true)) {
            Session::put('locale', $code);
        }

        // Store in cookie
        if (config('languages.storage.cookie', true)) {
            cookie()->queue('locale', $code, 60 * 24 * 30); // 30 days
        }

        // Store in user profile if authenticated
        if (Auth::check() && config('languages.storage.user_preference', true)) {
            $user = Auth::user();
            $user->update(['language' => $code]);
        }

        return true;
    }

    /**
     * Get user's preferred language
     */
    public function getUserLanguage(): ?string
    {
        if (!Auth::check()) {
            return null;
        }

        return Auth::user()->language;
    }

    /**
     * Get languages grouped by regions
     */
    public function getLanguagesByRegion(): array
    {
        $languages = $this->getSupportedLanguages();
        $regions = config('languages.regions', []);
        $grouped = [];

        foreach ($regions as $region => $codes) {
            $grouped[$region] = array_filter($languages, function ($code) use ($codes) {
                return in_array($code, $codes);
            }, ARRAY_FILTER_USE_KEY);
        }

        // Add ungrouped languages
        $groupedCodes = array_merge(...array_values($regions));
        $ungrouped = array_filter($languages, function ($code) use ($groupedCodes) {
            return !in_array($code, $groupedCodes);
        }, ARRAY_FILTER_USE_KEY);

        if (!empty($ungrouped)) {
            $grouped['other'] = $ungrouped;
        }

        return $grouped;
    }

    /**
     * Get RTL languages
     */
    public function getRtlLanguages(): array
    {
        $languages = $this->getSupportedLanguages();
        
        return array_filter($languages, function ($config) {
            return $config['rtl'] ?? false;
        });
    }

    /**
     * Check if current language is RTL
     */
    public function isCurrentLanguageRtl(): bool
    {
        $currentLang = $this->getCurrentLanguage();
        $config = $this->getLanguageConfig($currentLang);
        
        return $config['rtl'] ?? false;
    }

    /**
     * Get language statistics
     */
    public function getLanguageStatistics(): array
    {
        $totalUsers = \App\Models\User::count();
        $languageStats = \App\Models\User::selectRaw('language, COUNT(*) as count')
            ->whereNotNull('language')
            ->groupBy('language')
            ->orderByDesc('count')
            ->get()
            ->pluck('count', 'language')
            ->toArray();

        $supportedLanguages = $this->getSupportedLanguages();
        $stats = [];

        foreach ($supportedLanguages as $code => $config) {
            $count = $languageStats[$code] ?? 0;
            $percentage = $totalUsers > 0 ? round(($count / $totalUsers) * 100, 2) : 0;
            
            $stats[] = [
                'code' => $code,
                'name' => $config['name'],
                'native_name' => $config['native_name'],
                'flag' => $config['flag'],
                'users_count' => $count,
                'percentage' => $percentage,
            ];
        }

        // Sort by user count
        usort($stats, function ($a, $b) {
            return $b['users_count'] <=> $a['users_count'];
        });

        return [
            'total_users' => $totalUsers,
            'languages' => $stats,
            'default_language' => config('languages.default', 'en'),
            'current_language' => $this->getCurrentLanguage(),
        ];
    }

    /**
     * Get browser language detection info
     */
    public function getBrowserLanguageInfo(Request $request): array
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return [
                'detected' => null,
                'supported' => false,
                'raw_header' => null,
            ];
        }

        $languages = $this->parseAcceptLanguage($acceptLanguage);
        $supportedLanguages = array_keys($this->getSupportedLanguages());
        $mapping = config('languages.browser_mapping', []);

        $detected = null;
        $supported = false;

        foreach ($languages as $lang => $quality) {
            // Try direct match
            if (in_array($lang, $supportedLanguages)) {
                $detected = $lang;
                $supported = true;
                break;
            }

            // Try mapped version
            $mappedLang = $mapping[$lang] ?? null;
            if ($mappedLang && in_array($mappedLang, $supportedLanguages)) {
                $detected = $mappedLang;
                $supported = true;
                break;
            }

            // Try language code without region
            $baseLang = explode('-', $lang)[0];
            if (in_array($baseLang, $supportedLanguages)) {
                $detected = $baseLang;
                $supported = true;
                break;
            }
        }

        return [
            'detected' => $detected,
            'supported' => $supported,
            'raw_header' => $acceptLanguage,
            'parsed_languages' => $languages,
        ];
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

            if (str_contains($part, ';q=')) {
                [$lang, $q] = explode(';q=', $part, 2);
                $quality = (float) $q;
                $lang = trim($lang);
            }

            $languages[$lang] = $quality;
        }

        arsort($languages);
        return $languages;
    }

    /**
     * Clear language cache
     */
    public function clearCache(): void
    {
        Cache::forget('supported_languages');
    }

    /**
     * Get language switcher data
     */
    public function getLanguageSwitcherData(): array
    {
        $currentLang = $this->getCurrentLanguage();
        $languages = $this->getSupportedLanguages();
        $switcherConfig = config('languages.switcher', []);

        $data = [];
        foreach ($languages as $code => $config) {
            $data[] = [
                'code' => $code,
                'name' => $config['name'],
                'native_name' => $config['native_name'],
                'flag' => $switcherConfig['show_flags'] ? $config['flag'] : null,
                'is_current' => $code === $currentLang,
                'is_rtl' => $config['rtl'] ?? false,
            ];
        }

        return $data;
    }
}
