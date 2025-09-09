import moment from 'moment';

// Time formatting utilities
export const formatDuration = (minutes: number): string => {
  if (minutes < 60) {
    return `${minutes}м`;
  }
  
  const hours = Math.floor(minutes / 60);
  const remainingMinutes = minutes % 60;
  
  if (remainingMinutes === 0) {
    return `${hours}ч`;
  }
  
  return `${hours}ч ${remainingMinutes}м`;
};

export const formatTime = (date: string | Date): string => {
  return moment(date).format('HH:mm');
};

export const formatDateTime = (date: string | Date): string => {
  return moment(date).format('DD.MM.YYYY HH:mm');
};

export const formatDate = (date: string | Date): string => {
  return moment(date).format('DD.MM.YYYY');
};

// Number formatting utilities
export const formatNumber = (num: number, decimals: number = 1): string => {
  return num.toFixed(decimals);
};

export const formatWeight = (weight: number): string => {
  if (weight < 1) {
    return `${(weight * 1000).toFixed(0)}г`;
  }
  return `${weight.toFixed(1)}кг`;
};

export const formatLength = (length: number): string => {
  if (length < 100) {
    return `${length.toFixed(0)}см`;
  }
  return `${(length / 100).toFixed(1)}м`;
};

export const formatDistance = (distance: number): string => {
  if (distance < 1000) {
    return `${distance.toFixed(0)}м`;
  }
  return `${(distance / 1000).toFixed(1)}км`;
};

// Heart rate utilities
export const getHeartRateZone = (heartRate: number): string => {
  if (heartRate < 60) return 'resting';
  if (heartRate < 100) return 'light';
  if (heartRate < 140) return 'moderate';
  if (heartRate < 180) return 'vigorous';
  return 'maximum';
};

export const getHeartRateZoneColor = (zone: string): string => {
  const colors = {
    resting: '#4CAF50',
    light: '#8BC34A',
    moderate: '#FF9800',
    vigorous: '#FF5722',
    maximum: '#F44336',
  };
  return colors[zone as keyof typeof colors] || '#757575';
};

export const getHeartRateZoneDescription = (zone: string): string => {
  const descriptions = {
    resting: 'Покой',
    light: 'Легкая активность',
    moderate: 'Умеренная активность',
    vigorous: 'Интенсивная активность',
    maximum: 'Максимальная активность',
  };
  return descriptions[zone as keyof typeof descriptions] || 'Неизвестно';
};

// Mood utilities
export const getMoodEmoji = (moodIndex: number): string => {
  if (moodIndex >= 80) return '😊';
  if (moodIndex >= 60) return '🙂';
  if (moodIndex >= 40) return '😐';
  if (moodIndex >= 20) return '😕';
  return '😩';
};

export const getMoodDescription = (moodIndex: number): string => {
  if (moodIndex >= 80) return 'Отличное настроение';
  if (moodIndex >= 60) return 'Хорошее настроение';
  if (moodIndex >= 40) return 'Нейтральное настроение';
  if (moodIndex >= 20) return 'Плохое настроение';
  return 'Очень плохое настроение';
};

export const getMoodColor = (moodIndex: number): string => {
  if (moodIndex >= 80) return '#4CAF50';
  if (moodIndex >= 60) return '#8BC34A';
  if (moodIndex >= 40) return '#FF9800';
  if (moodIndex >= 20) return '#FF5722';
  return '#F44336';
};

// Stress utilities
export const getStressDescription = (stressLevel: number): string => {
  if (stressLevel >= 80) return 'Высокий стресс';
  if (stressLevel >= 60) return 'Повышенный стресс';
  if (stressLevel >= 40) return 'Умеренный стресс';
  if (stressLevel >= 20) return 'Низкий стресс';
  return 'Очень низкий стресс';
};

export const getStressColor = (stressLevel: number): string => {
  if (stressLevel >= 80) return '#F44336';
  if (stressLevel >= 60) return '#FF5722';
  if (stressLevel >= 40) return '#FF9800';
  if (stressLevel >= 20) return '#8BC34A';
  return '#4CAF50';
};

// Activity utilities
export const getActivityDescription = (activityLevel: number): string => {
  if (activityLevel >= 80) return 'Очень высокая';
  if (activityLevel >= 60) return 'Высокая';
  if (activityLevel >= 40) return 'Умеренная';
  if (activityLevel >= 20) return 'Низкая';
  return 'Очень низкая';
};

export const getActivityColor = (activityLevel: number): string => {
  if (activityLevel >= 80) return '#F44336';
  if (activityLevel >= 60) return '#FF5722';
  if (activityLevel >= 40) return '#FF9800';
  if (activityLevel >= 20) return '#8BC34A';
  return '#4CAF50';
};

// Validation utilities
export const isValidEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

export const isValidPassword = (password: string): boolean => {
  return password.length >= 6;
};

export const isValidHeartRate = (heartRate: number): boolean => {
  return heartRate >= 30 && heartRate <= 220;
};

export const isValidWeight = (weight: number): boolean => {
  return weight > 0 && weight <= 1000;
};

