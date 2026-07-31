#!/bin/sh
set -e

cd /var/www/html

echo "[entrypoint] Preparing Laravel directories..."
mkdir -p \
    storage/app/public \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/framework/testing \
    storage/logs \
    bootstrap/cache

if [ "${APP_ENV:-production}" != "production" ] && [ -d /opt/public ]; then
    if [ ! -f public/index.php ]; then
        echo "[entrypoint] Initializing dev public volume from the image..."
        cp -a /opt/public/. public/
    else
        echo "[entrypoint] Dev public volume is already initialized; preserving it."
    fi

    sed -ri 's/^opcache.validate_timestamps=.*/opcache.validate_timestamps=1/' \
        /usr/local/etc/php/conf.d/zz-shortlinks.ini
fi

if [ "${DB_CONNECTION:-}" = "sqlite" ] \
    && [ -n "${DB_DATABASE:-}" ] \
    && [ "${DB_DATABASE}" != ":memory:" ]; then
    echo "[entrypoint] Preparing SQLite database..."
    mkdir -p "$(dirname "${DB_DATABASE}")"
    touch "${DB_DATABASE}"
fi

echo "[entrypoint] Setting writable directory permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
find public -type d -exec chmod 755 {} \;
find public -type f -exec chmod 644 {} \;

if [ -z "${APP_KEY:-}" ] && ! grep -q '^APP_KEY=.\+' .env 2>/dev/null; then
    echo "[entrypoint] APP_KEY is empty; generating a persistent key..."
    php artisan key:generate --force
else
    echo "[entrypoint] APP_KEY is already configured."
fi

echo "[entrypoint] Clearing runtime caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan storage:link || echo "[entrypoint] Storage link already exists"

echo "[entrypoint] Applying database migrations..."
migration_attempt=1
migration_attempts=10

until php artisan migrate --force; do
    if [ "$migration_attempt" -ge "$migration_attempts" ]; then
        echo "[entrypoint] Migration failed after ${migration_attempts} attempts." >&2
        exit 1
    fi

    echo "[entrypoint] Database migration failed; retrying in 3 seconds (${migration_attempt}/${migration_attempts})..."
    migration_attempt=$((migration_attempt + 1))
    sleep 3
done

if [ "${APP_ENV:-production}" = "production" ]; then
    echo "[entrypoint] Building production caches..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    echo "[entrypoint] Dev mode: skipping cache generation"
fi

echo "[entrypoint] Starting PHP-FPM..."
exec php-fpm
