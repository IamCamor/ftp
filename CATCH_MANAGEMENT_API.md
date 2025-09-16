# API для управления уловами и жалобами

## Редактирование и удаление уловов

### Редактирование улова
```
PUT /api/v1/catch/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "species": "Новый вид рыбы",
  "length": 30.5,
  "weight": 1.8,
  "notes": "Обновленные заметки",
  "privacy": "friends"
}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Catch updated successfully",
  "data": {
    "id": 1,
    "species": "Новый вид рыбы",
    "length": 30.5,
    "weight": 1.8,
    "notes": "Обновленные заметки",
    "privacy": "friends",
    "user": {...},
    "created_at": "2025-09-12T10:00:00Z",
    "updated_at": "2025-09-12T10:30:00Z"
  }
}
```

### Удаление улова
```
DELETE /api/v1/catch/{id}
Authorization: Bearer {token}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Catch deleted successfully"
}
```

## Система жалоб

### Подача жалобы на улов
```
POST /api/v1/catch/{id}/report
Authorization: Bearer {token}
Content-Type: application/json

{
  "category": "spam",
  "description": "Это спам контент"
}
```

**Категории жалоб:**
- `spam` - Спам
- `advertisement` - Реклама  
- `fraud` - Обман

**Ответ:**
```json
{
  "success": true,
  "message": "Report submitted successfully",
  "data": {
    "id": 1,
    "category": "spam",
    "category_label": "Спам",
    "description": "Это спам контент",
    "status": "pending",
    "status_label": "Ожидает рассмотрения",
    "created_at": "2025-09-12T10:00:00Z",
    "reports_count": 1,
    "catch_hidden": false
  }
}
```

### Получение категорий жалоб
```
GET /api/v1/catch/report-categories
```

**Ответ:**
```json
{
  "success": true,
  "data": [
    {"value": "spam", "label": "Спам"},
    {"value": "advertisement", "label": "Реклама"},
    {"value": "fraud", "label": "Обман"}
  ]
}
```

### Получение жалоб на улов (только для админов)
```
GET /api/v1/catch/{id}/reports
Authorization: Bearer {admin_token}
```

**Ответ:**
```json
{
  "success": true,
  "data": {
    "catch": {
      "id": 1,
      "species": "Рыба",
      "user": {
        "id": 1,
        "name": "Иван Рыболов",
        "username": "ivan_fisher"
      }
    },
    "reports": [
      {
        "id": 1,
        "category": "spam",
        "category_label": "Спам",
        "description": "Это спам контент",
        "status": "pending",
        "status_label": "Ожидает рассмотрения",
        "user": {
          "id": 2,
          "name": "Мария",
          "username": "maria"
        },
        "reviewer": null,
        "admin_notes": null,
        "created_at": "2025-09-12T10:00:00Z",
        "reviewed_at": null
      }
    ],
    "total_reports": 1,
    "is_hidden": false
  }
}
```

## Логика скрытия уловов

- После получения **3 жалоб** улов автоматически скрывается для всех пользователей
- **Автор улова** всегда может видеть свой улов, даже если он скрыт
- Уловы с 3+ жалобами не отображаются в лентах (feed) для других пользователей
- Админы могут просматривать все жалобы и управлять ими

## Ограничения

- Один пользователь может подать только **одну жалобу** на один улов
- Пользователь **не может** жаловаться на свой собственный улов
- Описание жалобы должно содержать **от 10 до 1000 символов**
- Редактировать и удалять уловы может только **автор улова**




