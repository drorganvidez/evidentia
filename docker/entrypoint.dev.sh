#!/bin/sh

echo "🛠️ [DEV] Entrypoint started..."

composer install

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

sh ./scripts/wait-for-db.sh
php artisan migrate

# Execute seeder only if the table is empty (example with users table)
USERS_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null)

if [ "$USERS_COUNT" -eq 0 ]; then
    echo "🌱 Ejecutando seeders..."
    php artisan db:seed --class=DevelopmentSeeder
else
    echo "✅ Seeder ya ejecutado anteriormente, saltando..."
fi

php artisan serve --host=0.0.0.0 --port=${APP_PORT:-8000}
