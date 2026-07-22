#!/bin/sh
set -e

echo "[entrypoint] Starting container setup..."

echo "[entrypoint] Fixing permissions for storage and bootstrap/cache..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

echo "[entrypoint] Setting correct permissions for public directory..."
find /var/www/html/public -type d -exec chmod 755 {} \;
find /var/www/html/public -type f -exec chmod 644 {} \;

echo "[entrypoint] Ensuring storage structure exists..."
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/testing
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

echo "[entrypoint] Publishing package assets..."
php artisan livewire:publish --assets || echo "[entrypoint] Livewire publish failed (package may not be installed)"
php artisan filament:assets || echo "[entrypoint] Filament publish failed (package may not be installed)"
php artisan storage:link || echo "[entrypoint] Storage link failed (may already exist)"
php artisan migrate || echo "[entrypoint] Nothing to migrate"

echo "[entrypoint] Checking APP_KEY..."
if [ -z "$(grep '^APP_KEY=.\+' /var/www/html/.env 2>/dev/null)" ]; then
    echo "[entrypoint] APP_KEY is empty, generating..."
    php artisan key:generate --force
else
    echo "[entrypoint] APP_KEY already set, skipping generation."
fi

echo "[entrypoint] Clearing existing caches..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

if [ "$APP_ENV" = "production" ]; then
    echo "[entrypoint] Caching configuration..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
else
    echo "[entrypoint] Dev mode: skipping cache generation"
fi

echo "[entrypoint] Setup completed. Starting php-fpm..."
exec php-fpm