export const isValidLength = (length: number): boolean => {
  return length > 0 && length <= 500;
};

// Array utilities
export const groupBy = <T>(array: T[], key: keyof T): Record<string, T[]> => {
  return array.reduce((groups, item) => {
    const group = String(item[key]);
    groups[group] = groups[group] || [];
    groups[group].push(item);
    return groups;
  }, {} as Record<string, T[]>);
};

export const sortBy = <T>(array: T[], key: keyof T, direction: 'asc' | 'desc' = 'asc'): T[] => {
  return [...array].sort((a, b) => {
    const aVal = a[key];
    const bVal = b[key];
    
    if (aVal < bVal) return direction === 'asc' ? -1 : 1;
    if (aVal > bVal) return direction === 'asc' ? 1 : -1;
    return 0;
  });
};

export const uniqueBy = <T>(array: T[], key: keyof T): T[] => {
  const seen = new Set();
  return array.filter(item => {
    const value = item[key];
    if (seen.has(value)) {
      return false;
    }
    seen.add(value);
    return true;
  });
};

// String utilities
export const capitalize = (str: string): string => {
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

export const truncate = (str: string, length: number): string => {
  if (str.length <= length) return str;
  return str.substring(0, length) + '...';
};

export const slugify = (str: string): string => {
  return str
    .toLowerCase()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '');
};

// Math utilities
export const clamp = (value: number, min: number, max: number): number => {
  return Math.min(Math.max(value, min), max);
};

export const lerp = (start: number, end: number, factor: number): number => {
  return start + (end - start) * factor;
};

export const roundTo = (value: number, decimals: number): number => {
  return Math.round(value * Math.pow(10, decimals)) / Math.pow(10, decimals);
};

export const percentage = (value: number, total: number): number => {
  if (total === 0) return 0;
  return roundTo((value / total) * 100, 1);
};

// Color utilities
export const hexToRgb = (hex: string): { r: number; g: number; b: number } | null => {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? {
    r: parseInt(result[1], 16),
    g: parseInt(result[2], 16),
    b: parseInt(result[3], 16)
  } : null;
};

export const rgbToHex = (r: number, g: number, b: number): string => {
  return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
};

export const lightenColor = (hex: string, percent: number): string => {
  const rgb = hexToRgb(hex);
  if (!rgb) return hex;
  
  const r = Math.min(255, Math.floor(rgb.r + (255 - rgb.r) * percent));
  const g = Math.min(255, Math.floor(rgb.g + (255 - rgb.g) * percent));
  const b = Math.min(255, Math.floor(rgb.b + (255 - rgb.b) * percent));
  
  return rgbToHex(r, g, b);
};

export const darkenColor = (hex: string, percent: number): string => {
  const rgb = hexToRgb(hex);
  if (!rgb) return hex;
  
  const r = Math.max(0, Math.floor(rgb.r * (1 - percent)));
  const g = Math.max(0, Math.floor(rgb.g * (1 - percent)));
  const b = Math.max(0, Math.floor(rgb.b * (1 - percent)));
  
  return rgbToHex(r, g, b);
};

// Storage utilities
export const generateId = (): string => {
  return Date.now().toString(36) + Math.random().toString(36).substr(2);
};

export const debounce = <T extends (...args: any[]) => any>(
  func: T,
  wait: number
): ((...args: Parameters<T>) => void) => {
  let timeout: NodeJS.Timeout;
  return (...args: Parameters<T>) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func(...args), wait);
  };
};

export const throttle = <T extends (...args: any[]) => any>(
  func: T,
  limit: number
): ((...args: Parameters<T>) => void) => {
  let inThrottle: boolean;
  return (...args: Parameters<T>) => {
    if (!inThrottle) {
      func(...args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
};

// Platform utilities
export const isIOS = (): boolean => {
  return Platform.OS === 'ios';
};

export const isAndroid = (): boolean => {
  return Platform.OS === 'android';
};

export const getPlatformVersion = (): string => {
  return Platform.Version.toString();
};

// Error handling utilities
export const handleError = (error: any): string => {
  if (error.response?.data?.message) {
    return error.response.data.message;
  }
  if (error.message) {
    return error.message;
  }
  return 'Произошла неизвестная ошибка';
};

export const logError = (error: any, context?: string): void => {
  console.error(`[${context || 'Error'}]:`, error);
  // Here you could also send to crash reporting service
};

// Network utilities
export const isOnline = (): boolean => {
  // This would check network connectivity
  return true;
};

export const retryWithBackoff = async <T>(
  fn: () => Promise<T>,
  maxRetries: number = 3,
  baseDelay: number = 1000
): Promise<T> => {
  let lastError: any;
  
  for (let i = 0; i < maxRetries; i++) {
    try {
      return await fn();
    } catch (error) {
      lastError = error;
      if (i < maxRetries - 1) {
        const delay = baseDelay * Math.pow(2, i);
        await new Promise(resolve => setTimeout(resolve, delay));
      }
    }
  }
  
  throw lastError;
};
