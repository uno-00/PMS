# Test Accounts

Seeded by `database/seeders/UserSeeder.php` and
`database/seeders/BidderPortalSeeder.php` (`php artisan migrate --seed`).
**Local/demo use only** — rotate or disable these before any real
deployment.

Default password for every account: **`Passw0rd!2026`**

## Internal agency portal (`/login`)

| Role | Email | Notes |
|---|---|---|
| Super Admin | `superadmin@pms.gov.ph` | Full access, bypasses all Policy checks |
| System Admin | `sysadmin@pms.gov.ph` | Settings, users, fiscal years, audit trail |
| BAC Chairperson | `bac.chair@pms.gov.ph` | Approves post-qualification, awards, NOA/NTP |
| BAC Secretariat | `bac.secretariat@pms.gov.ph` | Runs PhilGEPS posting, bidding, evaluation, bidder verification |
| BAC Member | `bac.member@pms.gov.ph` | Bid opening/evaluation participation |
| Budget Officer | `budget.officer@pms.gov.ph` | GAA upload/validation, budget allocation, CAF |
| Planning Officer | `planning.officer@pms.gov.ph` | APP consolidation, PPMP planning review |
| Accounting Officer | `accounting.officer@pms.gov.ph` | CAF certification, payment visibility |
| Supply Officer | `supply.officer@pms.gov.ph` | Purchase Requests, Purchase Orders, deliveries |
| Division Chief | `division.chief@pms.gov.ph` | PPMP/PR division-level review |
| End User | `end.user@pms.gov.ph` | PPMP/PR requester |
| HOPE | `hope@pms.gov.ph` | Final PR/PPMP/APP/Award approval |
| Internal Auditor | `auditor@pms.gov.ph` | Read-only Audit Trail + all `.view` permissions |
| Inspector | `inspector@pms.gov.ph` | Delivery inspection |
| Property Officer | `property.officer@pms.gov.ph` | Delivery/acceptance recording |
| Cashier | `cashier@pms.gov.ph` | Payment recording |
| Finance | `finance@pms.gov.ph` | Payment + CAF visibility |
| Viewer | `viewer@pms.gov.ph` | Read-only across all `.view` permissions |

## Bidder Portal (`/bidder/login`)

| Role | Email | Notes |
|---|---|---|
| Bidder | `bidder@supplier.com` | Pre-verified sample supplier ("Juan Dela Cruz Trading Corp."), can browse opportunities, order bid documents, ask clarifications, submit bids, and track awards |

## Sample data

`SampleProcurementSeeder` walks FY2026 through
**GAA → Budget Allocation → APP → PPMP → Purchase Request → CAF** so every
role has something real to look at immediately after seeding — see
`docs/ARCHITECTURE.md` for the full lifecycle diagram.
