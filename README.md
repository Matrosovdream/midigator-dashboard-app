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

---

## Tech Stack

- Laravel (PHP 8.x, FPM)
- PostgreSQL 16
- Redis (cache, queues, broadcasting)
- Meilisearch (search)
- Laravel Horizon (queue worker dashboard)
- Laravel Reverb (websocket server)
- Vite (frontend build)
- Docker Compose (dev & prod)

---

## Local Development

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

---

## Production — Cloud Deployment

### Recommended server

- **DigitalOcean Droplet** — $24/mo (2 vCPU, 4GB RAM, Ubuntu 24.04)
- Ports 80 and 443 open (default on DigitalOcean)

### Prerequisites on the server

```bash
curl -fsSL https://get.docker.com | sh
systemctl enable docker
systemctl start docker
docker network create traefik-network
```

### Step 1 — Set up Traefik

```bash
mkdir -p /opt/traefik/dynamic
cd /opt/traefik
touch acme.json && chmod 600 acme.json
```

Create `/opt/traefik/traefik.yml`:
```yaml
api:
  dashboard: false
entryPoints:
  web:
    address: ":80"
    http:
      redirections:
        entryPoint:
          to: websecure
          scheme: https
          permanent: true
  websecure:
    address: ":443"
certificatesResolvers:
  letsencrypt:
    acme:
      email: you@youremail.com
      storage: /acme.json
      httpChallenge:
        entryPoint: web
providers:
  docker:
    exposedByDefault: false
    network: traefik-network
  file:
    directory: /etc/traefik/dynamic
    watch: true
log:
  level: INFO
```

Create `/opt/traefik/docker-compose.yml`:
```yaml
services:
  traefik:
    image: traefik:v2.11
    container_name: traefik
    restart: unless-stopped
    security_opt:
      - no-new-privileges:true
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - /etc/localtime:/etc/localtime:ro
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - ./traefik.yml:/traefik.yml:ro
      - ./acme.json:/acme.json
      - ./dynamic:/etc/traefik/dynamic
    networks:
      - traefik-network
networks:
  traefik-network:
    external: true
```

```bash
docker compose up -d
docker logs traefik --tail=20
# Should show: Provider connection established with docker
```

### Step 2 — Add DNS in Cloudflare

| Field | Value |
|---|---|
| Type | A |
| Name | `midigator` |
| IPv4 | Your Droplet IP |
| Proxy | DNS only (grey cloud) |

### Step 3 — Clone the repo

```bash
cd /opt/apps
git clone <repo-url> midigator
cd midigator
```

### Step 4 — Create .env

```bash
cp .env.example .env
chmod 777 .env
nano .env
```

Key values to set:
```
APP_NAME=Midigator
APP_ENV=production
APP_DEBUG=false
APP_URL=https://midigator.yourdomain.com
APP_KEY=

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=midigator
DB_USERNAME=midigator
DB_PASSWORD=yourpassword

SESSION_DRIVER=database
SESSION_DOMAIN=midigator.yourdomain.com

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=redis
CACHE_STORE=redis

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379

SANCTUM_STATEFUL_DOMAINS=midigator.yourdomain.com

MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=yourpassword

REVERB_APP_ID=midigator
REVERB_APP_KEY=midigator-key
REVERB_APP_SECRET=midigator-secret
REVERB_HOST=midigator.yourdomain.com
REVERB_PORT=443
REVERB_SCHEME=https

VITE_APP_NAME="${APP_NAME}"
VITE_REVERB_APP_KEY=midigator-key
VITE_REVERB_HOST=midigator.yourdomain.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

> **Important:** `DB_USERNAME` must match `POSTGRES_USER` in compose.prod.yaml (default: `midigator`)

### Step 5 — Create Traefik routing file

```bash
nano /opt/traefik/dynamic/midigator.yml
```

```yaml
http:
  routers:
    midigator:
      rule: "Host(`midigator.yourdomain.com`)"
      entryPoints:
        - websecure
      tls:
        certResolver: letsencrypt
      service: midigator
  services:
    midigator:
      loadBalancer:
        servers:
          - url: "http://midigator-web-1:80"
```

### Step 6 — Update compose.prod.yaml

Add to `web` service:
```yaml
    networks:
      - <app-network>
      - traefik-network
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.midigator.rule=Host(`midigator.yourdomain.com`)"
      - "traefik.http.routers.midigator.tls.certresolver=letsencrypt"
      - "traefik.http.routers.midigator.entrypoints=websecure"
      - "traefik.http.services.midigator.loadbalancer.server.port=80"
```

Add to bottom networks section:
```yaml
networks:
  <app-network>:
    driver: bridge
  traefik-network:
    external: true
```

Remove ALL `ports:` from every service.

### Step 7 — Build frontend assets

```bash
docker run --rm \
  -v $(pwd):/var/www \
  -v midigator_node_modules:/var/www/node_modules \
  -w /var/www \
  --env-file .env \
  node:20-alpine sh -c "npm install && npm run build"
```

### Step 8 — Start the app

```bash
docker compose -f compose.prod.yaml up -d --build
```

### Step 9 — Generate key and run migrations

```bash
chmod 777 .env
docker compose -f compose.prod.yaml exec php-fpm php artisan key:generate
docker compose -f compose.prod.yaml exec php-fpm php artisan migrate --force
docker compose -f compose.prod.yaml exec php-fpm php artisan db:seed --force
docker compose -f compose.prod.yaml exec php-fpm php artisan config:cache
docker compose -f compose.prod.yaml exec php-fpm php artisan route:cache
docker compose -f compose.prod.yaml exec php-fpm php artisan view:cache
```

### Step 10 — Copy assets to volume

```bash
# Check exact volume name first
docker volume ls | grep midigator

