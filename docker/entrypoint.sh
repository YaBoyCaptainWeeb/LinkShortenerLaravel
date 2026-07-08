#!/bin/sh
set -e

echo "[entrypoint] Fixing permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

echo "[entrypoint] Creating storage structure..."
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

echo "[entrypoint] Running Laravel setup..."
php artisan storage:link --force || true

if [ -z "$(grep '^APP_KEY=.\+' /var/www/html/.env 2>/dev/null)" ]; then
    echo "[entrypoint] APP_KEY is empty, generating..."
    php artisan key:generate --force
else
    echo "[entrypoint] APP_KEY already set, skipping."
fi

echo "[entrypoint] Caching config..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "[entrypoint] Starting php-fpm..."
exec php-fpm
