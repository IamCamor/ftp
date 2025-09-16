/**
 * Утилита для валидации ID уловов
 */

/**
 * Проверяет, является ли ID улова валидным
 * @param id - ID улова для проверки
 * @returns true, если ID валидный, false - если нет
 */
export function isValidCatchId(id: number | string): boolean {
  const numericId = typeof id === 'string' ? parseInt(id, 10) : id;
  
  // Проверяем, что ID является положительным числом
  if (isNaN(numericId) || numericId <= 0) {
    return false;
  }
  
  // Проверяем, что ID находится в разумном диапазоне
  // (например, больше 200, так как доступные ID начинаются с 218)
  if (numericId < 200) {
    return false;
  }
  
  return true;
}

/**
 * Получает ближайший доступный ID улова
 * @param currentId - текущий ID
 * @param availableIds - массив доступных ID
 * @returns ближайший доступный ID или null
 */
export function findNearestCatchId(currentId: number, availableIds: number[]): number | null {
  if (availableIds.length === 0) {
    return null;
  }
  
  // Сортируем ID по возрастанию
  const sortedIds = [...availableIds].sort((a, b) => a - b);
  
  // Ищем ближайший ID
  let nearestId = sortedIds[0];
  let minDifference = Math.abs(currentId - nearestId);
  
  for (const id of sortedIds) {
    const difference = Math.abs(currentId - id);
    if (difference < minDifference) {
      minDifference = difference;
      nearestId = id;
    }
  }
  
  return nearestId;
}

/**
 * Валидирует ID улова и возвращает информацию о валидности
 * @param id - ID улова для проверки
 * @returns объект с информацией о валидности
 */
export function validateCatchId(id: number | string): {
  isValid: boolean;
  error?: string;
  suggestion?: number;
} {
  const numericId = typeof id === 'string' ? parseInt(id, 10) : id;
  
  if (isNaN(numericId)) {
    return {
      isValid: false,
      error: 'ID должен быть числом'
    };
  }
  
  if (numericId <= 0) {
    return {
      isValid: false,
      error: 'ID должен быть положительным числом'
    };
  }
  
  if (numericId < 428) {
    return {
      isValid: false,
      error: 'ID слишком мал (доступные ID начинаются с 428)',
      suggestion: 428
    };
  }
  
  if (numericId > 477) {
    return {
      isValid: false,
      error: 'ID слишком велик (доступные ID заканчиваются на 477)',
      suggestion: 477
    };
  }
  
  return {
    isValid: true
  };
}

/**
 * Генерирует случайный валидный ID улова из доступных
 * @param availableIds - массив доступных ID
 * @returns случайный ID или null, если массив пуст
 */
export function getRandomCatchId(availableIds: number[]): number | null {
  if (availableIds.length === 0) {
    return null;
  }
  
  const randomIndex = Math.floor(Math.random() * availableIds.length);
  return availableIds[randomIndex];
}

/**
 * Проверяет, существует ли ID в списке доступных
 * @param id - ID для проверки
 * @param availableIds - массив доступных ID
 * @returns true, если ID существует в списке
 */
export function isCatchIdAvailable(id: number, availableIds: number[]): boolean {
  return availableIds.includes(id);
}
