FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    libxml2-dev \
    oniguruma-dev \
    sqlite-dev \
    postgresql-dev \
    && docker-php-ext-install -j"$(nproc)" \
    mbstring \
    pdo_mysql \
    pdo_sqlite \
    pdo_pgsql \
    opcache
WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . ./

RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x docker/entrypoint.sh

ENTRYPOINT ["./docker/entrypoint.sh"]

CMD ["php-fpm", "-F"]
