# 🧪 Evidentia (Laravel 12)

This project is built with [Laravel 12](https://laravel.com/docs/12.x), the modern and elegant PHP framework for web development.

---

## 📦 Requirements

Make sure you have the following installed:

- Docker
- Docker compose

---

## 🚀 Deployment in develop

### Create `.env` file

```
cp .env.example.dev .env
```

### Generate app key

Laravel requires a unique `APP_KEY` to secure encrypted data within the application, such as cookies, sessions, and other sensitive information. This key must be set in your `.env` file and should be a random 32-character string encoded in base64.

```
php -r "echo 'APP_KEY=base64:' . base64_encode(random_bytes(32)) . PHP_EOL;" >> .env
```

### Run containers

```
docker compose -f docker/docker-compose.dev.yml up -d --build
```

This will build and start the Laravel, MySQL, Redis, and Mailhog containers.

You can see Evidentia app running on `localhost:8000`

### 📧 View test emails

Use Mailhog to inspect outgoing emails in development:

```
http://localhost:8025
```

## 🚀 Deployment in production

### Create `.env` file

```
cp .env.example.prod .env
```

⚠️ Important: The `.env` file contains sensitive information such as database credentials, API keys, and encryption secrets.
Do not leave default values in production—review and update every field accordingly.

### Generate app key

Laravel requires a unique `APP_KEY` to secure encrypted data within the application, such as cookies, sessions, and other sensitive information. This key must be set in your `.env` file and should be a random 32-character string encoded in base64.

```
php -r "echo 'APP_KEY=base64:' . base64_encode(random_bytes(32)) . PHP_EOL;" >> .env
```

### Run containers

```
docker compose -f docker/docker-compose.prod.yml up -d --build
```

You can see Evidentia app running on `localhost` or your own web domain

Remember to enter inside the `evidentia_app_container` to execute the rest of the commands

```
docker exec -it evidentia_app_container bash
```

### Create default values

```
php artisan create:roles
php artisan create:committees
```

### Create new professor

```
php artisan create:professor
```

## 	🎁 Utilities

### 🧹 Auto-format PHP files

```
./vendor/bin/pint 
```

### 🧹 Auto-format HTML Blade files

```
npx blade-formatter "resources/views/**/*.blade.php" --write
```