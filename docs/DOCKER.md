# Docker Guide

The repository ships a production-shaped multi-container setup:

| Service | Image | Role |
|---|---|---|
| `app` | built from `Dockerfile` (php-fpm 8.3-alpine) | Runs migrations on boot, then serves PHP-FPM |
| `nginx` | `nginx:1.27-alpine` | Terminates HTTP, proxies `*.php` to `app:9000` |
| `queue` | same image as `app`, `CONTAINER_ROLE=queue` | `php artisan queue:work` (NOA/NTP/PhilGEPS notifications, exports) |
| `scheduler` | same image as `app`, `CONTAINER_ROLE=scheduler` | Loops `php artisan schedule:run` every 60s (closing PhilGEPS postings, pruning audit logs, expiring-document reminders) |
| `mysql` | `mysql:8.0` | Primary datastore |
| `redis` | `redis:7-alpine` | Available for cache/queue if you switch `CACHE_STORE`/`QUEUE_CONNECTION` to `redis` |
| `mailhog` | `mailhog/mailhog` | Local SMTP catcher + web UI at `:8025` for every NOA/NTP/CAF/PhilGEPS email |

All four PHP services (`app`, `queue`, `scheduler`) are built from the
**same image**; `docker/entrypoint.sh` reads `CONTAINER_ROLE` to decide what
to run, which keeps the image list to build/scan/patch to exactly one.

## 1. Build and run

```bash
cp .env.example .env
# Edit .env: set DB_HOST=mysql, DB_USERNAME/DB_PASSWORD to match docker-compose.yml,
# MAIL_HOST=mailhog, MAIL_PORT=1025.

docker compose build
docker compose up -d
```

The `app` container automatically runs, on every boot:

```
config:cache → route:cache → view:cache → migrate --force → storage:link
```

Visit `http://localhost:8080` (nginx). MailHog UI: `http://localhost:8025`.

## 2. Seed sample data

```bash
docker compose exec app php artisan db:seed
```

## 3. Logs

```bash
docker compose logs -f app nginx queue scheduler
```

## 4. Building the Vite assets

The frontend build happens **inside the image** (multi-stage `Dockerfile`,
`frontend` stage) so `public/build/` is baked in — no Node.js is required at
runtime. Any front-end change requires `docker compose build app` (or
`queue`/`scheduler`, which share the image) to pick up new assets.

## 5. Production notes

- `docker/php/opcache.ini` sets `opcache.validate_timestamps=0` — rebuild the
  image for every deploy, don't bind-mount source over it.
- `docker/nginx/default.conf` denies direct access to `/storage/private`;
  all document downloads must go through the signed-URL controller
  (`routes/web.php` → `documents.download`), never a static Nginx location.
- Swap the `documents` disk to S3 in production by setting
  `DOCUMENTS_DISK_DRIVER=s3` plus the `AWS_*` variables — see
  `docs/AWS_DEPLOYMENT.md`.
- Scale `queue` horizontally with `docker compose up -d --scale queue=3`;
  each worker independently claims jobs from the `jobs` table (or Redis).
- The `scheduler` service intentionally runs a 60-second sleep loop rather
  than `cron` so it works identically on Docker, ECS, and Kubernetes without
  a separate cron daemon.

## 6. Tearing down

```bash
docker compose down            # keep volumes (DB data, storage)
docker compose down -v         # also delete volumes (destructive)
```
