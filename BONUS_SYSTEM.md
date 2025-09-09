# Система бонусов FishTrackPro

Система бонусов поощряет активность пользователей в приложении FishTrackPro, начисляя виртуальную валюту за различные действия.

## 🪙 Размеры бонусов

| Действие | Размер бонуса | Описание |
|----------|---------------|----------|
| Добавление друга | 200 🪙 | За добавление нового друга в список |
| Запись улова | 50 🪙 | За публикацию нового улова |
| Создание точки | 100 🪙 | За добавление новой рыболовной точки |
| Добавление комментария | 10 🪙 | За комментарий к улову |
| Поставленный лайк | 5 🪙 | За лайк улова другого пользователя |

## 🏗️ Архитектура системы

### Основные компоненты

1. **BonusTransaction** - модель для хранения транзакций бонусов
2. **BonusService** - сервис для управления бонусами
3. **BonusController** - API контроллер для работы с бонусами
4. **События и слушатели** - автоматическое начисление бонусов

### События

- `FriendAdded` - добавление друга
- `CatchRecorded` - запись улова
- `PointCreated` - создание точки
- `CommentAdded` - добавление комментария
- `LikeGiven` - поставленный лайк
- `BonusAwarded` - начисление бонуса

### Слушатели

- `AwardFriendBonus` - начисление бонуса за друга
- `AwardCatchBonus` - начисление бонуса за улов
- `AwardPointBonus` - начисление бонуса за точку
- `AwardCommentBonus` - начисление бонуса за комментарий
- `AwardLikeBonus` - начисление бонуса за лайк
- `SendBonusNotification` - уведомление в Telegram

## 📊 База данных

### Таблица `bonus_transactions`

```sql
CREATE TABLE bonus_transactions (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    type ENUM('earned', 'spent', 'refund') NOT NULL,
    action VARCHAR(255) NOT NULL,
    amount INTEGER NOT NULL,
    description TEXT NULL,
    metadata JSON NULL,
    related_user_id BIGINT NULL,
    related_catch_id BIGINT NULL,
    related_point_id BIGINT NULL,
    related_comment_id BIGINT NULL,
    related_like_id BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (related_catch_id) REFERENCES catch_records(id) ON DELETE SET NULL,
    FOREIGN KEY (related_point_id) REFERENCES points(id) ON DELETE SET NULL,
    FOREIGN KEY (related_comment_id) REFERENCES catch_comments(id) ON DELETE SET NULL,
    FOREIGN KEY (related_like_id) REFERENCES catch_likes(id) ON DELETE SET NULL
);
```

### Поля в таблице `users`

- `bonus_balance` - текущий баланс бонусов пользователя
- `last_bonus_earned_at` - время последнего начисления бонуса

## 🔌 API Endpoints

### Пользовательские маршруты

```http
GET /api/v1/bonus
```
Получить баланс и статистику бонусов пользователя

```http
GET /api/v1/bonus/transactions
```
Получить историю транзакций бонусов

**Параметры:**
- `per_page` - количество записей на странице (по умолчанию 20)
- `type` - тип транзакции (`earned`, `spent`, `all`)
- `action` - действие для фильтрации

```http
GET /api/v1/bonus/amounts
```
Получить размеры бонусов за различные действия

```http
GET /api/v1/bonus/statistics
```
Получить статистику бонусов пользователя

**Параметры:**
- `action` - действие для получения статистики

```http
GET /api/v1/bonus/leaderboard
```
Получить таблицу лидеров по бонусам

**Параметры:**
- `limit` - количество участников (по умолчанию 10)
- `period` - период (`all`, `month`, `week`)

```http
POST /api/v1/bonus/spend
```
Потратить бонусы

**Тело запроса:**
```json
{
    "action": "subscription_purchased",
    "amount": 1990,
    "description": "Покупка Pro подписки"
}
```

### Админские маршруты

```http
GET /api/admin/bonus/global-stats
```
Получить глобальную статистику по бонусам

```http
POST /api/admin/bonus/award
```
Начислить бонусы пользователю вручную

**Тело запроса:**
```json
{
    "user_id": 123,
    "action": "manual_award",
    "amount": 100,
    "description": "Бонус за активность"
}
```

