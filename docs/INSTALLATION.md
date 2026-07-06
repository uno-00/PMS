# Installation Guide (Local Development)

## 1. Requirements

- PHP 8.3+ (extensions: `pdo_mysql`, `mbstring`, `bcmath`, `intl`, `gd`, `zip`, `fileinfo`)
- Composer 2.x
- Node.js 20+ and npm
- MySQL 8.0+ (or SQLite for a zero-config sandbox)
- Optional: Redis (queue/cache in production), an SMTP relay for real email

## 2. Clone and install dependencies

```bash
git clone <repo-url> pms-procurement
cd pms-procurement
composer install
npm install
```

## 3. Configure the environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set at minimum:

- `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
  (or `DB_CONNECTION=sqlite` + `touch database/database.sqlite` for a
  zero-config sandbox — no MySQL server required)
- `MAIL_*` (or leave the defaults and use `php artisan pail` / MailHog to
  inspect outgoing NOA/NTP/CAF/PhilGEPS notification emails locally)
- `AWS_*` and `DOCUMENTS_DISK_DRIVER` — leave `DOCUMENTS_DISK_DRIVER=local`
  for local dev (documents land in `storage/app/documents`); switch to `s3`
  once you have a bucket (see `docs/AWS_DEPLOYMENT.md`)

## 4. Database

```bash
php artisan migrate --seed
```

This runs all 43 migrations and seeds:

- 19 roles / 101 permissions (`PermissionSeeder`)
- Departments, divisions, offices, cost centers (`OrganizationSeeder`)
- Modes of procurement, fund sources, UACS codes, PAPs, holidays (`ReferenceDataSeeder`)
- Default approval routing (`ApprovalRoutingSeeder`)
- 18 internal test accounts, one per role (`UserSeeder`)
- 1 Bidder Portal test account (`BidderPortalSeeder`)
- A fully walked FY2026 sample: GAA → APP → Budget Allocation → PPMP → Purchase Request → CAF (`SampleProcurementSeeder`)

See `docs/TEST_ACCOUNTS.md` for the full credential list.

## 5. Build frontend assets and link storage

```bash
php artisan storage:link
npm run build      # production build
# or: npm run dev  # Vite dev server with HMR
```

## 6. Run the app

The fastest way to run everything (web server, queue worker, log tailer,
Vite dev server) in one terminal:

```bash
composer run dev
```

Or individually:

```bash
php artisan serve
php artisan queue:listen
php artisan schedule:work   # simulates the cron-driven scheduler locally
```

Visit `http://localhost:8000` and sign in with any account from
`docs/TEST_ACCOUNTS.md` (default password: `Passw0rd!2026`). The Bidder
Portal is at `http://localhost:8000/bidder/login`.

## 7. Run the test suite

```bash
composer test
# or
php artisan test
```

19 feature tests / 79+ assertions cover: RBAC route protection, the full
GAA→CAF budget-integrity rules (no overallocation, no duplicate PPMP
utilization), and the full BAC→Bidding→Award→NTP→PO→Delivery→Inspection→
Acceptance→Payment lifecycle.

## 8. Common issues

| Symptom | Fix |
|---|---|
| `Vite manifest not found` | Run `npm run build` (or keep `npm run dev` running) before hitting any page. |
| `SQLSTATE[HY000] [2002] Connection refused` | MySQL isn't running / wrong `DB_HOST`, or switch to `DB_CONNECTION=sqlite` for local dev. |
| Emails not appearing | Set `MAIL_MAILER=log` (check `storage/logs/laravel.log`) or point at MailHog (`docker-compose up mailhog`, UI at `:8025`). |
| 403 on every internal page | The signed-in user's role has no matching entry in `config/rbac.php`; re-run `php artisan db:seed --class=PermissionSeeder`. |
| Login page hangs for a long time / "Too many login attempts" | With `DB_CONNECTION=sqlite`, keep `SESSION_DRIVER=file` and `CACHE_STORE=file` (the defaults in `.env`). Pointing sessions/cache at the `database` driver on top of SQLite means every request writes to the same file the queue worker and scheduler also use, which can stall requests for a long time under lock contention. `php artisan cache:clear` also resets any rate-limiter lockout left over from earlier failed attempts. |