docker run --rm \
  -v $(pwd)/public/build:/source \
  -v midigator_<assets-volume-name>:/dest \
  alpine sh -c "cp -rf /source/. /dest/"

docker compose -f compose.prod.yaml up -d --no-deps --force-recreate web
```

---

## Deploy Script

Save as `/opt/deploy.sh`:

```bash
#!/bin/bash
APP=$1
cd /opt/apps/$APP

echo "Pulling latest code..."
git pull origin main

echo "Building frontend assets..."
docker run --rm \
  -v $(pwd):/var/www \
  -v ${APP}_node_modules:/var/www/node_modules \
  -w /var/www \
  --env-file .env \
  node:20-alpine sh -c "npm install && npm run build"

echo "Copying assets to volume..."
docker volume ls | grep ${APP}

docker run --rm \
  -v $(pwd)/public/build:/source \
  -v ${APP}_easypost-management-app-public-assets:/dest \
  alpine sh -c "cp -rf /source/. /dest/"

echo "Building PHP image..."
docker compose -f compose.prod.yaml build php-fpm

echo "Restarting app..."
docker compose -f compose.prod.yaml up -d --no-deps --force-recreate php-fpm web

echo "Running migrations..."
docker compose -f compose.prod.yaml exec -T php-fpm php artisan migrate --force

echo "Clearing cache..."
docker compose -f compose.prod.yaml exec -T php-fpm php artisan config:cache
docker compose -f compose.prod.yaml exec -T php-fpm php artisan route:cache
docker compose -f compose.prod.yaml exec -T php-fpm php artisan view:cache

echo "✓ $APP deployed successfully!"
```

```bash
chmod +x /opt/deploy.sh
/opt/deploy.sh midigator
```

---

## Common Commands

```bash
# Run migrations
docker compose -f compose.prod.yaml exec php-fpm php artisan migrate

# Seed database
docker compose -f compose.prod.yaml exec php-fpm php artisan db:seed

# Open bash shell
docker compose -f compose.prod.yaml exec php-fpm bash

# View logs
docker compose -f compose.prod.yaml logs -f

# Restart app
docker compose -f compose.prod.yaml down && docker compose -f compose.prod.yaml up -d

# Check container status
docker compose -f compose.prod.yaml ps
```

---

## Known Issues & Fixes

### 1. APP_KEY not generating — Permission denied
`php artisan key:generate` fails because container user `www-data` cannot write to `.env`.

**Fix:**
```bash
chmod 777 .env
```

Or generate manually:
```bash
python3 -c "import base64, os; print('base64:' + base64.b64encode(os.urandom(32)).decode())"
```
Paste output into `.env` as `APP_KEY=base64:...`

---

### 2. Port already allocated
Error: `Bind for 0.0.0.0:5432 failed: port is already allocated`

Multiple apps trying to bind the same host port.

**Fix:** Remove ALL `ports:` sections from every service in `compose.prod.yaml`. Traefik handles all routing.

---

### 3. Wrong DB credentials — password authentication failed
`DB_USERNAME` in `.env` doesn't match `POSTGRES_USER` in compose file.

**Fix:** Check what the compose file expects:
```bash
grep POSTGRES_USER compose.prod.yaml
```
Match it in `.env`. If Postgres was already initialized with wrong credentials:
```bash
docker compose -f compose.prod.yaml down -v
docker compose -f compose.prod.yaml up -d
```

---

### 4. 404 on site
Container not connected to `traefik-network`.

**Fix:** Add `traefik-network` to `web` service networks and declare it as external in the networks section.

---

### 5. Traefik "Filtering disabled container"
Traefik sees container but ignores it.

**Fix:** Add `traefik.enable=true` label to `web` service.

---

### 6. Site shows old version after deploy
Docker layer cache serving stale assets, or assets not copied to volume.

**Fix:**
```bash
docker compose -f compose.prod.yaml build --no-cache web

docker run --rm \
  -v $(pwd)/public/build:/source \
  -v <app>_<assets-volume>:/dest \
  alpine sh -c "cp -rf /source/. /dest/"
```

---

### 7. Pusher/Reverb key missing in browser
`VITE_` variables not baked into JS build.

**Fix:** Always pass `--env-file .env` to node build container and rebuild after changing any `VITE_` variable:
```bash
docker run --rm \
  -v $(pwd):/var/www \
  -w /var/www \
  --env-file .env \
  node:20-alpine sh -c "npm install && npm run build"
```

---

### 8. .env BOM character error
Error: `unexpected character "≈" in variable name`

Caused by text editor adding invisible BOM character when pasting.

**Fix:**
```bash
python3 -c "
with open('.env', 'rb') as f:
    content = f.read()
content = content.replace(b'\xef\xbb\xbf', b'')
with open('.env', 'wb') as f:
    f.write(content)
"
```

---

### 9. Traefik v3 Docker API error
Error: `client version 1.24 is too old`

**Fix:** Use Traefik v2.11 instead of v3:
```yaml
image: traefik:v2.11
```