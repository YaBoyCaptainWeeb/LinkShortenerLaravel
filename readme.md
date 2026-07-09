# LinkShortener — Laravel URL Shortener

Сервис для сокращения ссылок на базе Laravel 11 + Livewire + Filament, упакованный в Docker с multi-stage сборкой.

Проект развернут для наглядной демонстрации по адресу:
http://185.68.246.39/ 
---

# Инструкция

## 1. Клонирование репозитория

```bash
# Команда клонирования репозитория
git clone https://github.com/YaBoyCaptainWeeb/LinkShortenerLaravel.git
```

---
## 2. Сборка проекта
Собранный контейнер полностью работает с файлами внутри образа, поэтому вы можете собрать контейнер
и закинуть в любое место на вашем сервере, главное чтобы были следующие файлы:
- Dockerfile
- docker-compose.yml
- .dockerignore
- .env

Приложение работает с БД(тестировалось на sqlite & mysql), желательно использовать внешнее подключение, дабы при обновлении сборки или ее перезапуске
не происходило потери данных.

> **ВАЖНО**<br>
> Настоятельно рекомендуется НЕ производить сборку с запуском одновременно: важный файл с переменными окружения .env стоит подготовить ДО запуска контейнера, иначе поведение может быть непредсказуемым.<br>
> 1. СНАЧАЛА docker compose <file> build<br>
> 2. Описываем .env (*)<br>
> 3. .env ДОЛЖЕН располагаться в папке вашего проекта, куда вы расположили Docker файлы<br>
> 4. ТОЛЬКО ПОСЛЕ ЭТОГО производим запуск контейнера <br>

```bash
# Сборка проекта
# DOCKER_DEFAULT_PLATFORM - если вдруг вам нужно сделать сборку под конкретную платформу
DOCKER_DEFAULT_PLATFORM=linux/amd64 docker compose -f docker/docker-compose.yml build;  

# Сохранение образа в отдельный файл, если желаете экспортировать образ на другую машину 
docker save linkshortenerlaravel-app:latest | gzip > app.tar.gz  
```
> Сборка занимает примерно **5–15 минут**, так как выполняется:
>
> - composer install
> - npm install
> - npm build
> - установка PHP Extensions

**Шаблон .env (*)**
```env
APP_NAME=shortLinks
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=

APP_LOCALE=ru
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=ru_RU

APP_MAINTENANCE_DRIVER=file

# PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=host.docker.internal
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_STORE=database

VITE_APP_NAME="${APP_NAME}"
```
---
## 3. Подготовка вашего VPS
> **ВАЖНО**<br>
> Заранее установите СУБД и создайте БД перед запуском контейнера<br>

### Пример создания БД с выделенным юзером для вашего приложения
```sql
CREATE DATABASE shortlinks;
CREATE USER 'shortlinks'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON shortlinks.* TO 'shortlinks'@'%';
FLUSH PRIVILEGES;
```
### Подготовка
```bash
# Рекомендуется создать папку под проект в данном месте
mkdir -p /var/www/<project_name>

# В случае импорта образа
docker load < <app.tar.gz path> # загружаем образ 
rm <app.tar.gz path> # Убираемся за собой
```
Убедитесь, что на вашей машине, куда вы импортируете образ имеет необходимую структуру файлов из пункта №2
```bash
# Поднимаем контейнер
# предварительно создайте себе папку под проект
cd <LinkShortenerLaravel path>
docker compose -f docker/docker-compose.yml up -d
```

```bash
# Если же вы решили все делать в рамках одной машины, то после сборки пишем
docker compose -f docker/docker-compose.yml up -d
```
---
# 🔄 Обновление приложения
```bash
# На машине разработки:
git pull
# DOCKER_DEFAULT_PLATFORM - если вдруг вам нужно сделать сборку под конкретную платформу
DOCKER_DEFAULT_PLATFORM=linux/amd64 docker compose -f docker/docker-compose.yml build --no-cache
docker save linkshortenerlaravel-app:latest | gzip > app.tar.gz
scp app.tar.gz root@YOUR_VPS_IP:/tmp/

# На VPS:
docker load < /tmp/app.tar.gz
rm /tmp/app.tar.gz
cd /var/www/LinkShortenerLaravel
docker compose -f docker/docker-compose.yml down
docker volume rm docker_app_public 2>/dev/null || true
docker compose -f docker/docker-compose.yml up -d
docker compose -f docker/docker-compose.yml exec app php artisan migrate --force

# Если сборка и запуск происходит на одной машине
git pull https://github.com/YaBoyCaptainWeeb/LinkShortenerLaravel.git
# DOCKER_DEFAULT_PLATFORM - если вдруг вам нужно сделать сборку под конкретную платформу
DOCKER_DEFAULT_PLATFORM=linux/amd64 docker compose -f docker/docker-compose.yml up -d --build --force-recreate

# Примените новые миграции(если они есть)
docker compose -f docker/docker-compose.yml exec app php artisan migrate

# Очистите кэш
docker compose -f docker/docker-compose.yml exec app php artisan config:clear
docker compose -f docker/docker-compose.yml exec app php artisan route:clear
docker compose -f docker/docker-compose.yml exec app php artisan view:clear
```
---
# 🧹 Удаление проекта
```bash
# Остановить и удалить контейнеры, сети, volumes
docker compose -f docker/docker-compose.yml down --rmi local --volumes --remove-orphans

# Удалить образ
docker rmi linkshortenerlaravel-app:latest 2>/dev/null || true

# Удалить кэш сборки Docker
docker builder prune -f

# Удалить все неиспользуемые образы и volumes
docker system prune -a --volumes -f
```

# 🔍 Диагностика Docker-образа и контейнеров
**Проверка логов**
```bash
# Логи приложения (последние 100 строк)
docker compose -f docker/docker-compose.yml logs app --tail 100

# Логи nginx
docker compose -f docker/docker-compose.yml logs nginx --tail 100

# Логи init-контейнера (копирование public)
docker compose -f docker/docker-compose.yml logs init_public

# Следить за логами в реальном времени
docker compose -f docker/docker-compose.yml logs -f app

# Логи с временными метками
docker compose -f docker/docker-compose.yml logs -t app

# Логи за последние 30 минут
docker compose -f docker/docker-compose.yml logs --since 30m app
```
**Проверка статуса контейнеров**
```bash
# Статус всех контейнеров
docker compose -f docker/docker-compose.yml ps

# Подробная информация о контейнере
docker inspect shortlinks_app

# Статистика использования ресурсов (CPU, RAM)
docker stats shortlinks_app shortlinks_nginx

# Проверить, запущен ли контейнер
docker compose -f docker/docker-compose.yml ps --format "table {{.Name}}\t{{.Status}}\t{{.Ports}}"
```
**Интерактивная оболочка контейнера**
```bash
# Войти в контейнер app (PHP-FPM)
docker compose -f docker/docker-compose.yml exec app sh

# Войти в контейнер nginx
docker compose -f docker/docker-compose.yml exec nginx sh

# Войти в контейнер с root-правами
docker exec -u root -it shortlinks_app sh
```
