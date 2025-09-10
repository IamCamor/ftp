# MCP Playwright Integration для FishTrackPro

## Обзор

Этот проект интегрирован с Model Context Protocol (MCP) Playwright для автоматизации браузера и тестирования веб-приложения FishTrackPro.

## Установка

### 1. Установка зависимостей

```bash
npm install
```

### 2. Установка MCP пакетов

```bash
npm install @modelcontextprotocol/sdk puppeteer-mcp-server @hisma/server-puppeteer
```

## Использование

### Запуск MCP сервера

```bash
# Запуск основного Puppeteer MCP сервера
npm run mcp:server

# Запуск альтернативного Hisma Puppeteer сервера
npm run mcp:server:hisma
```

### Тестирование с MCP

```bash
# Запуск автоматизированного тестирования
npm run mcp:test

# Запуск TypeScript версии
npm run mcp:test:ts

# Запуск с автоматизацией
npm run test:automation
```

### Разработка с MCP

```bash
# Запуск фронтенда и MCP сервера одновременно
npm run dev:with-mcp
```

## Доступные инструменты

MCP Playwright предоставляет следующие инструменты:

- `navigate` - переход на URL
- `screenshot` - создание скриншота
- `click` - клик по элементу
- `type` - ввод текста
- `getContent` - получение содержимого страницы
- `waitForElement` - ожидание элемента
- `evaluate` - выполнение JavaScript

## Примеры использования

### Базовое использование

```javascript
import MCPPlaywrightClient from './mcp-client.js';

const client = new MCPPlaywrightClient();
await client.connect();

// Переход на страницу
await client.navigateToUrl('http://localhost:5173');

// Создание скриншота
await client.takeScreenshot('homepage.png');

// Клик по элементу
await client.clickElement('button[data-testid="add-catch"]');

// Ввод текста
await client.typeText('input[name="fish-type"]', 'Щука');

await client.close();
```

### TypeScript использование

```typescript
import { MCPPlaywrightClient, FishTrackProTester } from './mcp-client';

// Простое использование
const client = new MCPPlaywrightClient();
await client.connect();
await client.navigateToUrl('http://localhost:5173');
await client.close();

// Автоматизированное тестирование
const tester = new FishTrackProTester();
await tester.testApplication();
```

## Конфигурация

### MCP конфигурация (mcp-config.json)

```json
{
  "mcpServers": {
    "puppeteer": {
      "command": "npx",
      "args": ["puppeteer-mcp-server"],
      "env": {
        "NODE_ENV": "development"
      }
    }
  }
}
```

### Переменные окружения

```env
NODE_ENV=development
MCP_SERVER_URL=http://localhost:3000
PUPPETEER_HEADLESS=false
```

## Тестирование FishTrackPro

### Автоматизированные тесты

MCP клиент автоматически тестирует:

1. **Навигацию** - переходы между страницами
2. **Функциональность** - кнопки добавления уловов и мест
3. **UI элементы** - доступность интерфейса
4. **Скриншоты** - визуальная документация

### Ручное тестирование

```bash
# Запуск фронтенда
cd frontend && npm run dev

# В другом терминале - запуск MCP тестов
npm run mcp:test
```

## Отладка

### Логи MCP сервера

```bash
# Запуск с подробными логами
DEBUG=mcp* npm run mcp:server
```

### Скриншоты

Все скриншоты сохраняются в корневой директории проекта:
- `fishtrackpro-home.png` - главная страница
- `fishtrackpro-map.png` - страница карты
- `fishtrackpro-profile.png` - страница профиля
- `fishtrackpro-add-catch.png` - форма добавления улова
- `fishtrackpro-add-point.png` - форма добавления места

## Интеграция с CI/CD

### GitHub Actions

```yaml
name: MCP Tests
on: [push, pull_request]

jobs:
  mcp-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: '18'
      - run: npm install
      - run: npm run build
      - run: npm run mcp:test
```

## Устранение неполадок

### Проблемы с подключением

1. Убедитесь, что MCP сервер запущен
2. Проверьте, что порты не заняты
3. Проверьте логи сервера

### Проблемы с Puppeteer

1. Установите зависимости браузера:
   ```bash
   npx puppeteer browsers install chrome
   ```

2. Проверьте переменные окружения:
   ```bash
   export PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
   ```

### Проблемы с TypeScript

1. Убедитесь, что TypeScript установлен:
   ```bash
   npm install -g typescript
   ```

2. Скомпилируйте TypeScript файлы:
   ```bash
   npm run build:mcp
   ```

## Дополнительные ресурсы

- [Model Context Protocol Documentation](https://modelcontextprotocol.io/)
- [Puppeteer Documentation](https://pptr.dev/)
- [MCP SDK Documentation](https://github.com/modelcontextprotocol/sdk)

## Лицензия

MIT License - см. файл LICENSE для подробностей.
