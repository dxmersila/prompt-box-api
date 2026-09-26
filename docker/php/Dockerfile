FROM php:8.5.9-fpm-alpine

RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    mysql-client

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    intl \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]