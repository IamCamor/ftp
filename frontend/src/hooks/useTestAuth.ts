import { useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';

// Тестовый токен для пользователя с id=1
const TEST_TOKEN = 'simple_token_1_' + Date.now();

export const useTestAuth = () => {
  const [searchParams] = useSearchParams();

  useEffect(() => {
    // Проверяем, есть ли уже токен в localStorage
    const existingToken = localStorage.getItem('token');
    
    if (!existingToken) {
      // Авторизуем пользователя с тестовым токеном по умолчанию
      localStorage.setItem('token', TEST_TOKEN);
      console.log('🔐 Тестовая авторизация активирована для пользователя с id=1');
    }
    
    const testId = searchParams.get('id');
    if (testId === '1') {
      // Убираем параметр из URL
      const newUrl = new URL(window.location.href);
      newUrl.searchParams.delete('id');
      window.history.replaceState({}, '', newUrl.toString());
    }
  }, [searchParams]);

  return {
    isTestAuth: true // Всегда возвращаем true для тестовой авторизации
  };
};
