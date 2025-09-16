# Замена логотипа на оригинальную PNG картинку

## ✅ ГОТОВО: PNG логотип загружен и работает

**Текущий статус:** Логотип работает с PNG файлом (30KB)

## Для замены на вашу PNG картинку:

### 1. Загрузите вашу PNG картинку
Скопируйте вашу оригинальную PNG картинку в файл:
```
/var/www/ftp/frontend/public/logo.png
```

### 2. Обновите конфигурацию
После загрузки PNG файла, измените в файле `/var/www/ftp/frontend/src/config.ts`:
```typescript
logoUrl: '/logo.png',  // вместо '/logo.svg'
```

### 3. Создайте иконки (опционально)
- `favicon.png` (32x32px) - для вкладки браузера
- `icon-192.png` (192x192px) - для PWA  
- `icon-512.png` (512x512px) - для PWA

Все файлы должны быть в папке `/var/www/ftp/frontend/public/`

### 4. Обновите HTML и манифест
Измените в `index.html`:
```html
<link rel="icon" type="image/png" href="/favicon.png" />
```

И в `manifest.json` замените все SVG пути на PNG.

### 5. Соберите проект
```bash
cd /var/www/ftp/frontend
npm run build
```

## Текущие пути (работают):
- **Логотип:** `https://fishtrackpro.ru/logo.png` ✅ (загружен с Яндекс.Диска!)
- **Favicon:** `https://fishtrackpro.ru/favicon.svg` ✅ (обновлен с рыбкой!)
- **PWA иконки:** `https://fishtrackpro.ru/icon-*.svg` ✅ (обновлены с рыбкой!)

## Текущие файлы:
- ✅ `logo.png` (работает, 30KB, загружен с Яндекс.Диска)
- ✅ `favicon.svg` (работает, новый дизайн с рыбкой)
- ✅ `icon-192.svg` (работает, новый дизайн с рыбкой)
- ✅ `icon-512.svg` (работает, новый дизайн с рыбкой)
- ❌ `logo.svg` (старый SVG файл)
- ❌ `favicon.png` (пустой файл-заглушка)
