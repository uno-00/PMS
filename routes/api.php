<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes ("API Ready" per system requirements)
|--------------------------------------------------------------------------
|
| Versioned, Sanctum-protected JSON API exposing read access to key
| procurement resources for integrations (COA reporting tools, agency
| BI dashboards, mobile apps). See routes/api/v1.php per-module files
| and docs/API.md / the generated Swagger/OpenAPI spec at
| storage/api-docs/openapi.yaml.
|
*/

Route::prefix('v1')->name('api.v1.')->group(base_path('routes/api/v1.php'));
