#!/bin/sh

echo "🛠️ [DEV] Entrypoint iniciado..."

composer install

php artisan config:clear
php artisan key:generate
php artisan migrate

# Ejecutar seeder solo si la tabla está vacía (ejemplo con la tabla users)
USERS_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null)

if [ "$USERS_COUNT" -eq 0 ]; then
    echo "🌱 Ejecutando seeders..."
    php artisan db:seed --class=SampleSeeder
else
    echo "✅ Seeder ya ejecutado anteriormente, saltando..."
fi

php artisan serve --host=0.0.0.0 --port=${APP_PORT:-8000}
