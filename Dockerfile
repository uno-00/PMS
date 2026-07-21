# syntax=docker/dockerfile:1

#############################################
# Stage 1 - Frontend assets (Vite/Tailwind) #
#############################################
FROM node:22-alpine AS frontend

WORKDIR /app
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci
COPY frontend/resources/ resources/
COPY frontend/vite.config.js ./
RUN npm run build

#############################################
# Stage 2 - PHP dependencies (Composer)     #
#############################################
FROM composer:2 AS vendor

WORKDIR /app
COPY backend/database/ database/
COPY backend/composer.json backend/composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader \
    --ignore-platform-reqs

#############################################
# Stage 3 - Runtime image (php-fpm)         #
#############################################
FROM php:8.3-fpm-alpine AS base

LABEL maintainer="PMS Engineering" \
      description="Enterprise Procurement Management System (RA 12009)"

WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        curl \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        oniguruma-dev \
        zip \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && apk del --no-cache libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev icu-dev oniguruma-dev libzip-dev

COPY backend/docker/php/php.ini /usr/local/etc/php/conf.d/99-pms.ini
COPY backend/docker/php/opcache.ini /usr/local/etc/php/conf.d/98-opcache.ini
COPY backend/docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

COPY backend/ .
COPY frontend/resources/ resources/
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

RUN addgroup -g 1000 www \
    && adduser -G www -g www -s /bin/sh -D www \
    && chown -R www:www /var/www/html \
    && chmod -R 775 storage bootstrap/cache

USER www

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
