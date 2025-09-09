# Многоязычность FishTrackPro

Система интернационализации (i18n) для FishTrackPro поддерживает топ 15 языков мира с автоопределением и настройками в профиле пользователя.

## 🌍 Поддерживаемые языки

| Код | Язык | Нативное название | Флаг | RTL |
|-----|------|-------------------|------|-----|
| `en` | English | English | 🇺🇸 | ❌ |
| `zh` | Chinese (Simplified) | 中文 | 🇨🇳 | ❌ |
| `hi` | Hindi | हिन्दी | 🇮🇳 | ❌ |
| `es` | Spanish | Español | 🇪🇸 | ❌ |
| `fr` | French | Français | 🇫🇷 | ❌ |
| `ar` | Arabic | العربية | 🇸🇦 | ✅ |
| `bn` | Bengali | বাংলা | 🇧🇩 | ❌ |
| `pt` | Portuguese | Português | 🇵🇹 | ❌ |
| `ru` | Russian | Русский | 🇷🇺 | ❌ |
| `ja` | Japanese | 日本語 | 🇯🇵 | ❌ |
| `de` | German | Deutsch | 🇩🇪 | ❌ |
| `ko` | Korean | 한국어 | 🇰🇷 | ❌ |
| `tr` | Turkish | Türkçe | 🇹🇷 | ❌ |
| `vi` | Vietnamese | Tiếng Việt | 🇻🇳 | ❌ |
| `it` | Italian | Italiano | 🇮🇹 | ❌ |

## 🏗️ Архитектура

### Backend компоненты

1. **Конфигурация**: `config/languages.php`
2. **Middleware**: `DetectLanguage` - автоопределение языка
3. **Сервис**: `LanguageService` - управление языками
4. **Контроллер**: `LanguageController` - API endpoints
5. **Модель**: `User` с полем `language`
6. **Переводы**: `resources/lang/{locale}/app.php`

### Frontend компоненты

1. **Сервис**: `languageService.ts` - работа с API
2. **Хук**: `useTranslation` - React интеграция
3. **Компоненты**: `LanguageSwitcher`, `LanguageSettings`
4. **Переводы**: `src/locales/{locale}/app.json`

## 🔧 Настройка

### Backend конфигурация

```php
// config/languages.php
return [
    'default' => env('APP_DEFAULT_LANGUAGE', 'en'),
    'fallback' => env('APP_FALLBACK_LANGUAGE', 'en'),
    'auto_detect' => env('APP_AUTO_DETECT_LANGUAGE', true),
    'detection_sources' => [
        'user_preference',
        'url_parameter',
        'session',
        'browser_header',
        'default',
    ],
    'storage' => [
        'user_preference' => true,
        'session' => true,
        'cookie' => true,
    ],
    'rtl_support' => true,
];
```

### Frontend конфигурация

```typescript
// src/services/languageService.ts
const languageService = new LanguageService();
await languageService.initialize();
```

## 🔌 API Endpoints

### Публичные маршруты

```http
GET /api/v1/languages
```
Получить все поддерживаемые языки

```http
GET /api/v1/languages/by-region
```
Получить языки, сгруппированные по регионам

```http
GET /api/v1/languages/switcher
```
Получить данные для переключателя языков

```http
GET /api/v1/languages/current
```
Получить текущий язык

```http
GET /api/v1/languages/detect
```
Получить информацию об автоопределении языка

```http
GET /api/v1/languages/rtl
```
Получить RTL языки

```http
GET /api/v1/languages/config
```
Получить конфигурацию языков

### Аутентифицированные маршруты

```http
POST /api/v1/languages/set
```
Установить язык пользователя

**Тело запроса:**
```json
{
    "language": "ru"
}
```

```http
GET /api/v1/languages/user-preference
```
Получить предпочтения языка пользователя

### Админские маршруты

```http
GET /api/admin/languages/statistics
```
Получить статистику использования языков

```http
POST /api/admin/languages/clear-cache
```
Очистить кэш языков

## 🎯 Автоопределение языка

Система автоматически определяет язык пользователя в следующем порядке:

1. **Предпочтение пользователя** - сохраненное в профиле
2. **URL параметр** - `?lang=ru`
3. **Сессия** - сохраненное в сессии
4. **Заголовок браузера** - `Accept-Language`
5. **По умолчанию** - язык приложения

### Поддержка браузерных кодов

Система автоматически маппит браузерные коды языков:

```php
'browser_mapping' => [
    'zh-cn' => 'zh',
    'zh-tw' => 'zh',
    'pt-br' => 'pt',
    'en-us' => 'en',
    'es-es' => 'es',
    // ... и другие
],
```

## 🎨 Frontend интеграция

### Использование хука переводов

```tsx
import { useTranslation } from '../hooks/useTranslation';

const MyComponent = () => {
  const { t, currentLanguage, isRtl, changeLanguage } = useTranslation();

  return (
    <div className={isRtl ? 'text-right' : 'text-left'}>
      <h1>{t('nav.home')}</h1>
      <p>{t('common.loading')}</p>
      <button onClick={() => changeLanguage('ru')}>
        {t('language.select_language')}
      </button>
    </div>
  );
};
```

