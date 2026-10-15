FROM composer:2.8 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-interaction --no-progress \
    --ignore-platform-req=ext-pdo_pgsql --ignore-platform-req=ext-gd

FROM php:8.4-cli-alpine
RUN apk add --no-cache $PHPIZE_DEPS curl postgresql-dev libzip-dev oniguruma-dev icu-dev libxml2-dev \
        librdkafka-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql intl zip gd curl \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka
WORKDIR /app
COPY --from=vendor /app/vendor ./vendor
COPY . .
EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public", "public/router.php"]
