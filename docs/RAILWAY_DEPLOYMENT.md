# Railway Deployment (24/7 Demo URL)

Deploy the PMS to Railway so the public link keeps working **even when your PC is off**.

## What you get

- Permanent HTTPS URL like `https://bicol-pms-production.up.railway.app`
- MySQL database in the cloud
- Auto-deploy on every push to `ysa-branch` (optional)

## 1. Prerequisites

- GitHub repo: [`uno-00/PMS`](https://github.com/uno-00/PMS) (branch `ysa-branch`)
- [Railway account](https://railway.com/) (GitHub login)
- ~$5 credit/month on free trial; stable demo usually needs **Hobby plan (~$5/mo)**

## 2. Create the project

1. Open [railway.com/new](https://railway.com/new)
2. **Deploy from GitHub repo** → select [`uno-00/PMS`](https://github.com/uno-00/PMS)
3. Set branch to **`ysa-branch`**
4. Railway detects `railway.toml` and builds with `Dockerfile.railway`

## 3. Add MySQL

1. In the project, click **+ New** → **Database** → **MySQL**
2. Wait until MySQL is running
3. Open the **PMS web service** → **Variables** → **Add Reference** from MySQL:

| Variable | Reference |
|----------|-----------|
| `DB_CONNECTION` | `mysql` (type manually) |
| `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `DB_DATABASE` | `${{MySQL.MYSQLDATABASE}}` |
| `DB_USERNAME` | `${{MySQL.MYSQLUSER}}` |
| `DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |

## 4. Required app variables

Add these on the **web service** (not MySQL):

| Variable | Value |
|----------|-------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | Your Railway public URL (see step 5) |
| `APP_TIMEZONE` | `Asia/Manila` |
| `RUN_SEED` | `true` (first deploy only; set `false` after) |
| `LOG_CHANNEL` | `stderr` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `DOCUMENTS_DISK_DRIVER` | `local` |

`APP_KEY` is generated automatically on first boot if empty.

## 5. Public URL

1. Web service → **Settings** → **Networking** → **Generate Domain**
2. Copy the URL (e.g. `https://bicol-pms-production.up.railway.app`)
3. Set `APP_URL` to that exact URL (with `https://`)
4. Redeploy once

## 6. First deploy

Railway builds the Docker image (~5–8 min). On boot the container:

1. Waits for MySQL
2. Runs migrations
3. Seeds demo data (if `RUN_SEED=true`)
4. Starts the app on port `$PORT`

Health check: `GET /up`

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
| Build fails on Composer | Check `composer.lock` is committed |
| Build OK but healthcheck fails | Server now starts before migrate/seed; ensure MySQL variables are linked; set `RUN_SEED=true` only on first deploy |
| CSS broken | Image includes `npm run build`; redeploy |
| Login works locally only | Set `APP_URL` to Railway domain |
| Mixed content / http assets | `APP_URL` must start with `https://` |

## Cost note

- **Quick Cloudflare tunnel** = free but PC must stay on
- **Railway** = cloud server runs 24/7; free tier may sleep; Hobby ~$5/mo recommended for client demos
