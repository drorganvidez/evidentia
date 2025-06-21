#!/bin/sh

echo "🚀 [PROD] Entrypoint started..."

composer install --no-dev --optimize-autoloader

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

sh ./scripts/wait-for-db.sh
php artisan migrate --force

exec php-fpm
