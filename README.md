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
  Policies/Gates),   fully admin-editable at runtime from **Settings** — see
  [`backend/docs/RBAC.md`](backend/docs/RBAC.md).
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
  lifecycle — see [`backend/docs/AWS_DEPLOYMENT.md`](backend/docs/AWS_DEPLOYMENT.md).
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
| [`backend/docs/INSTALLATION.md`](backend/docs/INSTALLATION.md) | Local development setup |
| [`backend/docs/DOCKER.md`](backend/docs/DOCKER.md) | Multi-container Docker Compose stack |
| [`backend/docs/AWS_DEPLOYMENT.md`](backend/docs/AWS_DEPLOYMENT.md) | Production AWS deployment (ECS, RDS, S3, IAM) |
| [`backend/docs/ARCHITECTURE.md`](backend/docs/ARCHITECTURE.md) | Layered design, ERD, workflow state machines, sequence diagrams |
| [`backend/docs/RBAC.md`](backend/docs/RBAC.md) | Roles, permissions, and dashboards |
| [`backend/docs/SECURITY.md`](backend/docs/SECURITY.md) | Authentication, authorization, encryption, audit trail |
| [`backend/docs/BACKUP_RESTORE.md`](backend/docs/BACKUP_RESTORE.md) | Database and document backup/restore runbook |
| [`backend/docs/API.md`](backend/docs/API.md) | JSON API reference (see also `storage/api-docs/openapi.yaml`) |
| [`backend/docs/TEST_ACCOUNTS.md`](backend/docs/TEST_ACCOUNTS.md) | Seeded demo accounts (internal + Bidder Portal) |

Full step-by-step **User Manuals** (per module and per role) are built into
the application itself: sign in and open **Help** in the sidebar.

## Quick start

```bash
cd backend && composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
cd ../frontend && npm install && npm run build
cd ../backend && php artisan storage:link
composer run dev
```

Visit `http://localhost:8000` and sign in with any account from
[`backend/docs/TEST_ACCOUNTS.md`](backend/docs/TEST_ACCOUNTS.md) (default password:
`Passw0rd!2026`). Full instructions, troubleshooting, and a Docker-based
alternative are in [`backend/docs/INSTALLATION.md`](backend/docs/INSTALLATION.md) and
[`backend/docs/DOCKER.md`](backend/docs/DOCKER.md).

## Testing

```bash
cd backend && composer test
```

Feature tests cover RBAC route protection and the full budget-integrity and
procurement-lifecycle business rules end to end (GAA → CAF and
BAC → Bidding → Award → NTP → PO → Delivery → Inspection → Acceptance →
Payment) — see `backend/tests/Feature/Workflow/`.

## Project layout

```
backend/              Laravel API, Livewire, migrations, tests
  app/                Application code
  routes/             Web, API, console routes
  database/           Migrations and seeders
  public/             Web root and compiled assets
  docs/               Project documentation
frontend/             UI layer
  resources/views/    Blade + Livewire templates
  resources/css/      Tailwind design system
  resources/js/       Vite entry points (Chart.js, Quill, Alpine)
```

## License

Proprietary — built for Philippine Government Agency internal use.
