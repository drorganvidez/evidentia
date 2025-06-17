#!/bin/sh

echo "🛠️ [DEV] Entrypoint iniciado..."

composer install

php artisan config:clear
php artisan key:generate
php artisan migrate

php artisan serve --host=0.0.0.0 --port=${APP_PORT:-8000}
