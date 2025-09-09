import { request } from '../utils/http';

// Create http object with methods
const http = {
  get: (path: string, params?: Record<string, any>) => request(path, { method: 'GET', params }),
  post: (path: string, data?: any) => request(path, { method: 'POST', data }),
  put: (path: string, data?: any) => request(path, { method: 'PUT', data }),
  delete: (path: string) => request(path, { method: 'DELETE' }),
};

export interface Language {
  code: string;
  name: string;
  native_name: string;
  flag: string;
  rtl: boolean;
  enabled: boolean;
}

export interface LanguageConfig {
  default_language: string;
  fallback_language: string;
  auto_detect: boolean;
  detection_sources: string[];
  storage: {
    user_preference: boolean;
    session: boolean;
    cookie: boolean;
  };
  rtl_support: boolean;
  switcher: {
    show_flags: boolean;
    show_native_names: boolean;
    show_english_names: boolean;
    group_by_region: boolean;
  };
  translation: {
    auto_generate: boolean;
    fallback_to_key: boolean;
    cache_translations: boolean;
    cache_ttl: number;
  };
}

export interface LanguageStatistics {
  total_users: number;
  languages: Array<{
    code: string;
    name: string;
    native_name: string;
    flag: string;
    users_count: number;
    percentage: number;
  }>;
  default_language: string;
  current_language: string;
}

export interface BrowserLanguageInfo {
  detected: string | null;
  supported: boolean;
  raw_header: string | null;
  parsed_languages: Record<string, number>;
}

class LanguageService {
  private currentLanguage: string = 'en';
  private translations: Record<string, any> = {};
  private isRtl: boolean = false;

  /**
   * Initialize language service
   */
  async initialize(): Promise<void> {
    try {
      // Get current language from API
      const response = await http.get('/api/v1/languages/current');
      if (response.success) {
        this.currentLanguage = response.data.code;
        this.isRtl = response.data.is_rtl;
        this.updateDocumentDirection();
      }

      // Load translations for current language
      await this.loadTranslations(this.currentLanguage);
    } catch (error) {
      console.error('Failed to initialize language service:', error);
      // Fallback to default language
      this.currentLanguage = 'en';
      this.isRtl = false;
    }
  }

  /**
   * Get all supported languages
   */
  async getSupportedLanguages(): Promise<Language[]> {
    try {
      const response = await http.get('/api/v1/languages');
      return response.data.languages;
    } catch (error) {
      console.error('Failed to get supported languages:', error);
      return [];
    }
  }

  /**
   * Get languages grouped by regions
   */
  async getLanguagesByRegion(): Promise<Record<string, Language[]>> {
    try {
      const response = await http.get('/api/v1/languages/by-region');
      return response.data.languages_by_region;
    } catch (error) {
      console.error('Failed to get languages by region:', error);
      return {};
    }
  }

  /**
   * Get language switcher data
   */
  async getLanguageSwitcherData(): Promise<Language[]> {
    try {
      const response = await http.get('/api/v1/languages/switcher');
      return response.data;
    } catch (error) {
      console.error('Failed to get language switcher data:', error);
      return [];
    }
  }

  /**
   * Get current language info
   */
  async getCurrentLanguage(): Promise<{ code: string; config: Language; is_rtl: boolean }> {
    try {
      const response = await http.get('/api/v1/languages/current');
      return response.data;
    } catch (error) {
      console.error('Failed to get current language:', error);
      return {
        code: 'en',
        config: {
          code: 'en',
          name: 'English',
          native_name: 'English',
          flag: '🇺🇸',
          rtl: false,
          enabled: true,
        },
        is_rtl: false,
      };
    }
  }

  /**
   * Set language
   */
  async setLanguage(languageCode: string): Promise<boolean> {
    try {
      const response = await http.post('/api/v1/languages/set', {
        language: languageCode,
      });

      if (response.success) {
        this.currentLanguage = languageCode;
        this.isRtl = response.data.is_rtl;
        this.updateDocumentDirection();
        await this.loadTranslations(languageCode);
        return true;
      }
      return false;
    } catch (error) {
      console.error('Failed to set language:', error);
      return false;
    }
  }

  /**
   * Get user's language preference
   */
  async getUserLanguagePreference(): Promise<{ language: string | null; config: Language | null; is_rtl: boolean }> {
    try {
      const response = await http.get('/api/v1/languages/user-preference');
      return response.data;
    } catch (error) {
      console.error('Failed to get user language preference:', error);
      return {
        language: null,
        config: null,
        is_rtl: false,
      };
    }
  }

  /**
   * Get browser language detection info
   */
  async getBrowserLanguageInfo(): Promise<BrowserLanguageInfo> {
    try {
      const response = await http.get('/api/v1/languages/detect');
      return response.data;
    } catch (error) {
      console.error('Failed to get browser language info:', error);
      return {
        detected: null,
        supported: false,
        raw_header: null,
        parsed_languages: {},
      };
    }
  }

  /**
   * Get RTL languages
   */
  async getRtlLanguages(): Promise<{ rtl_languages: Language[]; current_is_rtl: boolean }> {
    try {
      const response = await http.get('/api/v1/languages/rtl');
      return response.data;
    } catch (error) {
      console.error('Failed to get RTL languages:', error);
      return {
        rtl_languages: [],
        current_is_rtl: false,
      };
    }
  }