## 🤖 Telegram уведомления

Система автоматически отправляет уведомления в Telegram о начислении значительных бонусов (от 50 🪙).

### Шаблон уведомления

```
🎁 *Бонус начислен!*

Пользователь: Иван Петров (@ivan_fisher)
Действие: Запись улова
Бонус: +50 🪙
Баланс: 1250 🪙
Время: 2024-01-15 16:45:12
```

### Feature Flag

Уведомления о бонусах можно отключить через переменную окружения:

```env
TELEGRAM_BONUS_NOTIFICATIONS=false
```

## 🛡️ Защита от злоупотреблений

### Rate Limiting

- **Кулдаун**: 5 минут между начислениями за одно и то же действие
- **Проверка**: `BonusService::canPerformAction()`

### Валидация

- Проверка существования связанных сущностей
- Валидация размеров бонусов
- Проверка баланса при трате

## 📈 Статистика

### Пользовательская статистика

```json
{
    "balance": 1250,
    "statistics": {
        "total_earned": 1500,
        "total_spent": 250,
        "current_balance": 1250,
        "transactions_count": 45,
        "by_action": {
            "friends_added": 2,
            "catches_recorded": 15,
            "points_created": 8,
            "comments_added": 12,
            "likes_given": 8
        },
        "recent_transactions": [...]
    }
}
```

### Глобальная статистика

```json
{
    "total_users": 1234,
    "total_transactions": 5678,
    "total_bonuses_awarded": 125000,
    "total_bonuses_spent": 25000,
    "average_bonus_per_user": 101.3,
    "top_earners": [...],
    "action_statistics": [...]
}
```

## 🎯 Использование бонусов

### Покупка подписки

Пользователи могут тратить бонусы на:
- Pro подписку: 1990 🪙
- Premium подписку: 4990 🪙
- Дополнительные функции

### Пример траты бонусов

```php
$bonusService = app(BonusService::class);

$transaction = $bonusService->spendBonus(
    user: $user,
    action: 'subscription_purchased',
    amount: 1990,
    description: 'Покупка Pro подписки'
);
```

## 🔧 Настройка

### Переменные окружения

```env
# Telegram уведомления о бонусах
TELEGRAM_BONUS_NOTIFICATIONS=true
```

### Конфигурация

Размеры бонусов настраиваются в модели `BonusTransaction`:

```php
const BONUS_FRIEND_ADDED = 200;
const BONUS_CATCH_RECORDED = 50;
const BONUS_POINT_CREATED = 100;
const BONUS_COMMENT_ADDED = 10;
const BONUS_LIKE_GIVEN = 5;
```

## 🚀 Развертывание

1. Запустите миграции:
   ```bash
   php artisan migrate
   ```

2. Очистите кэш событий:
   ```bash
   php artisan event:clear
   php artisan config:clear
   ```

3. Перезапустите очереди:
   ```bash
   php artisan queue:restart
   ```

## 📝 Логирование

Все операции с бонусами логируются:

```php
Log::info('Bonus awarded', [
    'user_id' => $user->id,
    'action' => $action,
    'amount' => $amount,
    'transaction_id' => $transaction->id
]);
```

## 🧪 Тестирование

### Тест начисления бонуса

```php
$user = User::factory()->create();
$catch = CatchRecord::factory()->create(['user_id' => $user->id]);

event(new CatchRecorded($catch));

$user->refresh();
$this->assertEquals(50, $user->bonus_balance);
```

### Тест траты бонусов

```php
$user = User::factory()->create(['bonus_balance' => 1000]);

$bonusService = app(BonusService::class);
$transaction = $bonusService->spendBonus($user, 'test', 100);

$user->refresh();
$this->assertEquals(900, $user->bonus_balance);
```

## 🔍 Мониторинг

### Метрики для отслеживания

- Количество начисленных бонусов в день
- Топ пользователи по бонусам
- Популярные действия
- Конверсия бонусов в подписки

### Алерты

- Аномально высокое начисление бонусов
- Ошибки в системе бонусов
- Проблемы с Telegram уведомлениями
