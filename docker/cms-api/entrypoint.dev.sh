#!/bin/sh
set -e
cd /app
[ -f .env ] || cp .env.example .env
if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction
fi
php artisan key:generate --no-interaction --force 2>/dev/null || true
php artisan migrate --force --no-interaction 2>/dev/null || true
exec php artisan serve --host=0.0.0.0 --port=8000