  /**
   * Get language configuration
   */
  async getLanguageConfig(): Promise<LanguageConfig> {
    try {
      const response = await http.get('/api/v1/languages/config');
      return response.data;
    } catch (error) {
      console.error('Failed to get language config:', error);
      return {
        default_language: 'en',
        fallback_language: 'en',
        auto_detect: true,
        detection_sources: [],
        storage: {
          user_preference: true,
          session: true,
          cookie: true,
        },
        rtl_support: true,
        switcher: {
          show_flags: true,
          show_native_names: true,
          show_english_names: false,
          group_by_region: false,
        },
        translation: {
          auto_generate: false,
          fallback_to_key: true,
          cache_translations: true,
          cache_ttl: 3600,
        },
      };
    }
  }

  /**
   * Get language statistics (admin only)
   */
  async getLanguageStatistics(): Promise<LanguageStatistics> {
    try {
      const response = await http.get('/api/admin/languages/statistics');
      return response.data;
    } catch (error) {
      console.error('Failed to get language statistics:', error);
      return {
        total_users: 0,
        languages: [],
        default_language: 'en',
        current_language: 'en',
      };
    }
  }

  /**
   * Clear language cache (admin only)
   */
  async clearLanguageCache(): Promise<boolean> {
    try {
      const response = await http.post('/api/admin/languages/clear-cache');
      return response.success;
    } catch (error) {
      console.error('Failed to clear language cache:', error);
      return false;
    }
  }

  /**
   * Load translations for a specific language
   */
  private async loadTranslations(languageCode: string): Promise<void> {
    try {
      // In a real application, you would load translations from the backend
      // For now, we'll use a simple approach with static translations
      const translations = await import(`../locales/${languageCode}/app.json`);
      this.translations = translations.default;
    } catch (error) {
      console.error(`Failed to load translations for ${languageCode}:`, error);
      // Fallback to English
      if (languageCode !== 'en') {
        try {
          const fallbackTranslations = await import('../locales/en/app.json');
          this.translations = fallbackTranslations.default;
        } catch (fallbackError) {
          console.error('Failed to load fallback translations:', fallbackError);
          this.translations = {};
        }
      }
    }
  }

  /**
   * Update document direction based on RTL
   */
  private updateDocumentDirection(): void {
    const html = document.documentElement;
    if (this.isRtl) {
      html.setAttribute('dir', 'rtl');
      html.setAttribute('lang', this.currentLanguage);
    } else {
      html.setAttribute('dir', 'ltr');
      html.setAttribute('lang', this.currentLanguage);
    }
  }

  /**
   * Get translation for a key
   */
  t(key: string, params?: Record<string, any>): string {
    const keys = key.split('.');
    let value: any = this.translations;

    for (const k of keys) {
      if (value && typeof value === 'object' && k in value) {
        value = value[k];
      } else {
        // Fallback to key if translation not found
        return key;
      }
    }

    if (typeof value !== 'string') {
      return key;
    }

    // Replace parameters
    if (params) {
      return value.replace(/:(\w+)/g, (match, param) => {
        return params[param] || match;
      });
    }

    return value;
  }

  /**
   * Get current language code
   */
  getCurrentLanguageCode(): string {
    return this.currentLanguage;
  }

  /**
   * Check if current language is RTL
   */
  isCurrentLanguageRtl(): boolean {
    return this.isRtl;
  }

  /**
   * Get available languages for language switcher
   */
  getAvailableLanguages(): Language[] {
    // This would typically come from the API
    return [
      { code: 'en', name: 'English', native_name: 'English', flag: '🇺🇸', rtl: false, enabled: true },
      { code: 'ru', name: 'Russian', native_name: 'Русский', flag: '🇷🇺', rtl: false, enabled: true },
      { code: 'zh', name: 'Chinese', native_name: '中文', flag: '🇨🇳', rtl: false, enabled: true },
      { code: 'es', name: 'Spanish', native_name: 'Español', flag: '🇪🇸', rtl: false, enabled: true },
      { code: 'fr', name: 'French', native_name: 'Français', flag: '🇫🇷', rtl: false, enabled: true },
      { code: 'de', name: 'German', native_name: 'Deutsch', flag: '🇩🇪', rtl: false, enabled: true },
      { code: 'ja', name: 'Japanese', native_name: '日本語', flag: '🇯🇵', rtl: false, enabled: true },
      { code: 'ko', name: 'Korean', native_name: '한국어', flag: '🇰🇷', rtl: false, enabled: true },
      { code: 'ar', name: 'Arabic', native_name: 'العربية', flag: '🇸🇦', rtl: true, enabled: true },
      { code: 'hi', name: 'Hindi', native_name: 'हिन्दी', flag: '🇮🇳', rtl: false, enabled: true },
      { code: 'pt', name: 'Portuguese', native_name: 'Português', flag: '🇵🇹', rtl: false, enabled: true },
      { code: 'bn', name: 'Bengali', native_name: 'বাংলা', flag: '🇧🇩', rtl: false, enabled: true },
      { code: 'tr', name: 'Turkish', native_name: 'Türkçe', flag: '🇹🇷', rtl: false, enabled: true },
      { code: 'vi', name: 'Vietnamese', native_name: 'Tiếng Việt', flag: '🇻🇳', rtl: false, enabled: true },
      { code: 'it', name: 'Italian', native_name: 'Italiano', flag: '🇮🇹', rtl: false, enabled: true },
    ];
  }
}

export const languageService = new LanguageService();
