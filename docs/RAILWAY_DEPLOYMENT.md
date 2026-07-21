# Railway Deployment (24/7 Demo URL)

Deploy the PMS to Railway so the public link keeps working **even when your PC is off**.

## What you get

- Permanent HTTPS URL like `https://your-app.up.railway.app`
- MySQL database in the cloud
- Auto-deploy on every push to `ysa-branch`

## 1. Prerequisites

- GitHub repo: [`uno-00/PMS`](https://github.com/uno-00/PMS) (branch `ysa-branch`)
- [Railway account](https://railway.com/) (GitHub login)

## 2. Create the project

1. Open [railway.com/new](https://railway.com/new)
2. **Deploy from GitHub repo** → select [`uno-00/PMS`](https://github.com/uno-00/PMS)
3. Set branch to **`ysa-branch`**
4. Railway detects `railway.toml` and builds with `Dockerfile.railway`

## 3. Add MySQL

1. In the project, click **+ New** → **Database** → **MySQL**
2. Wait until MySQL is **Running**
3. Open the **PMS web service** (not MySQL) → **Variables**

### Important: ignore Railway’s “variables found in source code”

Railway scans `.env.example` and suggests **local** values (`127.0.0.1`, `APP_DEBUG=true`, etc.). **Do not use those.**

Copy from [`railway.env.example`](../railway.env.example) instead.

### Database variables (Add Reference)

Click **Add Reference** → select your **MySQL** service. Railway inserts `${{ServiceName.VAR}}` — the service name might be `MySQL`, `MySQL-abc1`, etc. Use whatever name Railway shows.

| Variable | How to set |
|----------|------------|
| `DB_CONNECTION` | type `mysql` manually |
| `DB_HOST` | reference → `MYSQLHOST` |
| `DB_PORT` | reference → `MYSQLPORT` |
| `DB_DATABASE` | reference → `MYSQLDATABASE` |
| `DB_USERNAME` | reference → `MYSQLUSER` |
| `DB_PASSWORD` | reference → `MYSQLPASSWORD` |

## 4. Required app variables (web service)

| Variable | Value |
|----------|-------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | Your Railway HTTPS URL (step 5) |
| `APP_TIMEZONE` | `Asia/Manila` |
| `RUN_SEED` | `true` (first deploy only; then `false`) |
| `LOG_CHANNEL` | `stderr` |
| `SESSION_DRIVER` | `file` |
| `CACHE_STORE` | `file` |
| `QUEUE_CONNECTION` | `sync` |
| `DOCUMENTS_DISK_DRIVER` | `local` |

Leave `APP_KEY` empty on first deploy — it is generated automatically.

## 5. Public URL

1. Web service → **Settings** → **Networking** → **Generate Domain**
2. Copy the URL (e.g. `https://pms-production.up.railway.app`)
3. Set `APP_URL` to that exact URL (with `https://`)
4. **Redeploy**

## 6. Deploy

On boot the container:

1. Starts the HTTP server immediately (healthcheck hits `/healthz.php`)
2. Waits for MySQL, runs migrations, optional seed in the background
3. Listens on Railway’s `$PORT`

Check **Deploy Logs** for:

```
[railway] starting HTTP server on 0.0.0.0:...
[railway] database reachable — running migrations...
[railway] database setup complete
```

If you see `DB_HOST is not configured` → MySQL references are missing or wrong.

## 7. Test login

Open `https://<your-domain>/login`

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `superadmin@pms.gov.ph` | `Passw0rd!2026` |

See `docs/TEST_ACCOUNTS.md` for all demo accounts.

## 8. After first successful deploy

Set `RUN_SEED=false` so redeploys do not re-seed the database.

## Troubleshooting

| Issue | Fix |
|-------|-----|
| Healthcheck fails | Ensure latest deploy (`php -S` + `/healthz.php`); check logs for server start line |
| `DB_HOST is not configured` | Add MySQL **Reference** variables — not `127.0.0.1` |
| `database not reachable` | MySQL service not running, or wrong reference service name |
| Login 500 | Wait for `database setup complete` in logs; set `RUN_SEED=true` once |
| Unstyled page | Redeploy — image runs `npm run build` |
| Mixed content | `APP_URL` must be `https://...` |
| Used Railway auto-detected vars | Delete them; use `railway.env.example` values |

## Cost note

- **Cloudflare tunnel** = free but PC must stay on
- **Railway** = 24/7 cloud; Hobby ~$5/mo recommended for client demos
