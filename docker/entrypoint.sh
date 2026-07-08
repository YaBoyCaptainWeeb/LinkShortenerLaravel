#!/bin/sh
set -e

echo "[entrypoint] Fixing permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

chmod 755 /var/www/html/public
find /var/www/html/public -type d -exec chmod 755 {} \;
find /var/www/html/public -type f -exec chmod 644 {} \;

echo "[entrypoint] Creating storage structure..."
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/testing
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

#echo "[entrypoint] Publishing package assets..."
#php artisan livewire:publish --assets 2>/dev/null || true
#php artisan filament:assets 2>/dev/null || true

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
