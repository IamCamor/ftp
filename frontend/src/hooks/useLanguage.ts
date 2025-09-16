import { useState, useEffect } from 'react';

export type Language = 'ru' | 'en';

export interface LanguageOption {
  code: Language;
  name: string;
  nativeName: string;
  flag: string;
}

export const languageOptions: LanguageOption[] = [
  { code: 'ru', name: 'Russian', nativeName: 'Русский', flag: '🇷🇺' },
  { code: 'en', name: 'English', nativeName: 'English', flag: '🇺🇸' }
];

export const useLanguage = () => {
  const [language, setLanguage] = useState<Language>(() => {
    // Проверяем сохраненный язык в localStorage
    const savedLanguage = localStorage.getItem('language') as Language;
    if (savedLanguage && languageOptions.some(opt => opt.code === savedLanguage)) {
      return savedLanguage;
    }
    
    // Проверяем язык браузера
    const browserLanguage = navigator.language.split('-')[0] as Language;
    if (languageOptions.some(opt => opt.code === browserLanguage)) {
      return browserLanguage;
    }
    
    // По умолчанию русский
    return 'ru';
  });

  useEffect(() => {
    // Применяем язык к документу
    document.documentElement.setAttribute('lang', language);
    
    // Сохраняем в localStorage
    localStorage.setItem('language', language);
  }, [language]);

  const changeLanguage = (newLanguage: Language) => {
    setLanguage(newLanguage);
  };

  const getCurrentLanguage = () => {
    return languageOptions.find(opt => opt.code === language) || languageOptions[0];
  };

  return {
    language,
    setLanguage: changeLanguage,
    getCurrentLanguage,
    languageOptions
  };
};
