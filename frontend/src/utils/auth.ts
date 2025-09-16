// import { request } from './http'; // Пока не используется

export const checkTokenValidity = async (): Promise<boolean> => {
  const token = localStorage.getItem('token');
  if (!token) {
    return false;
  }

  // Для упрощенной аутентификации просто проверяем наличие токена
  // В будущем можно добавить проверку на сервере
  return true;
};

export const clearInvalidToken = (): void => {
  localStorage.removeItem('token');
  // Optionally redirect to login page
  window.location.href = '/auth/login';
};

export const getCurrentUser = () => {
  const token = localStorage.getItem('token');
  if (!token) {
    return null;
  }
  
  // Для упрощенной аутентификации возвращаем базовую информацию
  // В будущем можно получать с сервера
  return {
    id: 1,
    name: 'Пользователь',
    email: 'user@example.com',
    username: 'user',
    role: 'user' as const,
  };
};


