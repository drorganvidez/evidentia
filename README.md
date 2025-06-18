# 🧪 Evidentia (Laravel 12)

This project is built with [Laravel 12](https://laravel.com/docs/12.x), the modern and elegant PHP framework for web development.

---

## 📦 Requirements

Make sure you have the following installed:

- Docker and Docker Compose
- Proper `UID` and `GID` environment variables to avoid permission issues

---

## ⚙️ Step 1 – Export host user and group

```bash
export UID=$(id -u)
export GID=$(id -g)
``` 

##  🚀 Step 2 – Start development environment

```
cp .env.example.dev .env
docker compose -f docker/docker-compose.dev.yml up -d --build
```

This will build and start the Laravel, MySQL, Redis, and Mailhog containers.

## 🛠️ Step 3 – Run migrations and seeders

```
docker exec -it evidentia_app_container bash
php artisan migrate
php artisan db:seed
```

## 📧 View test emails

Use Mailhog to inspect outgoing emails in development:

```
http://localhost:8025
```

## 🧹 Auto-format PHP files

```
./vendor/bin/pint 
```

## 🧹 Auto-format Blade files

```
npx blade-formatter "resources/views/**/*.blade.php" --write
```