#!/bin/sh
set -e

cd /app

# Se rodando como root (deploy Coolify), corrige ownership do volume persistente
if [ "$(id -u)" = "0" ]; then
    mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache

    chown -R appuser:appgroup storage bootstrap/cache
    chmod -R ug+rwX storage bootstrap/cache

    exec su-exec appuser "$0" "$@"
fi

# A partir daqui roda como appuser (uid 1000 — equivalente ao www-data do Laravel)
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ ! -L public/storage ]; then
    php artisan storage:link --force
fi

if [ "${APP_ENV:-local}" = "production" ]; then
    php artisan config:cache --no-ansi
    php artisan route:cache --no-ansi
    php artisan view:cache --no-ansi
    php artisan event:cache --no-ansi
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-ansi
fi

exec "$@"
