import config from '../config';

/**
 * Утилита для условного логирования в консоль
 * Использует feature flags для управления выводом
 */

export const logger = {
  /**
   * Обычное логирование (всегда включено если enableConsoleLogs = true)
   */
  log: (...args: any[]) => {
    if (config.features.debug.enableConsoleLogs) {
      console.log(...args);
    }
  },

  /**
   * Логирование данных (контролируется enableDataLogging)
   */
  data: (...args: any[]) => {
    if (config.features.debug.enableConsoleLogs && config.features.debug.enableDataLogging) {
      console.log(...args);
    }
  },

  /**
   * Логирование ошибок (всегда включено)
   */
  error: (...args: any[]) => {
    console.error(...args);
  },

  /**
   * Логирование предупреждений (всегда включено)
   */
  warn: (...args: any[]) => {
    console.warn(...args);
  },

  /**
   * Логирование информации (контролируется enableConsoleLogs)
   */
  info: (...args: any[]) => {
    if (config.features.debug.enableConsoleLogs) {
      console.info(...args);
    }
  },

  /**
   * Логирование отладки (контролируется enableConsoleLogs)
   */
  debug: (...args: any[]) => {
    if (config.features.debug.enableConsoleLogs) {
      console.debug(...args);
    }
  },

  /**
   * Логирование JSON данных (контролируется enableDataLogging)
   */
  json: (label: string, data: any) => {
    if (config.features.debug.enableConsoleLogs && config.features.debug.enableDataLogging) {
      console.log(`${label} JSON:`, JSON.stringify(data, null, 2));
    }
  },

  /**
   * Логирование массивов (контролируется enableDataLogging)
   */
  array: (label: string, data: any[]) => {
    if (config.features.debug.enableConsoleLogs && config.features.debug.enableDataLogging) {
      console.log(`${label} Array (${data.length} items):`, data);
    }
  },

  /**
   * Логирование объектов (контролируется enableDataLogging)
   */
  object: (label: string, data: object) => {
    if (config.features.debug.enableConsoleLogs && config.features.debug.enableDataLogging) {
      console.log(`${label} Object:`, data);
    }
  }
};

export default logger;

