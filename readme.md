# ShortLinks — Сокращатель ссылок

Приложение для создания коротких ссылок с панелью управления на Filament.

Проект задеплоен для наглядной демонстрации по адресу:
http://185.68.246.39/ 
---

# 🚀 Быстрый старт (Docker)

## 1. Клонирование репозитория

```bash
# Команда клонирования репозитория
git clone https://github.com/YaBoyCaptainWeeb/LinkShortenerLaravel.git
cd <PROJECT_FOLDER>
```

---

## 2. Создание `.env` файла

```bash
cp .env.example .env
```

Откройте `.env` и настройте следующие параметры:

```env
APP_NAME=ShortLinks
APP_ENV=local
APP_DEBUG=true
APP_URL=http://YOUR_IP

LOG_CHANNEL=stack

DB_CONNECTION=sqlite

SESSION_DRIVER=database

CACHE_STORE=database
QUEUE_CONNECTION=database
```

### Важные моменты

- `APP_URL` — укажите ваш IP или домен (без trailing slash)
- `DB_CONNECTION=sqlite` — для SQLite базы
- `SESSION_DRIVER=file` — для SQLite (или `database` для MySQL)

---

## 3. Создание SQLite базы данных

```bash
mkdir -p database
touch database/database.sqlite
```

---

## 4. Запуск контейнеров

```bash
docker compose up -d --build
```

Дождитесь сборки (5–10 минут).

---

## 5. Установка зависимостей

```bash
docker compose exec app composer install
docker compose exec app npm install
docker compose exec app npm run build
```

---

## 6. Публикация ассетов Livewire

```bash
docker compose exec app php artisan livewire:publish --assets
```

---

## 7. Настройка прав доступа

```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

---

## 8. Генерация ключа приложения

```bash
docker compose exec app php artisan key:generate
```

---

## 9. Запуск миграций

```bash
docker compose exec app php artisan migrate
```

---

## 10. Очистка и кэширование конфигурации

```bash
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:cache
```

---

## 11. Перезапуск контейнеров

```bash
docker compose restart
```

---

# 🌐 Доступ к приложению

Откройте в браузере:

```text
http://ТВОЙ_IP
```

### Основные маршруты

- Регистрация: `/register`
- Авторизация `/login`
- Панель управления: `/panel/links`

---

# 📁 Структура Docker файлов

В репозитории уже находятся:

- `Dockerfile` — образ PHP 8.4 с необходимыми расширениями
- `docker-compose.yml` — конфигурация контейнеров (`app + nginx`)
- `docker/nginx/default.conf` — конфигурация Nginx

Вы можете модифицировать эти файлы под свои нужды.

---

# 🔄 Обновление приложения

```bash
git pull

docker compose exec app composer install
docker compose exec app npm install
docker compose exec app npm run build

docker compose exec app php artisan migrate

docker compose restart
```

---

# 🛠️ Полезные команды

```bash
# Просмотр логов
docker compose logs -f

# Подключение к контейнеру
docker compose exec app bash

# Перезапуск контейнеров
docker compose restart

# Остановка контейнеров
docker compose down

# Полная пересборка
docker compose up -d --build
```

---

# ⚠️ Возможные проблемы

## Ошибка 403 после регистрации

Проверьте:

- `SESSION_DRIVER` в `.env` (должен быть `file` для SQLite)
- Папка `storage/framework/sessions` имеет права на запись

---

## Ошибка 405 Method Not Allowed

Проверьте:

- Опубликованы ли ассеты Livewire (шаг 6)
- Права на `public/vendor/livewire`

---

## Ошибка базы данных

Проверьте:

- Файл `database/database.sqlite` существует
- В `.env` указано:

```env
DB_CONNECTION=sqlite
```

Для SQLite не указывайте:

- `DB_HOST`
- `DB_PORT`
- `DB_USERNAME`
- `DB_PASSWORD`

---

## Проблемы с правами доступа

Если возникают ошибки прав, выполните:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```
---
