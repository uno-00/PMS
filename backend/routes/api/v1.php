<?php

use App\Http\Controllers\Api\V1\FiscalYearApiController;
use App\Http\Controllers\Api\V1\PpmpApiController;
use App\Http\Controllers\Api\V1\PurchaseRequestApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 (Sanctum token auth)
|--------------------------------------------------------------------------
| Read-focused endpoints supporting external reporting/BI integrations
| and the Bidder Portal SPA (future). Full reference lives in
| storage/api-docs/openapi.yaml (see docs/API.md).
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('fiscal-years', [FiscalYearApiController::class, 'index'])->name('fiscal-years.index');
    Route::get('ppmps', [PpmpApiController::class, 'index'])->name('ppmps.index');
    Route::get('ppmps/{ppmp}', [PpmpApiController::class, 'show'])->name('ppmps.show');
    Route::get('purchase-requests', [PurchaseRequestApiController::class, 'index'])->name('purchase-requests.index');
    Route::get('purchase-requests/{purchaseRequest}', [PurchaseRequestApiController::class, 'show'])->name('purchase-requests.show');
});
