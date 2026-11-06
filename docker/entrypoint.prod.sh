#!/bin/sh

echo "[PROD] Entrypoint started..."

# public/build is a volume shared with nginx: refresh it from the assets compiled in this image
if [ -d /opt/evidentia/build ]; then
    grep -q ' /var/www/html/public/build ' /proc/mounts \
        || echo "WARNING public/build is not a volume shared with nginx; recreate app and nginx with docker compose up -d" >&2
    rm -rf /var/www/html/public/build/*
    cp -R /opt/evidentia/build/. /var/www/html/public/build/ || { echo "Could not refresh public/build" >&2; exit 1; }
fi

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache
# the listeners are only discovered (app/Listeners); cached once here instead of on every request
php artisan event:cache

sh ./scripts/wait-for-db.sh
php artisan migrate --force

exec php-fpm
