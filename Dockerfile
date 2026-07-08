# Stage 1: PHP dependencies (needed by Vite/Ziggy)
FROM composer:2 AS composer-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts \
    --ignore-platform-req=ext-intl --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-bcmath

# Stage 2: Frontend assets
FROM node:22-alpine AS node-builder
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY --from=composer-builder /app/vendor ./vendor
COPY vite.config.ts tsconfig.json postcss.config.js components.json ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# Stage 3: Final image
FROM dunglas/frankenphp:1.5-php8.3-alpine AS base

RUN apk add --no-cache curl bash su-exec

RUN mkdir -p /data/caddy /config/caddy /home/.local/share/caddy && \
    chmod -R 755 /data /config /home/.local && \
    addgroup -g 1000 appgroup && \
    adduser -u 1000 -G appgroup -h /app -s /bin/sh -D appuser && \
    chown -R appuser:appgroup /data /config /home/.local

ENV XDG_CONFIG_HOME=/config \
    XDG_DATA_HOME=/data \
    APP_ENV=production \
    APP_DEBUG=false \
    OCTANE_SERVER=frankenphp

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN install-php-extensions \
    pcntl \
    intl \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    zip \
    bcmath \
    redis && \
    rm -rf /tmp/* /var/cache/apk/*

COPY docker/php/production.ini $PHP_INI_DIR/conf.d/99-production.ini
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /app
COPY --chown=appuser:appgroup . .
COPY --from=node-builder --chown=appuser:appgroup /app/public/build ./public/build/

RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction && \
    chown -R appuser:appgroup /app && \
    chmod -R ug+rwX storage bootstrap/cache && \
    rm -rf tests node_modules .git* && \
    composer clear-cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000", "--no-interaction"]
