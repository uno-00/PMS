# Roles, Permissions & Dashboards

Source of truth for the **seed data**: `config/rbac.php` (loaded once by
`PermissionSeeder`; after seeding, a Super Admin can edit roles/permissions
live from **Settings** without a code deploy — re-running the seeder is
idempotent and only *adds* newly introduced permissions, never revokes
admin customizations).

## Dashboards per role

| Role | Dashboard |
|---|---|
| Super Admin, System Admin, HOPE | Executive |
| BAC Chairperson, BAC Secretariat, BAC Member | BAC |
| Budget Officer, Accounting Officer, Cashier, Finance | Budget |
| Planning Officer | Planning |
| Supply Officer, Division Chief, End User, Inspector, Property Officer | Division |
| Internal Auditor, Viewer | Analytics |
| Bidder | Bidder (Portal) |

Each dashboard surfaces role-relevant widgets: Budget Utilization,
Procurement Status, PPMP/APP Status, Purchase Requests, Upcoming BAC
Activities, Award Summary, Supplier Performance, Cycle Time, Budget
Remaining, Monthly Procurement — rendered as Bar/Pie/Line charts via
Chart.js, each with Export Excel / Export PDF actions.

## Permission matrix (module-level summary)

| Module | Full CRUD + Approve | View only |
|---|---|---|
| GAA | Budget Officer | System Admin (via `.view` in dashboards) |
| Budget Allocation | Budget Officer | — |
| APP | Planning Officer (consolidate/review) | Budget Officer, HOPE (approve) |
| PPMP | Division Chief (draft), Planning Officer, Budget Officer, BAC Secretariat (consolidate) | End User |
| Purchase Request | End User/Division Chief (create), Planning/Budget/HOPE (approve chain) | Supply Officer |
| CAF | Budget Officer (generate), Accounting Officer (certify), HOPE-tier approve | Finance |
| BAC Calendar | BAC Chairperson/Secretariat (manage) | BAC Member |
| PhilGEPS Posting | BAC Secretariat | BAC Chairperson, BAC Member |
| Bidder Directory | BAC Chairperson/Secretariat (verify/suspend) | BAC Member |
| Bid Documents / Submission | BAC Secretariat, Bidder (self-service) | — |
| Bid Opening / Evaluation | BAC Secretariat/Member (conduct/evaluate) | BAC Chairperson |
| Post-Qualification | BAC Secretariat (process), BAC Chairperson (approve) | BAC Member |
| Award / NOA / NTP | BAC Secretariat (generate), BAC Chairperson (approve) | Supply Officer, HOPE |
| Purchase Order | Supply Officer, BAC Secretariat (create); HOPE (approve) | BAC Chairperson |
| Delivery / Inspection / Acceptance | Supply Officer, Inspector, Property Officer | — |
| Payment | Cashier, Finance | Accounting Officer |
| Reports & Analytics | Every internal role | — |
| Settings | Super Admin, System Admin | — |
| Audit Trail | Super Admin, System Admin, Internal Auditor | — |
| Help | Every role, including Bidder | — |

`Super Admin` holds the wildcard `*` permission (`Roles::SUPER_ADMIN => ['*']`)
and additionally bypasses every Policy via `BasePolicy::before()`.

## Adding a new role or permission

1. Add the permission string to the relevant `App\Support\Permissions::forModule()`
   entry (or a bespoke string) and to whichever role(s) in `config/rbac.php`
   should hold it.
2. Re-run `php artisan db:seed --class=PermissionSeeder` (safe/idempotent in
   any environment — it will not remove permissions an admin already
   customized in the `roles`/`permissions` tables).
3. Guard the corresponding Livewire `mount()`/action and (if applicable)
   the matching `Policy` method with the new permission string.
4. Add/extend a feature test asserting both the 200 (authorized) and 403
   (unauthorized) outcomes, following the pattern in
   `tests/Feature/Workflow/ProcurementLifecycleTest.php`.
