import React, { useState, useEffect } from 'react';
import { languageService, Language } from '../services/languageService';

interface LanguageSwitcherProps {
  showFlags?: boolean;
  showNativeNames?: boolean;
  showEnglishNames?: boolean;
  className?: string;
  onLanguageChange?: (language: string) => void;
}

export const LanguageSwitcher: React.FC<LanguageSwitcherProps> = ({
  showFlags = true,
  showNativeNames = true,
  showEnglishNames = false,
  className = '',
  onLanguageChange,
}) => {
  const [languages, setLanguages] = useState<Language[]>([]);
  const [currentLanguage, setCurrentLanguage] = useState<string>('en');
  const [isOpen, setIsOpen] = useState(false);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    loadLanguages();
    loadCurrentLanguage();
  }, []);

  const loadLanguages = async () => {
    try {
      const availableLanguages = languageService.getAvailableLanguages();
      setLanguages(availableLanguages);
    } catch (error) {
      console.error('Failed to load languages:', error);
    }
  };

  const loadCurrentLanguage = async () => {
    try {
      const current = await languageService.getCurrentLanguage();
      setCurrentLanguage(current.code);
    } catch (error) {
      console.error('Failed to load current language:', error);
    }
  };

  const handleLanguageChange = async (languageCode: string) => {
    if (languageCode === currentLanguage) {
      setIsOpen(false);
      return;
    }

    setLoading(true);
    try {
      const success = await languageService.setLanguage(languageCode);
      if (success) {
        setCurrentLanguage(languageCode);
        setIsOpen(false);
        onLanguageChange?.(languageCode);
        
        // Reload the page to apply new language
        window.location.reload();
      } else {
        console.error('Failed to change language');
      }
    } catch (error) {
      console.error('Error changing language:', error);
    } finally {
      setLoading(false);
    }
  };

  const getCurrentLanguageInfo = (): Language | undefined => {
    return languages.find(lang => lang.code === currentLanguage);
  };

  const currentLangInfo = getCurrentLanguageInfo();

  return (
    <div className={`relative ${className}`}>
      <button
        onClick={() => setIsOpen(!isOpen)}
        disabled={loading}
        className="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {showFlags && currentLangInfo?.flag && (
          <span className="text-lg">{currentLangInfo.flag}</span>
        )}
        <span>
          {showNativeNames && currentLangInfo?.native_name
            ? currentLangInfo.native_name
            : showEnglishNames && currentLangInfo?.name
            ? currentLangInfo.name
            : currentLanguage.toUpperCase()}
        </span>
        <svg
          className={`w-4 h-4 transition-transform ${isOpen ? 'rotate-180' : ''}`}
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M19 9l-7 7-7-7"
          />
        </svg>
      </button>

      {isOpen && (
        <>
          {/* Backdrop */}
          <div
            className="fixed inset-0 z-10"
            onClick={() => setIsOpen(false)}
          />
          
          {/* Dropdown */}
          <div className="absolute right-0 z-20 mt-1 w-56 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
            {languages.map((language) => (
              <button
                key={language.code}
                onClick={() => handleLanguageChange(language.code)}
                disabled={loading}
                className={`w-full flex items-center space-x-3 px-4 py-3 text-left hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed ${
                  language.code === currentLanguage ? 'bg-blue-50 text-blue-700' : 'text-gray-700'
                }`}
              >
                {showFlags && language.flag && (
                  <span className="text-lg">{language.flag}</span>
                )}
                <div className="flex-1 min-w-0">
                  {showNativeNames && language.native_name && (
                    <div className="text-sm font-medium truncate">
                      {language.native_name}
                    </div>
                  )}
                  {showEnglishNames && language.name && (
                    <div className="text-xs text-gray-500 truncate">
                      {language.name}
                    </div>
                  )}
                  {!showNativeNames && !showEnglishNames && (
                    <div className="text-sm font-medium truncate">
                      {language.code.toUpperCase()}
                    </div>
                  )}
                </div>
                {language.code === currentLanguage && (
                  <svg
                    className="w-4 h-4 text-blue-600"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fillRule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                      clipRule="evenodd"
                    />
                  </svg>
                )}
              </button>
            ))}
          </div>
        </>
      )}
    </div>
  );
};

export default LanguageSwitcher;
