FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM php:8.4-cli AS backend
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip libicu-dev libzip-dev \
    && docker-php-ext-install intl zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

FROM php:8.4-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev libicu-dev zip unzip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp*

WORKDIR /var/www/html

COPY . .
COPY --from=backend /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

RUN php artisan livewire:publish --assets 2>/dev/null || true
RUN php artisan filament:assets 2>/dev/null || true
RUN php artisan storage:link 2>/dev/null || true

RUN mkdir -p storage/framework/sessions \
             storage/framework/views \
             storage/framework/cache \
             storage/framework/testing \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
