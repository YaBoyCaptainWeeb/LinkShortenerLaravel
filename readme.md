# LinkShortener

A self-hosted URL shortener built with Laravel. Users can create short links, collect click statistics, and serve Open
Graph previews for social media crawlers from a Filament-based panel.

Live demo: [lnkshrt.xyz](https://lnkshrt.xyz/)

This document is available in:

- [English](#english)
- [Русский](#русский)

---

# English

## Overview

Main features:

- user registration, authentication, password reset, and two-factor authentication (WIP);
- per-user management of short links;
- random Base62 short codes;
- click counters with IP address, user agent, and timestamp history;
- Open Graph metadata collection and previews for social media crawlers;
- English and Russian interface translations (WIP);
- HTTP or HTTPS deployment with Nginx and Docker Compose.

### Stack

- PHP 8.4 Docker runtime, Laravel 13;
- Filament 3 and Livewire;
- Laravel Fortify;
- MySQL/MariaDB for production, SQLite for development;
- Tailwind CSS 4 and Vite 8;
- Nginx 1.28 Alpine;
- multi-stage Docker build with separate `production` and `development` targets.

## Production vs development

| Mode             | Production                     | Development                                     |
|------------------|--------------------------------|-------------------------------------------------|
| Compose file     | `docker/docker-compose.yml`    | `docker/docker-compose.dev.yml`                 |
| Image            | `shortlinks:latest-production` | `shortlinks:dev`                                |
| Build target     | `production`                   | `development`                                   |
| Database         | External MySQL/MariaDB         | `storage-dev/database.sqlite`                   |
| Application code | Stored in the image            | Bind-mounted from the host                      |
| Laravel caches   | Generated at startup           | Disabled                                        |
| Main purpose     | Stable public deployment       | Development, staging, or a remote editable demo |

Production is recommended for any public service. Its code and Composer dependencies are immutable until a new image is
built.

Development can also run on a remote HTTP or HTTPS server. PHP, Blade, route, and configuration changes from the host
project directory are visible without rebuilding the image. Composer dependency changes and frontend changes under
`resources/`
still require an image rebuild. A public development deployment is less safe: the source is mutable, SQLite has limited
write concurrency, and `APP_DEBUG=true` may expose sensitive details. Use it only when those trade-offs are intentional.

Both variants can run simultaneously when they use separate project directories, domains, ports, and `.env` files.
Compose project names, networks, public volumes, storage directories, databases, and session cookies are already
separated.

## Requirements

- Git;
- Docker Engine with Docker Compose v2;
- for production: a database reachable from the application container;
- optional: a domain and TLS certificate/key when HTTPS terminates in the bundled Nginx;
- a Linux Docker host is recommended for deployment.

Runtime requirements depend on traffic, database load, and enabled application features. Image builds need considerably
more temporary CPU, RAM, and disk space than running containers. On a resource-constrained host, build on another
machine and transfer the images. When building on the target host, keep enough free disk space and swap, and monitor the
system with `free -h`, `df -h`, and `docker stats`.

## Environment configuration

Clone the repository and create the local environment file:

```bash
git clone https://github.com/YaBoyCaptainWeeb/LinkShortenerLaravel.git
cd LinkShortenerLaravel
cp .env.example .env
```

The full repository is required on the machine that builds an image and on any development host. A target server that
only runs an already-built production image can instead use the minimal runtime directory described in production option
A.

The same root `.env` file has two purposes:

1. Laravel reads application settings from it.
2. Docker Compose reads ports and, in HTTPS mode, host certificate paths from it.

Always pass it explicitly before the Compose command:

```bash
docker compose --env-file .env -f docker/docker-compose.yml config
```

`--env-file` is a global Compose option, so it must appear before `up`, `exec`, `logs`, or another command.

Leave `APP_KEY=` empty before the first start. The application entrypoint runs `php artisan key:generate --force` once
and writes the generated key to the host `.env`. The file is mounted read-write for this initialization. Later starts
detect and reuse the existing key.

Keep `.env` persistent and backed up. Do not empty or change `APP_KEY` after deployment: Laravel uses it to encrypt
protected data and cookies.

### Production `.env` for HTTPS

At minimum, review these values:

```dotenv
APP_NAME=LinkShortener
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://example.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=host.docker.internal
DB_PORT=3306
DB_DATABASE=shortlinks
DB_USERNAME=shortlinks
DB_PASSWORD=REPLACE_WITH_A_STRONG_PASSWORD

SESSION_DRIVER=database
SESSION_COOKIE=shortlinks_session
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=sync

PROD_HTTPS_PORT=443
PROD_NGINX_PORT=443
PROD_SSL_CERTIFICATE=/absolute/host/path/fullchain.pem
PROD_SSL_CERTIFICATE_KEY=/absolute/host/path/privkey.pem
```

`PROD_HTTPS_PORT` is the public host port. `PROD_NGINX_PORT` is the port inside the Nginx container. Certificate paths
must be absolute paths on the VPS. Compose mounts them inside Nginx as `/etc/nginx/ssl/fullchain.pem` and
`/etc/nginx/ssl/privkey.pem`.

The production Compose file maps `host.docker.internal` to the Linux host gateway. This is intended for MySQL installed
directly on the Docker host. A remote MySQL server can be used by changing `DB_HOST`.

**Database choice.** Eloquent supports MariaDB, MySQL, PostgreSQL, SQLite, and SQL Server. The current image is ready
for MySQL/MariaDB and SQLite: SQLite is convenient for development, tests, and small single-instance deployments, while
MySQL/MariaDB is the simplest production choice for this repository. PostgreSQL is useful for more complex queries and
high write concurrency, but it requires adding `pdo_pgsql`; SQL Server also requires additional drivers. The project
does not include a database container.

Use a dedicated database user and allow connections from the Compose bridge, preferably only from its subnet. Use
`host.docker.internal` for a database on the Docker host or a private IP/DNS name for a remote database. Do not expose
the database publicly, and back it up with its native tools.

## Production deployment

### Option A: build locally and transfer the images (recommended for a resource-constrained host)

Build for the target architecture. For a typical x86-64 VPS, including builds made on Apple Silicon:

```bash
docker build --platform linux/amd64 --target production -t shortlinks:latest-production -f docker/Dockerfile .
docker pull --platform linux/amd64 nginx:1.28-alpine
```

Verify the application architecture:

```bash
docker image inspect shortlinks:latest-production --format '{{.Architecture}}'
```

The expected result is `amd64`.

Export both images used by production:

```bash
docker save shortlinks:latest-production nginx:1.28-alpine | gzip > shortlinks-production-amd64.tar.gz
scp shortlinks-production-amd64.tar.gz root@VPS_IP:/tmp/
```

The target server does not need a full repository checkout. For an imported production image, this minimal runtime
layout is sufficient:

```text
/var/www/LinkShortenerLaravel/
├── .env
├── storage/
└── docker/
    ├── docker-compose.yml
    └── nginx/
        └── default.conf
```

Copy `docker-compose.yml` and `default.conf` from the same project revision used to build the image. Keep `.env` and
`storage/` persistent and never replace them with files from the image archive. Certificate files may be stored
elsewhere because `.env` contains their absolute host paths.

The host does not need `bootstrap/`, application source code, `vendor/`, `Dockerfile`, or `entrypoint.sh`. They are
already stored in the application image. The embedded entrypoint creates `bootstrap/cache` and Laravel's subdirectories
inside the mounted `storage/`; creating the persistent root directory explicitly before the first start is still
recommended.

On the VPS:

```bash
cd /var/www/LinkShortenerLaravel
mkdir -p storage
docker load < /tmp/shortlinks-production-amd64.tar.gz
docker compose --env-file .env -f docker/docker-compose.yml config
docker compose --env-file .env -f docker/docker-compose.yml up -d --no-build --wait --wait-timeout 120
```

`config` validates required variables before changing containers. `--wait` waits for the application and Nginx
healthchecks. The `init_public` container is expected to finish with exit code `0`; it refreshes the named public volume
from the application image.

### Option B: build directly on the target machine

This requires a full repository checkout because Docker uses the project as its build context. It is simpler but slower
and needs more temporary RAM and disk space:

```bash
cd /var/www/LinkShortenerLaravel
docker build --target production -t shortlinks:latest-production -f docker/Dockerfile .
docker pull nginx:1.28-alpine
docker compose --env-file .env -f docker/docker-compose.yml up -d --no-build --wait --wait-timeout 120
```

On a resource-constrained server, keep swap available and check free disk space before building. An external build is
preferable when the same host is already serving the application and database.

### Option C: deploy over plain HTTP without a certificate

This option changes how Nginx accepts traffic; it can be combined with either build method A or B. Laravel itself does
not require a certificate. Use HTTP directly for a private network or testing, or place the container behind an external
reverse proxy that provides public HTTPS. Do not use unencrypted public HTTP for real accounts: credentials, cookies,
and application data can be intercepted in transit.

Set the public HTTP address and port in `.env`:

```dotenv
APP_URL=http://example.com
SESSION_SECURE_COOKIE=false
PROD_HTTP_PORT=80
```

In the `nginx` service of `docker/docker-compose.yml`, replace the `environment`, `ports`, and `healthcheck.test`
settings with:

```yaml
environment:
    NGINX_LISTEN: "80"
    NGINX_SSL_CERTIFICATE: ""
    NGINX_SSL_CERTIFICATE_KEY: ""
ports:
    - "${PROD_HTTP_PORT:-80}:80"
healthcheck:
    test: wget -q --spider http://127.0.0.1:80/healthz || exit 1
```

Keep the other healthcheck options (`interval`, `timeout`, `retries`, and `start_period`) unchanged.
`PROD_HTTP_PORT` is the port on the Docker host; port `80` after the colon is the port inside the container. If host
port 80 is occupied, choose another free port and include it in `APP_URL`, for example
`APP_URL=http://example.com:8080`.

Remove both certificate bind mounts from the same service: the entries whose targets are
`/etc/nginx/ssl/fullchain.pem` and `/etc/nginx/ssl/privkey.pem`. The `PROD_SSL_CERTIFICATE`,
`PROD_SSL_CERTIFICATE_KEY`, `PROD_HTTPS_PORT`, and `PROD_NGINX_PORT` values are no longer needed for this HTTP
configuration.

After completing build option A or B, validate and start the stack:

```bash
docker compose --env-file .env -f docker/docker-compose.yml config
docker compose --env-file .env -f docker/docker-compose.yml up -d --no-build --wait --wait-timeout 120
curl http://127.0.0.1:80/healthz
```

If another reverse proxy on the same host provides HTTPS, bind this Nginx only to the loopback interface:

```dotenv
PROD_HTTP_PORT=8080
```

```yaml
ports:
    - "127.0.0.1:${PROD_HTTP_PORT}:80"
```

In that case, use the public HTTPS URL in Laravel (`APP_URL=https://example.com`) and keep
`SESSION_SECURE_COOKIE=true`. Configure the external proxy to forward requests to `http://127.0.0.1:8080`.

### Production updates

Build and transfer a new image using the same tag. If the VPS contains a full repository checkout, update it to the same
revision and run:

```bash
cd /var/www/LinkShortenerLaravel
git pull --ff-only
docker load < /tmp/shortlinks-production-amd64.tar.gz
docker compose --env-file .env -f docker/docker-compose.yml config
docker compose --env-file .env -f docker/docker-compose.yml up -d --no-build --force-recreate --wait --wait-timeout 120
```

With the minimal runtime layout, there is no repository to pull. Copy new versions of
`docker/docker-compose.yml` and `docker/nginx/default.conf` only when they changed in the revision used for the image,
then run:

```bash
cd /var/www/LinkShortenerLaravel
docker load < /tmp/shortlinks-production-amd64.tar.gz
docker compose --env-file .env -f docker/docker-compose.yml config
docker compose --env-file .env -f docker/docker-compose.yml up -d --no-build --force-recreate --wait --wait-timeout 120
```

The entrypoint automatically applies migrations, recreates Laravel production caches, and starts PHP-FPM. Do not delete
`storage`, `shortlinks_prod_public`, or the MySQL database during a normal update.

Keep VPS-specific values in the untracked `.env`, not as edits to Compose files. This keeps `git pull --ff-only`
predictable.

## Development deployment

Development uses SQLite and the source code from the host project directory. The entrypoint creates
`storage-dev/database.sqlite` and runs migrations automatically.

Example development `.env` values:

```dotenv
APP_NAME="LinkShortener Dev"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=https://dev.example.com

DEV_HTTPS_PORT=2087
DEV_NGINX_PORT=443
DEV_SSL_CERTIFICATE=/absolute/host/path/fullchain.pem
DEV_SSL_CERTIFICATE_KEY=/absolute/host/path/privkey.pem
```

The Compose file overrides database, cache, queue, and session storage to use the isolated SQLite database. It also
forces a separate `shortlinks_dev_session` cookie.

### Option A: build locally and transfer the images

Build locally for an x86-64 VPS:

```bash
docker build --platform linux/amd64 --target development -t shortlinks:dev -f docker/Dockerfile .
docker pull --platform linux/amd64 nginx:1.28-alpine
docker save shortlinks:dev nginx:1.28-alpine | gzip > shortlinks-development-amd64.tar.gz
scp shortlinks-development-amd64.tar.gz root@VPS_IP:/tmp/
```

On the VPS, a full project checkout is required because the source is bind-mounted:

```bash
cd /var/www/LinkShortenerLaravel_dev
mkdir -p storage-dev
docker load < /tmp/shortlinks-development-amd64.tar.gz
docker compose --env-file .env -f docker/docker-compose.dev.yml config
docker compose --env-file .env -f docker/docker-compose.dev.yml up -d --no-build --wait --wait-timeout 120
```

### Option B: build directly on the target machine

This requires more temporary resources, but no image archive needs to be transferred:

```bash
cd /var/www/LinkShortenerLaravel_dev
mkdir -p storage-dev
docker build --target development -t shortlinks:dev -f docker/Dockerfile .
docker pull nginx:1.28-alpine
docker compose --env-file .env -f docker/docker-compose.dev.yml config
docker compose --env-file .env -f docker/docker-compose.dev.yml up -d --no-build --wait --wait-timeout 120
```

### Option C: run development over plain HTTP

This can be combined with build option A or B. In the development `.env`, use:

```dotenv
APP_URL=http://dev.example.com:8080
SESSION_SECURE_COOKIE=false
DEV_HTTP_PORT=8080
```

In the `nginx` service of `docker/docker-compose.dev.yml`, replace its `environment` and `ports` settings with:

```yaml
environment:
    NGINX_LISTEN: "80"
    NGINX_SSL_CERTIFICATE: ""
    NGINX_SSL_CERTIFICATE_KEY: ""
ports:
    - "${DEV_HTTP_PORT:-8080}:80"
```

Remove the two certificate bind mounts from that service. The certificate variables, `DEV_HTTPS_PORT`, and
`DEV_NGINX_PORT` are then unnecessary. `DEV_HTTP_PORT` is the port on the host, while port `80` after the colon belongs
to the container. After completing build option A or B, run:

```bash
docker compose --env-file .env -f docker/docker-compose.dev.yml config
docker compose --env-file .env -f docker/docker-compose.dev.yml up -d --no-build --wait --wait-timeout 120
curl http://127.0.0.1:8080/healthz
```

To put development behind an HTTPS reverse proxy on the same host, bind the port as
`127.0.0.1:${DEV_HTTP_PORT}:80`, set the public `APP_URL=https://dev.example.com`, and use
`SESSION_SECURE_COOKIE=true`.

For ordinary PHP, Blade, route, or configuration changes on the VPS, update the files or run `git pull --ff-only`; the
bind mount exposes them to the running application. A restart is normally unnecessary because development enables
OPcache timestamp validation.

Rebuild the development image when any of these change:

- `composer.json` or `composer.lock`;
- `package.json` or `package-lock.json`;
- frontend assets under `resources/`;
- `docker/Dockerfile` or files copied into the image.

After loading a rebuilt development image:

```bash
docker compose --env-file .env -f docker/docker-compose.dev.yml up -d --no-build --force-recreate --renew-anon-volumes --wait --wait-timeout 120
```

`--renew-anon-volumes` refreshes the image-provided Composer `vendor` directory. Restarting dev also refreshes its
public named volume from the image.

## Operations and diagnostics

Production examples:

```bash
docker compose --env-file .env -f docker/docker-compose.yml ps
docker compose --env-file .env -f docker/docker-compose.yml logs app --tail 100
docker compose --env-file .env -f docker/docker-compose.yml logs nginx --tail 100
docker compose --env-file .env -f docker/docker-compose.yml logs init_public
docker compose --env-file .env -f docker/docker-compose.yml exec app sh
docker stats shortlinks_app shortlinks_nginx
curl -k https://127.0.0.1:443/healthz
```

Development uses the same commands with `docker/docker-compose.dev.yml`. If the Compose variables are temporarily
unavailable but the container is already running, direct execution is possible:

```bash
docker exec -it shortlinks_app_dev sh
```

Docker healthchecks report `starting`, `healthy`, or `unhealthy` in `docker compose ps`. They control startup ordering
and make `up --wait` useful. An `unhealthy` status alone does not restart a still-running process; inspect logs and
recreate the affected service after fixing the cause.

---

# Русский

## О проекте

LinkShortener — самостоятельно разворачиваемый сервис сокращения ссылок на Laravel. Пользователь может создавать
короткие ссылки, смотреть статистику переходов и автоматически формировать Open Graph-превью для социальных сетей через
панель на Filament.

Основные возможности:

- регистрация, авторизация, сброс пароля и двухфакторная аутентификация (WIP);
- отдельный список ссылок для каждого пользователя;
- случайные короткие Base62-коды;
- счётчик переходов с историей IP-адресов, User-Agent и времени;
- получение Open Graph-метаданных целевой страницы;
- английская и русская локализация (WIP);
- HTTP- или HTTPS-развёртывание через Nginx и Docker Compose.

### Стек

- PHP 8.4 в Docker, Laravel 13;
- Filament 3 и Livewire;
- Laravel Fortify;
- MySQL/MariaDB для production, SQLite для development;
- Tailwind CSS 4 и Vite 8;
- Nginx 1.28 Alpine;
- multi-stage Dockerfile с целями `production` и `development`.

## Различия production и development

| Режим          | Production                     | Development                             |
|----------------|--------------------------------|-----------------------------------------|
| Compose-файл   | `docker/docker-compose.yml`    | `docker/docker-compose.dev.yml`         |
| Образ          | `shortlinks:latest-production` | `shortlinks:dev`                        |
| Build target   | `production`                   | `development`                           |
| База данных    | Внешний MySQL/MariaDB          | `storage-dev/database.sqlite`           |
| Код приложения | Находится внутри образа        | Подключён с Docker-host                 |
| Кэши Laravel   | Создаются при запуске          | Отключены                               |
| Назначение     | Стабильный публичный сервис    | Разработка, staging или изменяемое демо |

Для публичного сервиса рекомендуется production: код и Composer-зависимости не меняются до сборки нового образа.

Development также можно запускать на удалённом сервере по HTTP или HTTPS. Изменения PHP, Blade, маршрутов и конфигурации
на Docker-host видны без пересборки образа. Изменения Composer-зависимостей и frontend-файлов в `resources/` требуют
пересборки. Публичный development менее безопасен: код изменяемый, SQLite хуже переносит параллельную запись, а
`APP_DEBUG=true` может раскрыть чувствительную информацию. Используйте такой режим осознанно.

Оба варианта могут работать одновременно, если находятся в разных каталогах и используют разные домены, порты и `.env`.
Имена Compose-проектов, сети, public volumes, storage, базы и session cookie уже разделены.

## Требования

- Git;
- Docker Engine и Docker Compose v2;
- для production — доступная из контейнера база данных;
- опционально — домен, TLS-сертификат и закрытый ключ, если HTTPS завершается во встроенном Nginx;
- для развёртывания рекомендуется Linux Docker-host.

Требования во время работы зависят от трафика, нагрузки на базу и используемых функций. Сборка образа временно требует
заметно больше CPU, RAM и места, чем запуск готовых контейнеров. Для ограниченного по ресурсам сервера собирайте образ
на другой машине и переносите его. При сборке на целевом хосте заранее проверьте свободное место и swap, а за состоянием
следите командами `free -h`, `df -h` и `docker stats`.

## Настройка окружения

Клонируйте репозиторий и создайте локальный файл окружения:

```bash
git clone https://github.com/YaBoyCaptainWeeb/LinkShortenerLaravel.git
cd LinkShortenerLaravel
cp .env.example .env
```

Полный репозиторий нужен на машине, где собирается образ, и на любом development-хосте. Целевой сервер, который только
запускает заранее собранный production-образ, может использовать минимальный runtime-каталог из production-варианта А.

Корневой `.env` используется сразу в двух местах:

1. Laravel читает из него настройки приложения.
2. Docker Compose получает из него порты и, в режиме HTTPS, пути к сертификатам на хосте.

Передавайте файл явно до команды Compose:

```bash
docker compose --env-file .env -f docker/docker-compose.yml config
```

`--env-file` — глобальный параметр Compose, поэтому он должен находиться перед `up`, `exec`, `logs` или другой командой.

Перед первым запуском оставьте `APP_KEY=` пустым. Entrypoint приложения один раз выполняет
`php artisan key:generate --force` и записывает созданный ключ в хостовый `.env`. Для этой инициализации файл подключён
в режиме чтения и записи. При следующих запусках контейнер находит и повторно использует существующий ключ.

Храните резервную копию `.env`. Не очищайте и не меняйте `APP_KEY` после запуска: Laravel использует его для шифрования
защищённых данных и cookie.

### Production `.env` для HTTPS

Как минимум проверьте следующие значения:

```dotenv
APP_NAME=LinkShortener
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://example.com

APP_LOCALE=ru
APP_FALLBACK_LOCALE=en
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=host.docker.internal
DB_PORT=3306
DB_DATABASE=shortlinks
DB_USERNAME=shortlinks
DB_PASSWORD=ЗАМЕНИТЕ_НА_СЛОЖНЫЙ_ПАРОЛЬ

SESSION_DRIVER=database
SESSION_COOKIE=shortlinks_session
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=sync

PROD_HTTPS_PORT=443
PROD_NGINX_PORT=443
PROD_SSL_CERTIFICATE=/абсолютный/путь/на/хосте/fullchain.pem
PROD_SSL_CERTIFICATE_KEY=/абсолютный/путь/на/хосте/privkey.pem
```

`PROD_HTTPS_PORT` — внешний порт VPS, а `PROD_NGINX_PORT` — порт внутри контейнера Nginx. Пути к сертификатам должны
быть абсолютными путями на VPS. Compose подключает их внутрь Nginx как `/etc/nginx/ssl/fullchain.pem` и
`/etc/nginx/ssl/privkey.pem`.

Production Compose сопоставляет `host.docker.internal` со шлюзом Linux-хоста. Это предназначено для MySQL,
установленного непосредственно на Docker-host. Для удалённого MySQL укажите его адрес в `DB_HOST`.

**Выбор базы данных.** Eloquent поддерживает MariaDB, MySQL, PostgreSQL, SQLite и SQL Server. Текущий образ сразу готов
для MySQL/MariaDB и SQLite: SQLite удобна для разработки, тестов и небольших одиночных установок, а MySQL/MariaDB —
самый простой production-вариант для этого репозитория. PostgreSQL полезна для сложных запросов и высокой конкуренции
записи, но требует добавления `pdo_pgsql`; SQL Server также требует дополнительных драйверов. Контейнер базы данных в
проект не входит.

Используйте отдельного пользователя БД и разрешите подключения из Compose bridge, желательно только из его подсети. Для
БД на Docker-host используйте `host.docker.internal`, для удалённой — приватный IP или DNS-имя. Не открывайте БД в
интернет и создавайте резервные копии её штатными средствами.

## Развёртывание production

### Вариант А: локальная сборка и перенос образов — рекомендуется для ограниченного по ресурсам хоста

Соберите образ под архитектуру целевой машины. Для обычной x86-64 VPS, в том числе при сборке на Apple Silicon:

```bash
docker build --platform linux/amd64 --target production -t shortlinks:latest-production -f docker/Dockerfile .
docker pull --platform linux/amd64 nginx:1.28-alpine
```

Проверьте архитектуру приложения:

```bash
docker image inspect shortlinks:latest-production --format '{{.Architecture}}'
```

Ожидаемый результат — `amd64`.

Экспортируйте оба production-образа:

```bash
docker save shortlinks:latest-production nginx:1.28-alpine | gzip > shortlinks-production-amd64.tar.gz
scp shortlinks-production-amd64.tar.gz root@IP_VPS:/tmp/
```

Полный checkout репозитория на целевом сервере не требуется. Для импортированного production-образа достаточно такой
структуры:

```text
/var/www/LinkShortenerLaravel/
├── .env
├── storage/
└── docker/
    ├── docker-compose.yml
    └── nginx/
        └── default.conf
```

Файлы `docker-compose.yml` и `default.conf` должны быть из той же ревизии проекта, на которой собран образ. `.env` и
`storage/` должны сохраняться между обновлениями — не заменяйте их содержимым архива с образом. Сертификаты могут лежать
в другом месте, поскольку в `.env` указываются их абсолютные пути на хосте.

На хосте не нужны `bootstrap/`, исходный код приложения, `vendor/`, `Dockerfile` и `entrypoint.sh`: они уже находятся
внутри образа. Встроенный entrypoint создаёт `bootstrap/cache` и каталоги Laravel внутри подключённого `storage/`, но
перед первым запуском всё равно рекомендуется явно создать корневой постоянный каталог.

На VPS:

```bash
cd /var/www/LinkShortenerLaravel
mkdir -p storage
docker load < /tmp/shortlinks-production-amd64.tar.gz
docker compose --env-file .env -f docker/docker-compose.yml config
docker compose --env-file .env -f docker/docker-compose.yml up -d --no-build --wait --wait-timeout 120
```

Команда `config` проверяет обязательные переменные до изменения контейнеров. `--wait` ожидает успешные healthchecks
приложения и Nginx. Контейнер `init_public` должен завершиться с кодом `0`: он обновляет именованный public volume из
образа приложения.

### Вариант Б: сборка прямо на целевой машине

Для этого варианта требуется полный checkout репозитория: Docker использует проект как build context. Такой способ
проще, но медленнее и требует больше временной памяти и места:

```bash
cd /var/www/LinkShortenerLaravel
docker build --target production -t shortlinks:latest-production -f docker/Dockerfile .
docker pull nginx:1.28-alpine
docker compose --env-file .env -f docker/docker-compose.yml up -d --no-build --wait --wait-timeout 120
```

На ограниченном по ресурсам сервере оставьте доступным swap и заранее проверьте свободное место. Если тот же хост уже
обслуживает приложение и базу, предпочтительнее собирать образ на другой машине.

### Вариант В: развёртывание по HTTP без сертификата

Этот вариант меняет способ приёма трафика Nginx и сочетается с любым способом сборки А или Б. Самому Laravel сертификат
не нужен. Используйте прямой HTTP в приватной сети или для тестирования либо разместите контейнер за внешним reverse
proxy, который предоставляет публичный HTTPS. Не используйте незашифрованный публичный HTTP для реальных аккаунтов:
логины, cookies и данные приложения могут быть перехвачены при передаче.

Укажите публичный HTTP-адрес и порт в `.env`:

```dotenv
APP_URL=http://example.com
SESSION_SECURE_COOKIE=false
PROD_HTTP_PORT=80
```

В сервисе `nginx` файла `docker/docker-compose.yml` замените настройки `environment`, `ports` и `healthcheck.test`:

```yaml
environment:
    NGINX_LISTEN: "80"
    NGINX_SSL_CERTIFICATE: ""
    NGINX_SSL_CERTIFICATE_KEY: ""
ports:
    - "${PROD_HTTP_PORT:-80}:80"
healthcheck:
    test: wget -q --spider http://127.0.0.1:80/healthz || exit 1
```

Остальные параметры healthcheck (`interval`, `timeout`, `retries` и `start_period`) оставьте без изменений.
`PROD_HTTP_PORT` — порт Docker-host, а `80` после двоеточия — порт внутри контейнера. Если порт 80 на хосте занят,
выберите другой свободный порт и добавьте его в `APP_URL`, например `APP_URL=http://example.com:8080`.

Из этого же сервиса удалите оба bind mount сертификатов — записи с target
`/etc/nginx/ssl/fullchain.pem` и `/etc/nginx/ssl/privkey.pem`. Переменные `PROD_SSL_CERTIFICATE`,
`PROD_SSL_CERTIFICATE_KEY`, `PROD_HTTPS_PORT` и `PROD_NGINX_PORT` в такой конфигурации больше не нужны.

После выполнения варианта сборки А или Б проверьте конфигурацию и запустите контейнеры:

```bash
docker compose --env-file .env -f docker/docker-compose.yml config
docker compose --env-file .env -f docker/docker-compose.yml up -d --no-build --wait --wait-timeout 120
curl http://127.0.0.1:80/healthz
```

Если HTTPS предоставляет другой reverse proxy на том же хосте, откройте Nginx только на loopback-интерфейсе:

```dotenv
PROD_HTTP_PORT=8080
```

```yaml
ports:
    - "127.0.0.1:${PROD_HTTP_PORT}:80"
```

В этом случае укажите публичный HTTPS-адрес (`APP_URL=https://example.com`), оставьте
`SESSION_SECURE_COOKIE=true` и направьте внешний proxy на `http://127.0.0.1:8080`.

### Обновление production

Соберите и перенесите новый образ с тем же тегом. Если на VPS находится полный checkout репозитория, обновите его до той
же ревизии и выполните:

```bash
cd /var/www/LinkShortenerLaravel
git pull --ff-only
docker load < /tmp/shortlinks-production-amd64.tar.gz
docker compose --env-file .env -f docker/docker-compose.yml config
docker compose --env-file .env -f docker/docker-compose.yml up -d --no-build --force-recreate --wait --wait-timeout 120
```

При минимальной runtime-структуре на VPS нечего выполнять через `git pull`. Копируйте новые версии
`docker/docker-compose.yml` и `docker/nginx/default.conf`, только если они изменились в ревизии собранного образа, после
чего выполните:

```bash
cd /var/www/LinkShortenerLaravel
docker load < /tmp/shortlinks-production-amd64.tar.gz
docker compose --env-file .env -f docker/docker-compose.yml config
docker compose --env-file .env -f docker/docker-compose.yml up -d --no-build --force-recreate --wait --wait-timeout 120
```

Entrypoint сам применяет миграции, создаёт production-кэши Laravel и запускает PHP-FPM. При обычном обновлении не
удаляйте `storage`, `shortlinks_prod_public` и базу MySQL.

Храните настройки VPS в неотслеживаемом `.env`, а не как изменения Compose-файлов. Тогда `git pull --ff-only` будет
предсказуемым.

## Развёртывание development

Development использует SQLite и исходный код из каталога проекта на Docker-host. Entrypoint автоматически создаёт
`storage-dev/database.sqlite` и применяет миграции.

Пример значений development `.env`:

```dotenv
APP_NAME="LinkShortener Dev"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=https://dev.example.com

DEV_HTTPS_PORT=2087
DEV_NGINX_PORT=443
DEV_SSL_CERTIFICATE=/абсолютный/путь/на/хосте/fullchain.pem
DEV_SSL_CERTIFICATE_KEY=/абсолютный/путь/на/хосте/privkey.pem
```

Compose переопределяет настройки базы, кэша, очереди и сессий для работы с отдельной SQLite. Также принудительно
используется отдельная cookie `shortlinks_dev_session`.

### Вариант А: локальная сборка и перенос образов

Локальная сборка для x86-64 VPS:

```bash
docker build --platform linux/amd64 --target development -t shortlinks:dev -f docker/Dockerfile .
docker pull --platform linux/amd64 nginx:1.28-alpine
docker save shortlinks:dev nginx:1.28-alpine | gzip > shortlinks-development-amd64.tar.gz
scp shortlinks-development-amd64.tar.gz root@IP_VPS:/tmp/
```

На VPS требуется полный каталог проекта, потому что исходный код подключается bind mount:

```bash
cd /var/www/LinkShortenerLaravel_dev
mkdir -p storage-dev
docker load < /tmp/shortlinks-development-amd64.tar.gz
docker compose --env-file .env -f docker/docker-compose.dev.yml config
docker compose --env-file .env -f docker/docker-compose.dev.yml up -d --no-build --wait --wait-timeout 120
```

### Вариант Б: сборка прямо на целевой машине

Такой способ требует больше временных ресурсов, зато архив с образами переносить не нужно:

```bash
cd /var/www/LinkShortenerLaravel_dev
mkdir -p storage-dev
docker build --target development -t shortlinks:dev -f docker/Dockerfile .
docker pull nginx:1.28-alpine
docker compose --env-file .env -f docker/docker-compose.dev.yml config
docker compose --env-file .env -f docker/docker-compose.dev.yml up -d --no-build --wait --wait-timeout 120
```

### Вариант В: запуск development по HTTP

Этот вариант сочетается со способом сборки А или Б. В development `.env` укажите:

```dotenv
APP_URL=http://dev.example.com:8080
SESSION_SECURE_COOKIE=false
DEV_HTTP_PORT=8080
```

В сервисе `nginx` файла `docker/docker-compose.dev.yml` замените настройки `environment` и `ports`:

```yaml
environment:
    NGINX_LISTEN: "80"
    NGINX_SSL_CERTIFICATE: ""
    NGINX_SSL_CERTIFICATE_KEY: ""
ports:
    - "${DEV_HTTP_PORT:-8080}:80"
```

Удалите из этого сервиса два bind mount сертификатов. После этого переменные сертификатов, `DEV_HTTPS_PORT` и
`DEV_NGINX_PORT` не нужны. `DEV_HTTP_PORT` — порт на хосте, а `80` после двоеточия — порт контейнера. Завершив сборку
способом А или Б, выполните:

```bash
docker compose --env-file .env -f docker/docker-compose.dev.yml config
docker compose --env-file .env -f docker/docker-compose.dev.yml up -d --no-build --wait --wait-timeout 120
curl http://127.0.0.1:8080/healthz
```

Чтобы поставить development за HTTPS reverse proxy на том же хосте, привяжите порт как
`127.0.0.1:${DEV_HTTP_PORT}:80`, укажите публичный `APP_URL=https://dev.example.com` и используйте
`SESSION_SECURE_COOKIE=true`.

Для обычных изменений PHP, Blade, маршрутов и конфигурации на VPS достаточно изменить файлы или выполнить
`git pull --ff-only`: bind mount передаёт их работающему приложению. Перезапуск обычно не нужен, потому что в
development включена проверка времени изменения файлов OPcache.

Пересобирайте development-образ при изменении:

- `composer.json` или `composer.lock`;
- `package.json` или `package-lock.json`;
- frontend-файлов в `resources/`;
- `docker/Dockerfile` или файлов, копируемых в образ.

После загрузки пересобранного development-образа:

```bash
docker compose --env-file .env -f docker/docker-compose.dev.yml up -d --no-build --force-recreate --renew-anon-volumes --wait --wait-timeout 120
```

`--renew-anon-volumes` обновляет поставляемый образом каталог Composer `vendor`. При перезапуске dev также обновляет
свой именованный public volume из образа.

## Эксплуатация и диагностика

Примеры для production:

```bash
docker compose --env-file .env -f docker/docker-compose.yml ps
docker compose --env-file .env -f docker/docker-compose.yml logs app --tail 100
docker compose --env-file .env -f docker/docker-compose.yml logs nginx --tail 100
docker compose --env-file .env -f docker/docker-compose.yml logs init_public
docker compose --env-file .env -f docker/docker-compose.yml exec app sh
docker stats shortlinks_app shortlinks_nginx
curl -k https://127.0.0.1:443/healthz
```

Для development используйте те же команды с `docker/docker-compose.dev.yml`. Если Compose-переменные временно
недоступны, но контейнер уже запущен, можно обратиться к нему напрямую:

```bash
docker exec -it shortlinks_app_dev sh
```

Healthcheck отображает состояния `starting`, `healthy` и `unhealthy` в `docker compose ps`. Проверки управляют порядком
запуска и позволяют использовать `up --wait`. Сам статус `unhealthy` не перезапускает продолжающий работать процесс:
изучите логи и после исправления причины пересоздайте проблемный сервис.
