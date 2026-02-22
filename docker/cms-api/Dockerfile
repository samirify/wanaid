# cms-api – Laravel 12 on PHP 8.4 (no Docker/Sail inside the app)
FROM php:8.4-cli-alpine

RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    icu-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        zip \
        intl \
        opcache \
        pcntl \
        bcmath \
    && rm -rf /var/cache/apk/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application (context is repo root)
COPY backend/cms-api/ .

RUN composer install --no-dev --no-interaction --optimize-autoloader \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
