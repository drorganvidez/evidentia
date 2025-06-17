# evidentia

## Setting up in development mode

```
cp .env.example.dev .env
docker compose -f docker/docker-compose.dev.yml up -d --build
```

Open `localhost:8000`