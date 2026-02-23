#!/bin/sh
set -e
cd /app
[ -f .env ] || cp .env.example .env
composer install --no-interaction
php artisan key:generate --no-interaction --force 2>/dev/null || true
php artisan passport:keys --no-interaction 2>/dev/null || true
# nwidart/laravel-modules: modules_statuses.json is NOT created on install. It is created
# only when you first run `php artisan module:enable` (or enable a module). That command
# calls FileActivator::setActiveByName() -> writeJson(), which writes base_path('modules_statuses.json').
php artisan module:enable --no-interaction 2>/dev/null || true
# App migrations then module migrations (Laravel migrate:fresh does NOT run module paths)
php artisan migrate:fresh --drop-views --force --no-interaction
php artisan module:migrate --force --no-interaction
php artisan db:seed --force --no-interaction
exec php artisan serve --host=0.0.0.0 --port=8000
