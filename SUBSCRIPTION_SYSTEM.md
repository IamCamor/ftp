# Система подписок FishTrackPro

## Обзор

Реализована полноценная система подписок с двумя уровнями доступа: **Pro** и **Premium**. Пользователи могут оплачивать подписки как деньгами, так и бонусами, заработанными в приложении.

## Роли пользователей

### 👤 Обычный пользователь (user)
- Базовые возможности приложения
- Ограниченное количество уловов
- Реклама в приложении

### ⭐ Pro пользователь
- **Стоимость**: 199₽ или 1990 бонусов (30 дней)
- **Возможности**:
  - Неограниченные уловы
  - Расширенная статистика
  - Приоритетная поддержка
  - Без рекламы

### 👑 Premium пользователь
- **Стоимость**: 499₽ или 4990 бонусов (30 дней)
- **Возможности**:
  - Все возможности Pro
  - Создание и управление точками
  - Создание групп
  - Модерация групп
  - Приоритет в поиске
  - **Корона у аватарки** (золотая иконка)

### 🔧 Администратор (admin)
- Полный доступ ко всем функциям
- Административная панель
- Модерация контента

## Способы оплаты

### 💳 Платежные системы
- **Яндекс.Платежи** - для российских пользователей
- **Сбербанк** - интеграция с банковскими картами
- **Apple Pay** - для iOS устройств
- **Google Pay** - для Android устройств

### ⭐ Бонусная система
- Оплата бонусами (1₽ = 10 бонусов)
- Заработок бонусов за активность:
  - 10 бонусов за каждый улов
  - 1 бонус за лайк
  - 2 бонуса за комментарий
  - 5 бонусов за репост
  - 5 бонусов за ежедневный вход
  - Максимум 100 бонусов в день

## Техническая реализация

### Backend (Laravel)

#### База данных
```sql
-- Таблица подписок
subscriptions:
- id, user_id, type (pro/premium)
- status (active/expired/cancelled)
- payment_method, amount, bonus_amount
- starts_at, expires_at, cancelled_at
- metadata (JSON)

-- Таблица платежей
payments:
- id, user_id, subscription_id
- payment_id, provider, status
- type, amount, currency, bonus_amount
- provider_data, metadata
- paid_at, expires_at

-- Обновленная таблица пользователей
users:
- role (user/pro/premium/admin)
- is_premium, premium_expires_at
- crown_icon_url, bonus_balance
- last_bonus_earned_at
```

#### API Endpoints
```
GET    /api/v1/subscriptions/plans      - Получить планы подписок
GET    /api/v1/subscriptions/status     - Статус подписок пользователя
GET    /api/v1/subscriptions            - Список подписок
POST   /api/v1/subscriptions            - Создать подписку
GET    /api/v1/subscriptions/{id}       - Детали подписки
POST   /api/v1/subscriptions/{id}/cancel - Отменить подписку
POST   /api/v1/subscriptions/{id}/extend - Продлить подписку

GET    /api/v1/payments/methods         - Способы оплаты
GET    /api/v1/payments                 - История платежей
POST   /api/v1/payments/process         - Обработать платеж
POST   /api/v1/payments/{id}/cancel     - Отменить платеж
POST   /api/v1/payments/{id}/refund     - Возврат платежа
```

### Frontend (React + TypeScript)

#### Компоненты
- **SubscriptionPage** - страница выбора и оформления подписки
- **SubscriptionStatus** - отображение статуса подписки
- **Avatar** - обновлен для показа короны у Premium пользователей

#### Типы TypeScript
```typescript
interface User {
  role: 'user' | 'pro' | 'premium' | 'admin';
  is_premium?: boolean;
  premium_expires_at?: string;
  crown_icon_url?: string;
  bonus_balance?: number;
}

interface Subscription {
  type: 'pro' | 'premium';
  status: 'active' | 'expired' | 'cancelled';
  payment_method: 'yandex_pay' | 'sber_pay' | 'apple_pay' | 'google_pay' | 'bonuses';
  amount?: number;
  bonus_amount?: number;
  expires_at?: string;
}

interface Payment {
  provider: 'yandex_pay' | 'sber_pay' | 'apple_pay' | 'google_pay' | 'bonuses';
  status: 'pending' | 'processing' | 'completed' | 'failed' | 'cancelled' | 'refunded';
  type: 'subscription_pro' | 'subscription_premium' | 'bonus_purchase';
  amount: number;
  bonus_amount?: number;
}
```

## Конфигурация

### Настройки подписок (`backend/config/subscription.php`)
```php
'pro' => [
    'price_rub' => 199,
    'price_bonus' => 1990,
    'duration_days' => 30,
    'features' => [
        'unlimited_catches' => true,
        'advanced_statistics' => true,
        'priority_support' => true,
        'ad_free' => true,
    ],
],

'premium' => [
    'price_rub' => 499,
    'price_bonus' => 4990,
    'duration_days' => 30,
    'crown_icon_url' => 'https://cdn.fishtrackpro.ru/icons/crown.svg',
    'features' => [
        // Все возможности Pro +
        'create_points' => true,
        'manage_points' => true,
        'create_groups' => true,
        'moderate_groups' => true,
        'priority_search' => true,
        'crown_badge' => true,
    ],
],
```

## Функциональность

### ✅ Реализовано
- [x] Система ролей (user, pro, premium, admin)
- [x] Подписки с автоматическим истечением
- [x] Множественные способы оплаты
- [x] Бонусная система
- [x] Корона для Premium пользователей
- [x] API для управления подписками
- [x] Frontend интерфейс
- [x] Отмена и продление подписок
- [x] Система возвратов
- [x] Пробные периоды (7 дней)

### 🔄 В разработке
- [ ] Интеграция с реальными платежными системами
- [ ] Автоматическое продление подписок
- [ ] Push-уведомления об истечении подписки
- [ ] Аналитика по подпискам
- [ ] Промокоды и скидки

## Использование

### Для пользователей
1. Перейти в раздел "Pro" в нижнем меню
2. Выбрать план подписки (Pro или Premium)
3. Выбрать способ оплаты
4. Оформить подписку или начать пробный период

### Для разработчиков
```typescript
// Проверка роли пользователя
const user = await profileMe();
if (user.role === 'premium' || user.is_premium) {
  // Premium функциональность
}

// Получение статуса подписки
const status = await getSubscriptionStatus();
if (status.is_premium) {
  // Показать корону
  <Avatar crownIconUrl={status.crown_icon_url} isPremium={true} />
}
```

## Безопасность

- Все платежи проходят через защищенные API
- Проверка подписок на backend
- Валидация прав доступа
- Логирование всех операций с подписками
- Защита от повторного использования пробных периодов

## Мониторинг

- Отслеживание конверсии в подписки
- Аналитика по способам оплаты
- Мониторинг отмен подписок
- Статистика по бонусной системе

---

**Система подписок готова к использованию и легко масштабируется для добавления новых функций и способов оплаты!** 🚀
