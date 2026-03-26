# 🚀 Деплой GreenMetric (Elios) на Render

## 📋 Шаг 1: Подготовка репозитория

1. **Создайте GitHub репозиторий** (если ещё не создан):
   ```bash
   cd GreenMetric
   git init
   git add .
   git commit -m "Initial commit - Render ready"
   ```

2. **Запушьте код на GitHub**:
   ```bash
   git branch -M master
   git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git
   git push -u origin master
   ```

---

## 📋 Шаг 2: Регистрация на Render

1. Перейдите на [render.com](https://render.com/)
2. Зарегистрируйтесь через GitHub
3. После входа перейдите в **Dashboard**

---

##  Шаг 3: Создание базы данных

1. В Dashboard нажмите **New +** → **PostgreSQL**
2. Заполните:
   - **Name**: `elios-db`
   - **Database**: `elios`
   - **Plan**: **Free**
3. Нажмите **Create Database**
4. **Сохраните credentials** (понадобятся для ENV переменных)

---

## 📋 Шаг 4: Создание веб-сервиса (Docker)

1. В Dashboard нажмите **New +** → **Web Service**
2. Подключите ваш GitHub репозиторий
3. Заполните настройки:

   | Поле | Значение |
   |------|----------|
   | **Name** | `elios-backend` |
   | **Region** | Frankfurt (Germany) |
   | **Branch** | `master` |
   | **Root Directory** | `GreenMetric` |
   | **Runtime** | **Docker** |

4. **Plan**: Выберите **Free**
5. Нажмите **Create Web Service**

---

## 📋 Шаг 5: Настройка переменных окружения

После создания сервиса перейдите в **Environment** и добавьте:

```
APP_NAME=Elios
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
BCRYPT_ROUNDS=12
LOG_CHANNEL=stderr
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_HOST=<host-from-database>
DB_PORT=5432
DB_DATABASE=elios
DB_USERNAME=<user-from-database>
DB_PASSWORD=<password-from-database>

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_MAILER=log
VITE_APP_NAME=Elios
```

> ⚠️ **Важно**: Замените `<host-from-database>`, `<user-from-database>`, `<password-from-database>` на значения из вашей PostgreSQL базы данных Render (возьмите из Dashboard базы данных).

---

## 📋 Шаг 6: Деплой

1. После добавления переменных сервис автоматически перезапустится
2. Дождитесь сборки Docker-образа (5-10 минут)
3. После успешного деплоя сервис будет доступен по URL: `https://elios-backend.onrender.com`

---

## 🔧 Дополнительные команды

### Генерация APP_KEY (локально):
```bash
php artisan key:generate
```

### Запуск миграций вручную:
```bash
php artisan migrate --force
```

### Очистка кэша:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## ⚠️ Важные замечания

### Free тариф Render:
- ⏱️ **Авто-засыпание**: Сервис засыпает после 15 минут без активности
- 🐌 **Первый запуск**: После "пробуждения" первый запрос обрабатывается ~30-50 секунд
- 📊 **Лимиты**: 750 часов/месяц бесплатно (хватит на один сервис 24/7)

### База данных:
- 🗄️ Free PostgreSQL удаляется через 90 дней неактивности
- 💾 Делайте бэкапы через Dashboard Render

### Для продакшена рекомендуется:
- ✅ Включить HTTPS (автоматически на Render)
- ✅ Настроить домен (Custom Domain в настройках сервиса)
- ✅ Добавить мониторинг и алерты

---

## 🆘 Решение проблем

### Ошибка сборки Docker:
```
build failed: exit status 1
```
**Решение**: Проверьте логи сборки. Убедитесь, что `Dockerfile` и `apache.conf` существуют.

### Ошибка миграции:
```
SQLSTATE[08006] connection refused
```
**Решение**: Проверьте переменные окружения базы данных (DB_HOST, DB_USERNAME, DB_PASSWORD).

### Frontend не загружается:
```
404 Not Found - assets
```
**Решение**: Убедитесь, что `npm run build` выполнился успешно в Dockerfile. Проверьте логи сборки.

### Ошибка 500:
```
Internal Server Error
```
**Решение**: 
1. Проверьте логи в Dashboard Render
2. Убедитесь, что `APP_KEY` сгенерирован (добавьте в Environment вручную)
3. Проверьте права доступа к `storage/` и `bootstrap/cache/`

---

## 📞 Поддержка

Документация Render: https://render.com/docs
