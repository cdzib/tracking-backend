# Dockerfile optimizado para producción Laravel
FROM php:8.4-fpm-alpine as base

WORKDIR /var/www/html

# Instalar dependencias del sistema
# Dependencias base
RUN apk add --no-cache \
    bash \
    curl \
    gnupg \
    mysql-client \
    msmtp \
    perl \
    procps \
    shadow \
    libzip \
    libpng \
    libjpeg-turbo \
    libwebp \
    freetype \
    icu \
    build-base \
    autoconf \
    automake \
    make \
    g++ \
    zlib-dev \
    libzip-dev \
    icu-dev \
    freetype-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    pcre-dev \
    $PHPIZE_DEPS

# Extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install gd mysqli pdo_mysql intl bcmath opcache exif zip pcntl && \
    pecl install redis && docker-php-ext-enable redis

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# Node 22 (Alpine edge community)
RUN apk add --no-cache nodejs npm --repository=http://dl-cdn.alpinelinux.org/alpine/edge/community

# Copiar código fuente
COPY src/ .


# Instalar dependencias de Composer (sin dev, autoloader optimizado)
# RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
#     && php artisan storage:link \
#     && php artisan config:cache \
#     && php artisan route:cache \
#     && php artisan view:cache

# Permisos
# Usuario laravel
RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel
RUN chown -R laravel /var/www/html
USER laravel


# Opcache recomendado para producción
COPY php.ini/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 9000
CMD ["php-fpm"]