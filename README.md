# PMS — Enterprise Procurement Management System

A complete, production-shaped Enterprise Procurement Management System for
Philippine Government Agencies, compliant with **RA 12009** (New Government
Procurement Act), covering the entire lifecycle from the DBM General
Appropriations Act (GAA) through to Payment — with full audit trail and
configurable workflows, thresholds, and approval routes throughout.

```
DBM GAA → Budget Allocation → APP → PPMP → Purchase Request → CAF
   → BAC Procurement → PhilGEPS Posting → Bidder Portal → Bidding
   → Award → Notice to Proceed → Purchase Order → Delivery
   → Inspection → Acceptance → Payment → Reports & Analytics
```

## Technology Stack

Laravel 12 · PHP 8.3 · Livewire 3 · TailwindCSS 4 · AlpineJS · MySQL 8 ·
AWS S3 · Laravel Queue & Scheduler · Laravel Policies & Gates ·
Spatie Laravel Permission · Laravel Excel · DomPDF · Laravel Notifications ·
UUID Primary Keys · Repository Pattern · Service Layer ·
Event-Driven Architecture

## Highlights

- **17-phase procurement workflow**, each phase modeled as a PHP backed
  `enum` implementing a shared `Transitionable` contract, enforced through
  a single `HasWorkflow::transitionTo()` choke point that also writes an
  immutable `workflow_histories` audit entry on every transition.
- **19 roles / 101 permissions** (Spatie Laravel Permission + Laravel
  Policies/Gates), fully admin-editable at runtime from **Settings** — see
  `docs/RBAC.md`.
- **Nothing hardcoded**: procurement thresholds, approval routing, modes of
  procurement, fund sources, UACS codes, PAPs, cost centers, departments,
  divisions, password policy, session timeout, SMTP/SMS/PhilGEPS/AWS
  credentials, and audit-log retention all live in the **Settings** module
  (`SystemSetting` key-value store), editable without a deploy.
- **Complete audit trail**: `workflow_histories` (state transitions) +
  `spatie/laravel-activitylog` (field-level diffs) + a dedicated **Audit
  Trail** module for Internal Auditors, with configurable retention pruned
  daily by the Laravel Scheduler.
- **Separate Bidder Portal** (`/portal`) for supplier registration,
  eligibility documents, opportunities, bid document purchase, electronic
  bid submission (deadline-locked), clarifications, and award tracking.
- **AWS S3 document management**: private, versioned, encrypted bucket with
  a fixed folder taxonomy, signed URLs, and a configurable retention
  lifecycle — see `docs/AWS_DEPLOYMENT.md`.
- **Printable government forms** (PPMP, APP, PR, CAF, ORS/BURS, Abstract of
  Bids, Minutes, Attendance, BAC Resolution, NOA, NTP, PO, Inspection/
  Acceptance/Delivery reports) as PDFs with QR-code verification and
  digital signatures.
- **In-app Help & User Manuals** module with 7 role-specific manuals
  (Administrator, System Administrator, End User, Budget Officer, Planning,
  BAC, Supplier) plus a step-by-step guide, workflow diagram, common
  errors, FAQs, approval process, and tips for every functional module.
- **API-ready**: a versioned, Sanctum-authenticated read API
  (`/api/v1/...`) reusing the exact same Policies as the web app, documented
  with an OpenAPI 3.0 spec (`storage/api-docs/openapi.yaml`).

## Documentation

| Document | Purpose |
|---|---|
| [`docs/INSTALLATION.md`](docs/INSTALLATION.md) | Local development setup |
| [`docs/DOCKER.md`](docs/DOCKER.md) | Multi-container Docker Compose stack |
| [`docs/AWS_DEPLOYMENT.md`](docs/AWS_DEPLOYMENT.md) | Production AWS deployment (ECS, RDS, S3, IAM) |
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | Layered design, ERD, workflow state machines, sequence diagrams |
| [`docs/RBAC.md`](docs/RBAC.md) | Roles, permissions, and dashboards |
| [`docs/SECURITY.md`](docs/SECURITY.md) | Authentication, authorization, encryption, audit trail |
| [`docs/BACKUP_RESTORE.md`](docs/BACKUP_RESTORE.md) | Database and document backup/restore runbook |
| [`docs/API.md`](docs/API.md) | JSON API reference (see also `storage/api-docs/openapi.yaml`) |
| [`docs/TEST_ACCOUNTS.md`](docs/TEST_ACCOUNTS.md) | Seeded demo accounts (internal + Bidder Portal) |

Full step-by-step **User Manuals** (per module and per role) are built into
the application itself: sign in and open **Help** in the sidebar.

## Quick start

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
npm run build
php artisan storage:link
composer run dev
```

Visit `http://localhost:8000` and sign in with any account from
[`docs/TEST_ACCOUNTS.md`](docs/TEST_ACCOUNTS.md) (default password:
`Passw0rd!2026`). Full instructions, troubleshooting, and a Docker-based
alternative are in [`docs/INSTALLATION.md`](docs/INSTALLATION.md) and
[`docs/DOCKER.md`](docs/DOCKER.md).

## Testing

```bash
composer test
```

Feature tests cover RBAC route protection and the full budget-integrity and
procurement-lifecycle business rules end to end (GAA → CAF and
BAC → Bidding → Award → NTP → PO → Delivery → Inspection → Acceptance →
Payment) — see `tests/Feature/Workflow/`.

## Project layout

```
app/
  Enums/            Workflow status enums (Transitionable contract)
  Events/ Listeners/  Domain events driving cross-module side effects
  Http/Controllers/Api/V1/  Read-only JSON API
  Livewire/         UI components, one namespace per module
  Models/           Eloquent models (UUID PKs), grouped by domain
  Notifications/    Mail notifications (NOA, NTP, PhilGEPS, clarifications, ...)
  Policies/         Authorization rules per model
  Repositories/      Query abstraction for high-traffic aggregates
  Services/         Business rules, transactions, workflow transitions
  Support/          Roles, Permissions, PasswordPolicy, HelpContent helpers
database/
  factories/ migrations/ seeders/
docker/             Dockerfile support files (php.ini, nginx, entrypoint)
docs/               Deliverable documentation (this table, above)
resources/views/livewire/   Blade views matching each Livewire component
routes/             web.php, api.php, bidder_portal.php, console.php
storage/api-docs/   OpenAPI 3.0 spec
tests/Feature/      Smoke tests + Workflow lifecycle tests
```

## License

Proprietary — built for Philippine Government Agency internal use.
