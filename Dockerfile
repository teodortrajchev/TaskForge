
# Build frontend assets
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# Laravel application
FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libxml2-dev \
    libonig-dev libcurl4-openssl-dev \
    && docker-php-ext-install \
       pdo_mysql mbstring bcmath intl zip dom xml curl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

COPY --from=frontend /app/public/build /var/www/html/public/build

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
