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

### Generate app key

```
php -r "echo 'APP_KEY=base64:' . base64_encode(random_bytes(32)) . PHP_EOL;" >> .env
```

### Run containers

```
docker compose -f docker/docker-compose.dev.yml up -d --build
```

This will build and start the Laravel, MySQL, Redis, and Mailhog containers.

You can see Evidentia app running on `localhost:8000`

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

## Deployment in production

### Copy environment files and set

```
cp .env.example.prod .env
```

### Generate app key

```
php -r "echo 'APP_KEY=base64:' . base64_encode(random_bytes(32)) . PHP_EOL;" >> .env
```

### Run containers

```
docker compose -f docker/docker-compose.prod.yml up -d --build
```

You can see Evidentia app running on `localhost`