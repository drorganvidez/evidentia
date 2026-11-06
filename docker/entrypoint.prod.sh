#!/bin/sh

echo "🚀 [PROD] Entrypoint started..."

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache

sh ./scripts/wait-for-db.sh
php artisan migrate --force

exec php-fpm
