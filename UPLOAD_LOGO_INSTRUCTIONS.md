# Инструкция по загрузке логотипа

## 📁 Где загрузить файл

Загрузите вашу PNG картинку "FishTrackPro" в граффити стиле в одну из папок:

### Вариант 1: В корень проекта
```
/var/www/ftp/logo.png
```

### Вариант 2: В папку uploads
```
/var/www/ftp/uploads/logo.png
```

### Вариант 3: В папку frontend/public
```
/var/www/ftp/frontend/public/logo.png
```

## 🔧 После загрузки

### Если загрузили в корень проекта:
```bash
cd /var/www/ftp
cp logo.png frontend/public/logo.png
cd frontend
npm run build
```

### Если загрузили в папку uploads:
```bash
cd /var/www/ftp
cp uploads/logo.png frontend/public/logo.png
cd frontend
npm run build
```

### Если загрузили в frontend/public:
```bash
cd /var/www/ftp/frontend
npm run build
```

## ✅ Проверка

После загрузки и сборки логотип будет доступен по адресу:
`https://fishtrackpro.ru/logo.png`

## 📋 Текущая конфигурация

- **Логотип:** `/logo.png` (PNG формат)
- **Favicon:** `/favicon.svg` (SVG с рыбкой)
- **PWA иконки:** `/icon-*.svg` (SVG с рыбкой)


