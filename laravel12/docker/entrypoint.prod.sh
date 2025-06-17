#!/bin/sh

echo "🚀 [PROD] Entrypoint iniciado..."

composer install --no-dev --optimize-autoloader

php artisan config:clear
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

php artisan serve --host=0.0.0.0 --port=${APP_PORT:-9000}
