FROM node:20-alpine AS fronted
WORKDIR /app
COPY package.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM composer:2.7 AS backend
WORKDIR /app
COPY composer.* ./
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
COPY --from=fronted /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

USER www-data
EXPOSE 9000
CMD ["php-fpm"]
