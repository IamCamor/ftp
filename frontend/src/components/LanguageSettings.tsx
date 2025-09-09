import React, { useState, useEffect } from 'react';
import { languageService, Language } from '../services/languageService';
import { useTranslation } from '../hooks/useTranslation';

interface LanguageSettingsProps {
  onLanguageChange?: (language: string) => void;
}

export const LanguageSettings: React.FC<LanguageSettingsProps> = ({
  onLanguageChange,
}) => {
  const { t, currentLanguage, isRtl, changeLanguage } = useTranslation();
  const [languages, setLanguages] = useState<Language[]>([]);
  const [selectedLanguage, setSelectedLanguage] = useState<string>(currentLanguage);
  // const [loading, setLoading] = useState(false); // Removed unused variable
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    loadLanguages();
  }, []);

  useEffect(() => {
    setSelectedLanguage(currentLanguage);
  }, [currentLanguage]);

  const loadLanguages = async () => {
    try {
      const availableLanguages = languageService.getAvailableLanguages();
      setLanguages(availableLanguages);
    } catch (error) {
      console.error('Failed to load languages:', error);
    }
  };

  const handleLanguageChange = async (languageCode: string) => {
    setSelectedLanguage(languageCode);
  };

  const handleSave = async () => {
    if (selectedLanguage === currentLanguage) {
      return;
    }

    setSaving(true);
    try {
      const success = await changeLanguage(selectedLanguage);
      if (success) {
        onLanguageChange?.(selectedLanguage);
        // Show success message
        console.log('Language changed successfully');
      } else {
        console.error('Failed to change language');
        // Reset selection
        setSelectedLanguage(currentLanguage);
      }
    } catch (error) {
      console.error('Error changing language:', error);
      setSelectedLanguage(currentLanguage);
    } finally {
      setSaving(false);
    }
  };

  // const getCurrentLanguageInfo = (code: string): Language | undefined => {
  //   return languages.find(lang => lang.code === code);
  // }; // Removed unused function

  return (
    <div className="space-y-6">
      <div>
        <h3 className="text-lg font-medium text-gray-900">
          {t('language.title')}
        </h3>
        <p className="mt-1 text-sm text-gray-500">
          {t('language.select_language')}
        </p>
      </div>

      <div className="space-y-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            {t('language.current_language')}
          </label>
          <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            {languages.map((language) => (
              <label
                key={language.code}
                className={`relative flex items-center p-4 border rounded-lg cursor-pointer transition-colors ${
                  selectedLanguage === language.code
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-300 hover:border-gray-400'
                }`}
              >
                <input
                  type="radio"
                  name="language"
                  value={language.code}
                  checked={selectedLanguage === language.code}
                  onChange={() => handleLanguageChange(language.code)}
                  className="sr-only"
                />
                <div className="flex items-center space-x-3">
                  <span className="text-2xl">{language.flag}</span>
                  <div className="flex-1 min-w-0">
                    <div className="text-sm font-medium text-gray-900">
                      {language.native_name}
                    </div>
                    <div className="text-xs text-gray-500">
                      {language.name}
                    </div>
                  </div>
                  {selectedLanguage === language.code && (
                    <svg
                      className="w-5 h-5 text-blue-600"
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
                </div>
              </label>
            ))}
          </div>
        </div>

        <div className="bg-gray-50 p-4 rounded-lg">
          <h4 className="text-sm font-medium text-gray-900 mb-2">
            Preview
          </h4>
          <div className={`text-sm ${isRtl ? 'text-right' : 'text-left'}`}>
            <p className="text-gray-600">
              {t('common.loading')} • {t('common.save')} • {t('common.cancel')}
            </p>
            <p className="text-gray-500 mt-1">
              {t('language.auto_detect')}
            </p>
          </div>
        </div>

        <div className="flex items-center justify-between pt-4 border-t border-gray-200">
          <div className="text-sm text-gray-500">
            {selectedLanguage !== currentLanguage && (
              <span>Language will be changed after saving</span>
            )}
          </div>
          <button
            onClick={handleSave}
            disabled={saving || selectedLanguage === currentLanguage}
            className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {saving ? t('common.loading') : t('common.save')}
          </button>
        </div>
      </div>
    </div>
  );
};

export default LanguageSettings;
