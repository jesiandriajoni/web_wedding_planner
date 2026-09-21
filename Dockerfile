# Stage 1: PHP Dependencies (Composer)
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --ignore-platform-reqs

# Stage 2: Frontend Assets Build (Vite)
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# Stage 3: Production Runtime (PHP 8.4)
FROM php:8.4-cli-alpine
WORKDIR /var/www/html

RUN apk add --no-cache libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev sqlite-dev \
    && docker-php-ext-install pdo pdo_mysql gd zip pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload --optimize --ignore-platform-reqs
RUN chown -R www-data:www-data storage bootstrap/cache
EXPOSE 80

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
