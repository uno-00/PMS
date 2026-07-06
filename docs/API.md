# API Documentation

The system exposes a small, read-focused, versioned JSON API intended for
external integrations (COA reporting tools, agency BI dashboards, future
mobile clients), separate from the Livewire-driven web UI that handles all
write/workflow operations.

- **Base URL**: `/api/v1`
- **Auth**: Laravel Sanctum personal access tokens (`Authorization: Bearer <token>`)
- **Authorization**: identical Policies as the web app — a token inherits
  its owning user's role/permissions, so `PpmpPolicy`, `PurchaseRequestPolicy`,
  etc. are enforced on every request via `$this->authorize(...)`.
- **OpenAPI spec**: [`storage/api-docs/openapi.yaml`](../storage/api-docs/openapi.yaml)
  (OpenAPI 3.0 — import into Swagger UI, Postman, or Insomnia)

## Issuing a token

Tokens aren't self-service yet in the UI; issue one via Tinker for now:

```bash
php artisan tinker
>>> $user = \App\Models\User::where('email', 'budget.officer@pms.gov.ph')->first();
>>> $user->createToken('reporting-integration')->plainTextToken
```

## Endpoints (v1)

| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/fiscal-years` | List fiscal years |
| GET | `/api/v1/ppmps` | List PPMPs (filters: `fiscal_year_id`, `division_id`, `status`) |
| GET | `/api/v1/ppmps/{ppmp}` | PPMP detail with items |
| GET | `/api/v1/purchase-requests` | List Purchase Requests (filter: `status`) |
| GET | `/api/v1/purchase-requests/{purchaseRequest}` | Purchase Request detail with items |

All list endpoints return Laravel's standard paginated JSON:API-ish shape
(`data`, `links`, `meta`).

## Example

```bash
curl -H "Authorization: Bearer $TOKEN" \
     -H "Accept: application/json" \
     https://procurement.agency.gov.ph/api/v1/purchase-requests?status=approved
```

## Extending the API

New read-only resources follow the existing pattern in
`app/Http/Controllers/Api/V1/*Controller.php` +
`app/Http/Resources/*Resource.php`:

1. Add a route in `routes/api/v1.php`.
2. Add a controller that calls `$this->authorize(...)` then delegates to
   the existing Eloquent model / Service — **never** duplicate business
   rules in an API controller.
3. Add a `JsonResource` for the response shape.
4. Document the new path/schema in `storage/api-docs/openapi.yaml`.
5. Add a feature test asserting both the 200 (authorized) and 403
   (unauthorized) cases, following `tests/Feature/Workflow/ProcurementLifecycleTest.php`.
