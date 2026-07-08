#!/bin/sh
set -e

cd /app

# Corrige permissões do volume persistente (roda como root no Coolify)
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
fi

# Setup Laravel (como appuser quando possível)
run_as_app() {
    if [ "$(id -u)" = "0" ]; then
        su-exec appuser "$@"
    else
        "$@"
    fi
}

run_as_app mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ ! -L public/storage ]; then
    run_as_app php artisan storage:link --force
fi

if [ "${APP_ENV:-local}" = "production" ]; then
    run_as_app php artisan config:cache --no-ansi
    run_as_app php artisan view:cache --no-ansi
    run_as_app php artisan event:cache --no-ansi
    # route:cache pode falhar com closures — ignora se der erro
    run_as_app php artisan route:cache --no-ansi || true
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    run_as_app php artisan migrate --force --no-ansi
fi

# Octane/FrankenPHP precisa de root para o binário do servidor
exec "$@"
