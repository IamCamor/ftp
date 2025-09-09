import { useState, useEffect, useCallback } from 'react';
import { languageService } from '../services/languageService';

interface UseTranslationReturn {
  t: (key: string, params?: Record<string, any>) => string;
  currentLanguage: string;
  isRtl: boolean;
  isLoading: boolean;
  changeLanguage: (languageCode: string) => Promise<boolean>;
  availableLanguages: any[];
}

export const useTranslation = (): UseTranslationReturn => {
  const [currentLanguage, setCurrentLanguage] = useState<string>('en');
  const [isRtl, setIsRtl] = useState<boolean>(false);
  const [isLoading, setIsLoading] = useState<boolean>(true);

  useEffect(() => {
    initializeLanguage();
  }, []);

  const initializeLanguage = async () => {
    try {
      setIsLoading(true);
      await languageService.initialize();
      setCurrentLanguage(languageService.getCurrentLanguageCode());
      setIsRtl(languageService.isCurrentLanguageRtl());
    } catch (error) {
      console.error('Failed to initialize language:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const t = useCallback((key: string, params?: Record<string, any>): string => {
    return languageService.t(key, params);
  }, []);

  const changeLanguage = useCallback(async (languageCode: string): Promise<boolean> => {
    try {
      setIsLoading(true);
      const success = await languageService.setLanguage(languageCode);
      if (success) {
        setCurrentLanguage(languageCode);
        setIsRtl(languageService.isCurrentLanguageRtl());
        return true;
      }
      return false;
    } catch (error) {
      console.error('Failed to change language:', error);
      return false;
    } finally {
      setIsLoading(false);
    }
  }, []);

  const availableLanguages = languageService.getAvailableLanguages();

  return {
    t,
    currentLanguage,
    isRtl,
    isLoading,
    changeLanguage,
    availableLanguages,
  };
};

export default useTranslation;
