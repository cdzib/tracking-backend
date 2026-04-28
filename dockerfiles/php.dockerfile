FROM php:8.4-fpm-alpine

WORKDIR /var/www/html
COPY src .

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

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Node 22 (Alpine edge community)
RUN apk add --no-cache nodejs npm --repository=http://dl-cdn.alpinelinux.org/alpine/edge/community

# Usuario laravel
RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel
RUN chown -R laravel /var/www/html
USER laravel

EXPOSE 9000
CMD ["php-fpm"]