### Переключатель языков

```tsx
import { LanguageSwitcher } from '../components/LanguageSwitcher';

const Header = () => {
  return (
    <header>
      <LanguageSwitcher 
        showFlags={true}
        showNativeNames={true}
        onLanguageChange={(lang) => console.log('Language changed:', lang)}
      />
    </header>
  );
};
```

### Настройки языка в профиле

```tsx
import { LanguageSettings } from '../components/LanguageSettings';

const ProfileSettings = () => {
  return (
    <div>
      <LanguageSettings 
        onLanguageChange={(lang) => {
          // Обновить UI или перезагрузить страницу
          window.location.reload();
        }}
      />
    </div>
  );
};
```

## 📝 Добавление новых языков

### 1. Backend

Добавьте язык в конфигурацию:

```php
// config/languages.php
'supported' => [
    'new_lang' => [
        'name' => 'New Language',
        'native_name' => 'Native Name',
        'flag' => '🏳️',
        'rtl' => false,
        'enabled' => true,
    ],
],
```

Создайте файл переводов:

```bash
mkdir -p resources/lang/new_lang
touch resources/lang/new_lang/app.php
```

### 2. Frontend

Создайте директорию и файл переводов:

```bash
mkdir -p src/locales/new_lang
touch src/locales/new_lang/app.json
```

Добавьте язык в сервис:

```typescript
// src/services/languageService.ts
getAvailableLanguages(): Language[] {
  return [
    // ... существующие языки
    { 
      code: 'new_lang', 
      name: 'New Language', 
      native_name: 'Native Name', 
      flag: '🏳️', 
      rtl: false, 
      enabled: true 
    },
  ];
}
```

## 🔄 RTL поддержка

Для языков с направлением справа налево (RTL):

### Backend

```php
'ar' => [
    'name' => 'Arabic',
    'native_name' => 'العربية',
    'flag' => '🇸🇦',
    'rtl' => true,  // Включить RTL
    'enabled' => true,
],
```

### Frontend

```tsx
const { isRtl } = useTranslation();

return (
  <div className={isRtl ? 'text-right' : 'text-left'}>
    <h1>{t('nav.home')}</h1>
  </div>
);
```

## 📊 Статистика языков

### Получение статистики

```typescript
const stats = await languageService.getLanguageStatistics();

console.log(stats);
// {
//   total_users: 1234,
//   languages: [
//     {
//       code: 'en',
//       name: 'English',
//       native_name: 'English',
//       flag: '🇺🇸',
//       users_count: 500,
//       percentage: 40.5
//     },
//     // ... другие языки
//   ],
//   default_language: 'en',
//   current_language: 'ru'
// }
```

## 🚀 Развертывание

### 1. Миграции

```bash
php artisan migrate
```

### 2. Очистка кэша

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 3. Перезапуск очередей

```bash
php artisan queue:restart
```

## 🧪 Тестирование

### Тест автоопределения

```php
public function test_language_auto_detection()
{
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_ACCEPT_LANGUAGE' => 'ru-RU,ru;q=0.9,en;q=0.8'
    ]);
    
    $middleware = new DetectLanguage();
    $middleware->handle($request, function ($req) {
        $this->assertEquals('ru', app()->getLocale());
    });
}
```

### Тест API

```typescript
test('should get supported languages', async () => {
  const response = await languageService.getSupportedLanguages();
  expect(response).toHaveLength(15);
  expect(response[0]).toHaveProperty('code');
  expect(response[0]).toHaveProperty('name');
});
```

## 🔍 Мониторинг

### Логи

Все операции с языками логируются:

```php
Log::info('Language changed', [
    'user_id' => $user->id,
    'old_language' => $oldLanguage,
    'new_language' => $newLanguage,
    'source' => 'user_preference'
]);
```

### Метрики

Отслеживайте:
- Популярность языков
- Успешность автоопределения
- Ошибки загрузки переводов
- Производительность API

## 🛠️ Troubleshooting

### Проблемы с переводами

1. **Переводы не загружаются**
   - Проверьте существование файлов переводов
   - Убедитесь в правильности кодов языков
   - Очистите кэш приложения

2. **RTL не работает**
   - Проверьте настройку `rtl: true` в конфигурации
   - Убедитесь в правильной CSS поддержке
   - Проверьте атрибуты `dir` и `lang` в HTML

3. **Автоопределение не работает**
   - Проверьте middleware в `Kernel.php`
   - Убедитесь в правильности заголовков браузера
   - Проверьте маппинг браузерных кодов

### Отладка

```php
// Включить отладку языков
Log::debug('Language detection', [
    'accept_language' => $request->header('Accept-Language'),
    'detected' => $detectedLanguage,
    'sources' => $detectionSources
]);
```

## 📚 Дополнительные ресурсы

- [Laravel Localization](https://laravel.com/docs/localization)
- [React i18next](https://react.i18next.com/)
- [Unicode CLDR](https://cldr.unicode.org/)
- [ISO 639-1 Language Codes](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes)
