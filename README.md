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
echo "APP_KEY=base64:$(openssl rand -base64 32)" >> .env
```

### Run the containers

```
docker compose -f docker/docker-compose.dev.yml up -d --build
```

This starts the Laravel app on `localhost:8000`, the Vite dev server on `localhost:5173` (hot module replacement, assets are rebuilt as you save), MySQL, Redis and Mailhog. The `vite` service installs `node_modules` inside the container the first time it runs and again whenever `package-lock.json` changes; do not install it on the host, its native binaries would not match.

To compile the assets once, without the dev server

```
docker compose -f docker/docker-compose.dev.yml run --rm vite sh -c 'npm ci && npm run build'
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
echo "APP_KEY=base64:$(openssl rand -base64 32)" >> .env
```

### Run the containers

```
docker compose -f docker/docker-compose.prod.yml up -d
```

The production image comes from Docker Hub and compiles the front end in a Node stage, so it ships without Node. The compiled assets live in the `public_build` volume shared by the `app` and `nginx` containers; the entrypoint refreshes that volume from the image on every start, so a new image (released through Docker Hub and Watchtower) always serves its own assets. When the compose file changes (the volume was added with the new UI), run `git pull` and `docker compose -f docker/docker-compose.prod.yml up -d` once on the server so that `app` and `nginx` are recreated with it.

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

Every response carries a strict, nonce-based Content Security Policy (no `unsafe-inline`, no `unsafe-eval`) and `X-Frame-Options: DENY`. The behaviour is set in `config/csp.php`

| Variable | Default | Meaning |
| --- | --- | --- |
| `CSP_ENABLED` | `true` | Switch the policy off only to debug. |
| `CSP_MODE` | `report-only` | `report-only` while the views on the old layout remain; `enforce` afterwards. Routes already on the new UI enforce it through the `csp:enforce` middleware alias. |
| `CSP_REPORT` | `false` | When `true`, browsers post their violation reports to `/csp-report`. |
| `CSP_LOG_CHANNEL` | `csp` | Where the reports are written; the `csp` channel is `storage/logs/csp.log`, independent of `LOG_LEVEL`. |
| `CSP_UPGRADE_INSECURE_REQUESTS` | `true` | Adds `upgrade-insecure-requests` to requests that arrived over https. |

The client address and scheme come from the `X-Forwarded-*` headers of the reverse proxies in front of the app (`bootstrap/app.php`).

## Quality

The commands below run inside the containers of `docker-compose.dev.yml`; with PHP and Node installed on the host they also run directly.

### PHP

```
docker compose -f docker/docker-compose.dev.yml exec app vendor/bin/pint
docker compose -f docker/docker-compose.dev.yml exec app vendor/bin/phpunit
```

The whole suite runs in one process; `phpunit.xml` raises its memory limit to 512 MB because the spreadsheet exports need more than the default 128 MB.

### Front end

```
docker compose -f docker/docker-compose.dev.yml run --rm vite npm run lint          # ESLint, Stylelint and the blade-formatter check
docker compose -f docker/docker-compose.dev.yml run --rm vite npm run format        # Prettier, Stylelint --fix and blade-formatter --write
docker compose -f docker/docker-compose.dev.yml run --rm vite npm run format:check
```

The views that still use the old layout are listed in `.bladeignore`; remove a line when its view moves to the new UI.

### End to end

Playwright drives the app in Chromium, desktop and mobile. `playwright.config.js` starts `php artisan serve` with four PHP workers unless something already answers at `APP_URL` (default `http://127.0.0.1:8000`). The tests run one at a time by default, because the catalogue at `/_ui` is a heavy page and a navigation can wait behind the accessibility audit of another browser; set `PW_WORKERS` to run more in parallel on a roomy machine.

```
npm run test:e2e
```

Without Node on the host, run it from the official image against the running app

```
docker run --rm -v "$PWD":/app -w /app -e HOME=/tmp -e APP_URL=http://host.docker.internal:8000 --add-host=host.docker.internal:host-gateway mcr.microsoft.com/playwright:v1.65.0-noble npx playwright test
```

The report lands in `playwright-report/` and the traces in `test-results/`, both ignored by git.

### Continuous integration

Every push to `main` and every pull request runs Pint and PHPUnit, the front-end lint and format checks and the Vite build (`tests.yml`), and the Playwright suite against the app served with PHP and SQLite, desktop and mobile, with the HTML report as an artifact (`e2e.yml`). Dependabot opens pull requests for the GitHub Actions, the Docker images and the npm and Composer packages. Publishing a GitHub release builds the production image and pushes it to Docker Hub.
