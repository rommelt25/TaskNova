#!/bin/sh

set -eu

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    attempt=1

    until php artisan migrate --force --no-interaction; do
        if [ "$attempt" -ge "${MIGRATION_MAX_ATTEMPTS:-30}" ]; then
            exit 1
        fi

        attempt=$((attempt + 1))
        sleep 2
    done
fi

if [ "${CACHE_CONFIGURATION:-true}" = "true" ]; then
    php artisan config:cache --no-interaction
fi

if [ ! -e public/storage ]; then
    php artisan storage:link --no-interaction
fi

exec "$@"
