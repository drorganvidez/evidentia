# evidentia

## Export host users

```
export UID=$(id -u)
export GID=$(id -g)
```

## Setting up in develop

```
cp .env.example.dev
docker compose -f docker/docker-compose.dev.yml up -d --build
```

## Run migrations

```
docker exec -it evidentia_app_container bash
php artisan migrate
php artisan db:seed
```
