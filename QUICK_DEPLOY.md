# 🚀 Быстрый запуск автоматического развертывания FishTrackPro

## Варианты развертывания

### 1. 🎯 Webhook развертывание (рекомендуется)

**Самый безопасный и удобный способ**

```bash
# 1. Настройте сервер
chmod +x scripts/setup-server.sh
./scripts/setup-server.sh

# 2. Настройте GitHub webhook
chmod +x scripts/setup-github-webhook.sh
./scripts/setup-github-webhook.sh

# 3. Готово! Теперь каждый push в main автоматически развертывает приложение
```

### 2. 🔑 Прямое SSH развертывание

**Для простых случаев**

```bash
# 1. Настройте сервер
chmod +x scripts/setup-server.sh
./scripts/setup-server.sh

# 2. Добавьте секреты в GitHub:
# - HOST: IP вашего сервера
# - USERNAME: имя пользователя
# - SSH_KEY: ваш приватный SSH ключ
# - PORT: 22

# 3. Настройте SSH доступ
ssh-copy-id username@server_ip
```

## Что происходит при развертывании

1. **Тестирование** - запускаются все тесты
2. **Сборка** - собирается frontend
3. **Развертывание** - код обновляется на сервере
4. **Миграции** - обновляется база данных
5. **Кэширование** - очищается и обновляется кэш
6. **Перезапуск** - перезапускаются сервисы

## Мониторинг

```bash
# Проверка состояния
curl https://your-domain.com/health

# Логи развертывания
tail -f /var/log/nginx/access.log
journalctl -u php8.2-fpm -f
```

## Быстрые команды

```bash
# Ручное развертывание
/usr/local/bin/deploy-fishtrackpro

# Проверка статуса сервисов
sudo systemctl status nginx php8.2-fpm mysql redis-server

# Резервная копия
mysqldump -u fishtrackpro -p fishtrackpro > backup.sql
```

## Требования

- **Сервер**: Ubuntu 20.04+ с 2GB RAM
- **Домен**: для SSL сертификата
- **GitHub**: репозиторий с правами на настройку webhook

## Поддержка

- 📖 Полная документация: `DEPLOYMENT_GUIDE.md`
- 🐛 Проблемы: создавайте issues в GitHub
- 💬 Вопросы: используйте discussions

---

**Готово! Ваше приложение теперь развертывается автоматически при каждом push в main ветку! 🎉**
