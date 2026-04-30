# Midigator Dashboard

Multi-tenant Laravel dashboard application for managing tenants, users, roles, evidence, comments, and orders. Built on Laravel with Horizon (queues), Reverb (websockets), Meilisearch, Postgres, and Redis.

## Login by PIN

Demo users are created by `DemoUsersSeeder` against the `demo` tenant. Use the email + PIN combinations below on the PIN login screen.

### Tenant users (demo tenant)

| Role          | Email                       | PIN  |
|---------------|-----------------------------|------|
| Tenant Admin  | admin@midigator.test        | 1234 |
| Manager       | manager@midigator.test      | 5678 |
| Analyst       | analyst@midigator.test      | 4321 |
| Viewer        | viewer@midigator.test       | 8765 |

### Platform admin

| Role            | Email                    | PIN  |
|-----------------|--------------------------|------|
| Platform Admin  | platform@midigator.test  | 9999 |

All demo users also share the standard password `password` if password login is used.

## Tech Stack

- Laravel (PHP 8.x, FPM)
- PostgreSQL 16
- Redis (cache, queues, broadcasting)
- Meilisearch (search)
- Laravel Horizon (queue worker dashboard)
- Laravel Reverb (websocket server)
- Vite (frontend build)
- Docker Compose (dev & prod)

## Installation

### Prerequisites

- Docker and Docker Compose
- Git

### Steps

1. Clone the repository:
   ```bash
   git clone <repo-url> midigator-dashboard-app
   cd midigator-dashboard-app
   ```

2. Copy the environment file and configure it:
   ```bash
   cp .env.example .env
   ```

3. Start the development stack:
   ```bash
   docker compose -f compose.dev.yaml up -d --build
   ```

4. Install PHP dependencies:
   ```bash
   docker compose -f compose.dev.yaml exec workspace composer install
   ```

5. Generate the application key:
   ```bash
   docker compose -f compose.dev.yaml exec workspace php artisan key:generate
   ```

6. Run migrations and seed demo data (creates demo tenant, roles, and users):
   ```bash
   docker compose -f compose.dev.yaml exec workspace php artisan migrate --seed
   ```

7. Install Node dependencies and start Vite:
   ```bash
   docker compose -f compose.dev.yaml exec workspace npm install
   docker compose -f compose.dev.yaml exec workspace npm run dev
   ```

The app is available at `http://localhost:8080` (or `NGINX_PORT` from `.env`).

### Useful URLs (development)

- App: `http://localhost:8080`
- Adminer (database UI): `http://localhost:9091`
- Meilisearch: `http://localhost:7700`
- Vite dev server: `http://localhost:5173`
- Reverb (websockets): `ws://localhost:9090`

## Production

A production compose stack is provided in `compose.prod.yaml` (Traefik-based reverse proxy, no exposed ports for postgres/redis/meilisearch). Build and start:

```bash
docker compose -f compose.prod.yaml up -d --build
```

Make sure `.env` defines `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `MEILISEARCH_KEY`, and any Traefik/host settings before starting.

## Common Commands

Run inside the `workspace` (dev) or `php-fpm` (prod) container:

```bash
php artisan migrate            # run migrations
php artisan db:seed             # re-seed demo data
php artisan horizon             # queue worker (auto-started in compose)
php artisan reverb:start        # websocket server (auto-started in compose)
php artisan schedule:work       # scheduler (auto-started in compose)
```
