#!/bin/sh

echo "🚀 [PROD] Entrypoint started..."

# public/build is a volume shared with nginx: refresh it from the assets compiled in this image
if [ -d /opt/evidentia/build ]; then
    rm -rf /var/www/html/public/build/*
    cp -R /opt/evidentia/build/. /var/www/html/public/build/
fi

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache

sh ./scripts/wait-for-db.sh
php artisan migrate --force

exec php-fpm
