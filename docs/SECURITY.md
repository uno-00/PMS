# Security Documentation

## 1. Authentication

- Session-based auth for the internal agency application (`web` guard) and
  the separate Bidder Portal (`routes/bidder_portal.php`), both backed by
  the same `users` table and Spatie Permission roles.
- `Laravel\Sanctum` issues personal access tokens for the read-only JSON API
  (`routes/api/v1.php`), scoped by the same Policies as the web app.
- Passwords are hashed with bcrypt (`BCRYPT_ROUNDS`, default 12 in
  production, lowered to 4 only in the test environment for speed).
- **Configurable password policy** (`Settings > Security`, backed by
  `App\Support\PasswordPolicy`): minimum length, mixed case, numbers,
  symbols — all admin-editable at runtime, not hardcoded. Enforced on
  registration, forced password change, and user management.
- **Forced password change**: new accounts and admin-reset accounts carry
  `must_change_password = true`; `App\Http\Middleware\ForcePasswordChange`
  redirects every request until the user sets a compliant password.
- **Session timeout**: `App\Http\Middleware\EnforceSessionTimeout` logs out
  idle users after `Settings > Security > session_timeout_minutes`
  (default 120 minutes), read via `PasswordPolicy::sessionTimeoutMinutes()`.

## 2. Authorization (defense in depth)

Three layers, all backed by the same source of truth:

1. **Spatie Laravel Permission** — 19 roles / 101 granular permissions
   (`config/rbac.php` is the seed source; the seeded `roles`/`permissions`
   tables are the runtime source of truth and can be edited by a Super
   Admin in **Settings** without a deploy).
2. **Laravel Policies** (`app/Policies/*`) — model-level authorization
   (`GaaPolicy`, `PpmpPolicy`, `PurchaseRequestPolicy`, `CafPolicy`,
   `ProcurementPolicy`, `PurchaseOrderPolicy`, `BidderPolicy`,
   `AnnualProcurementPlanPolicy`), each extending `BasePolicy` which grants
   Super Admin an unconditional `before()` bypass and otherwise checks the
   specific permission string for the action.
3. **Laravel Gates** — route-level and Blade/Livewire-level checks
   (`Gate::authorize(...)`, `@can` directives) for actions that don't map
   cleanly to a single Eloquent model (e.g. Settings tabs, Reports export).

Every Livewire component's `mount()` calls `Gate::authorize(...)` or
`$this->authorize(...)` before rendering; every API controller action calls
`$this->authorize(...)` before querying. See `tests/Feature/Workflow/ProcurementLifecycleTest.php::test_non_bac_role_cannot_access_settings_or_audit_trail`
for an executable example of this being enforced.

## 3. Data protection

- **UUID primary keys** everywhere (`HasUuid` trait) — internal IDs are
  never sequential/guessable, which also makes cross-environment data
  merges and future multi-tenant splits safe.
- **Document encryption at rest**: production documents live in a private,
  versioned, SSE-encrypted S3 bucket (`AWS_SSE=AES256` or upgrade to
  SSE-KMS); local dev falls back to a non-public `storage/app/documents`
  disk. See `docs/AWS_DEPLOYMENT.md` §2.
- **Signed URLs**: every document download goes through
  `App\Services\Support\DocumentStorageService::temporaryUrl()`
  (15-minute expiry by default, `DOCUMENT_SIGNED_URL_MINUTES`), never a
  public bucket URL or a static Nginx location (see `docker/nginx/default.conf`,
  which explicitly denies `/storage/private`).
- **QR-code + control-number verification**: printed forms (CAF, NOA, NTP,
  PO) embed a QR code resolving to `routes/web.php`'s `verify.document`
  route, letting anyone with the physical document confirm its authenticity
  and current status without touching the internal system.
- **Encrypted settings values**: SMTP/PhilGEPS/SMS/AWS credentials stored
  via `Settings > Integrations` are persisted through `SystemSetting`
  with sensitive fields passed through Laravel's `encrypter` before
  storage (see `IntegrationsTab`).

## 4. Audit trail

- **`workflow_histories`** (polymorphic): every `transitionTo()` call
  records `from_status`, `to_status`, `action`, `remarks`, `performed_by`,
  `performed_role`, `metadata` (JSON), and `performed_at`. This is
  immutable — there is no update/delete path exposed anywhere in the UI.
- **`activity_log`** (`spatie/laravel-activitylog`, via the `HasAuditLog`
  trait): field-level before/after diffs on every auditable model
  attribute (see each model's `auditableAttributes()`), plus the causer
  (who), IP, and user agent.
- **Retention**: `App\Console\Commands\PruneAuditLogs` runs daily at 02:00
  (see `routes/console.php`) and prunes `activity_log` rows older than
  `Settings > Security > audit_log_retention_days` (admin-configurable,
  never silently unbounded nor silently disabled).
- The **Audit Trail** module (`/audit-trail`, `Internal Auditor` role +
  `audit-trail.view` permission) gives read-only, filterable access to the
  full log without write/delete affordances, satisfying segregation of
  duties for COA review.

## 5. Input validation & injection protection

- All user input is validated through Livewire component `rules()` /
  Form Requests before touching a Service.
- Eloquent/query-builder parameter binding throughout — no raw
  string-interpolated SQL.
- File uploads are validated by MIME/size in each component (`WithFileUploads`)
  and stored under UUID-derived paths, never the client-supplied filename,
  preventing path traversal.
- CSRF protection is Laravel's default (`VerifyCsrfToken` middleware) for
  all state-changing web requests; the JSON API uses Sanctum bearer tokens
  instead of cookies.

## 6. Reporting a vulnerability

Route security reports to the agency's IT Security Officer / System Admin
account holder rather than filing a public issue. Include reproduction
steps and affected module/role.
