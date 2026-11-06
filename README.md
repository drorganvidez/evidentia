# Evidentia

Free software to manage the evidences of work in teaching conferences. Built with [Laravel 13](https://laravel.com/docs/13.x) on PHP 8.4; the front end is compiled with Vite 8, Tailwind CSS 4 and Alpine.js on Node 26.

## Requirements

- Docker
- Docker Compose

PHP, Composer and Node run inside the containers; nothing else is needed on the host.

## Development

### Create the `.env` file

```
cp .env.example.dev .env
```

### Generate the app key

Laravel needs a unique `APP_KEY` to encrypt cookies, sessions and other sensitive data. Add a random 32-byte key encoded in base64 to the `.env` file.

```
php -r "echo 'APP_KEY=base64:' . base64_encode(random_bytes(32)) . PHP_EOL;" >> .env
```

### Run the containers

```
docker compose -f docker/docker-compose.dev.yml up -d --build
```

This starts the Laravel app on `localhost:8000`, the Vite dev server on `localhost:5173` (hot module replacement, assets are rebuilt as you save), MySQL, Redis and Mailhog. The `vite` service installs `node_modules` the first time it runs.

To compile the assets once, without the dev server

```
docker compose -f docker/docker-compose.dev.yml run --rm vite npm run build
```

### Design system

The tokens and components of the UI are shown on `http://localhost:8000/_ui` (local and testing environments only).

### Test emails

Mailhog shows the outgoing emails on `http://localhost:8025`.

## Production

### Create the `.env` file

```
cp .env.example.prod .env
```

The `.env` file contains database credentials, API keys and encryption secrets. Review and update every field; never keep the default values in production.

### Generate the app key

```
php -r "echo 'APP_KEY=base64:' . base64_encode(random_bytes(32)) . PHP_EOL;" >> .env
```

### Run the containers

```
docker compose -f docker/docker-compose.prod.yml up -d --build
```

The production image compiles the front end in a Node stage and ships without Node. The compiled assets live in the `public_build` volume shared by the `app` and `nginx` containers; the entrypoint refreshes that volume from the image on every start, so a new image (released through Docker Hub and Watchtower) always serves its own assets.

The app is available on `localhost` or on your own domain. The remaining commands run inside the `evidentia_app_container`

```
docker exec -it evidentia_app_container bash
```

### Create the default values

```
php artisan create:roles
php artisan create:committees
```

### Create a new professor

```
php artisan create:professor
```

## Content Security Policy

Every response carries a strict, nonce-based Content Security Policy (no `unsafe-inline`, no `unsafe-eval`). While the views on the old layout remain, `CSP_MODE=report-only` keeps it informative; the routes already on the new UI enforce it. Set `CSP_REPORT=true` to have browsers post their violation reports to `/csp-report` and into the log.

## Quality

### PHP

```
vendor/bin/pint
vendor/bin/phpunit
```

### Front end

```
npm run lint          # ESLint, Stylelint and the blade-formatter check
npm run format        # Prettier, Stylelint --fix and blade-formatter --write
npm run format:check
```

Both run inside the containers as well

```
docker compose -f docker/docker-compose.dev.yml exec app vendor/bin/pint
docker compose -f docker/docker-compose.dev.yml run --rm vite npm run lint
```

The views that still use the old layout are listed in `.bladeignore`; remove a line when its view moves to the new UI.

### Continuous integration

Every push to `main` and every pull request runs Pint, PHPUnit, the front-end lint and format checks and the Vite build. Publishing a GitHub release builds the production image and pushes it to Docker Hub.
