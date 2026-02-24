#!/bin/sh
set -e
cd /app
[ -f .env ] || cp .env.example .env
composer install --no-interaction
php artisan key:generate --no-interaction --force 2>/dev/null || true
php artisan passport:keys --no-interaction 2>/dev/null || true
php artisan module:enable --no-interaction 2>/dev/null || true
# Only apply pending migrations. Use "make reset" for full DB wipe + seed.
php artisan migrate --force --no-interaction
php artisan module:migrate --force --no-interaction
exec php artisan serve --host=0.0.0.0 --port=8000
