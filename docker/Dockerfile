FROM --platform=$BUILDPLATFORM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM php:8.4-fpm AS php-base
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl libcurl4-openssl-dev libpng-dev libonig-dev libxml2-dev libzip-dev libicu-dev zip unzip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql mbstring exif pcntl bcmath curl dom gd zip intl opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

FROM php-base AS backend

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

FROM php-base AS app-base

WORKDIR /var/www/html

COPY . .
COPY --from=backend /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

RUN echo "[build] Creating storage structure..." \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/testing \
    && mkdir -p storage/logs \
    && mkdir -p storage/app/public \
    && mkdir -p bootstrap/cache \
    && php artisan package:discover --ansi \
    && php artisan livewire:publish --assets \
    && php artisan filament:assets \
    && rm -rf /opt/public \
    && cp -a public /opt/public \
    && echo "[build] Setting permissions..." \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && find public -type d -exec chmod 755 {} \; \
    && find public -type f -exec chmod 644 {} \; \
    && echo "[build] Build completed successfully"

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh \
    && sed -ri \
        -e 's!^pm = .*!pm = ondemand!' \
        -e 's!^pm.max_children = .*!pm.max_children = 2!' \
        -e 's!^;?pm.process_idle_timeout = .*!pm.process_idle_timeout = 10s!' \
        -e 's!^;?pm.max_requests = .*!pm.max_requests = 300!' \
        /usr/local/etc/php-fpm.d/www.conf \
    && { \
        echo 'memory_limit=256M'; \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=0'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.revalidate_freq=0'; \
    } > /usr/local/etc/php/conf.d/zz-shortlinks.ini

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]

FROM app-base AS development
RUN apt-get update && apt-get install -y --no-install-recommends \
        libsqlite3-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_sqlite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

FROM app-base AS production
