# Управление логированием в консоли

## Feature Flags для отладки

В приложении FishTrackPro добавлены feature flags для управления выводом данных в консоль:

### Конфигурация

В файле `src/config.ts`:

```typescript
features: {
  debug: {
    enableConsoleLogs: true,    // Включить/выключить все логи в консоль
    enableDataLogging: false,   // Включить/выключить логирование массивов данных
  }
}
```

### Использование

#### 1. Обычное логирование
```typescript
import logger from '../utils/logger';

// Всегда выводится если enableConsoleLogs = true
logger.log('Обычное сообщение');
logger.info('Информационное сообщение');
logger.debug('Отладочное сообщение');
```

#### 2. Логирование данных
```typescript
// Выводится только если enableConsoleLogs = true И enableDataLogging = true
logger.data('Данные получены:', data);
logger.json('JSON данные:', jsonData);
logger.array('Массив данных:', arrayData);
logger.object('Объект данных:', objectData);
```

#### 3. Логирование ошибок
```typescript
// Всегда выводится (не зависит от feature flags)
logger.error('Ошибка:', error);
logger.warn('Предупреждение:', warning);
```

### Текущие настройки

- **enableConsoleLogs**: `true` - обычные логи включены
- **enableDataLogging**: `false` - логирование массивов данных отключено

### Как изменить

1. **Отключить все логи**:
   ```typescript
   enableConsoleLogs: false
   ```

2. **Включить логирование данных**:
   ```typescript
   enableDataLogging: true
   ```

3. **Полностью отключить отладку**:
   ```typescript
   enableConsoleLogs: false,
   enableDataLogging: false
   ```

### Затронутые компоненты

- `InstagramCatchCard.tsx` - логирование данных улова
- `InstagramCatchDetail.tsx` - логирование деталей улова и комментариев
- `WeatherPage.tsx` - логирование данных погоды
- `NotificationsPage.tsx` - логирование уведомлений
- `http.ts` - логирование HTTP запросов

### Преимущества

- ✅ Уменьшает засорение консоли в продакшене
- ✅ Позволяет включать детальную отладку при необходимости
- ✅ Централизованное управление логированием
- ✅ Легко переключать между режимами отладки

