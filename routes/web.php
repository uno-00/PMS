<?php

use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\Pdf\CafPdfController;
use App\Http\Controllers\Pdf\GaaPdfController;
use App\Http\Controllers\Pdf\NoticeOfAwardPdfController;
use App\Http\Controllers\Pdf\NoticeToProceedPdfController;
use App\Http\Controllers\Pdf\MarketScopingPdfController;
use App\Http\Controllers\Pdf\PpmpPdfController;
use App\Http\Controllers\Pdf\PurchaseOrderPdfController;
use App\Http\Controllers\Pdf\PurchaseRequestPdfController;
use App\Http\Controllers\Verify\DocumentVerificationController;
use App\Livewire\AuditTrail\Index as AuditTrailIndex;
use App\Livewire\Auth\ForcePasswordChange;
use App\Livewire\Auth\Login;
use App\Livewire\Bac\BidEvaluationShow;
use App\Livewire\Bac\CalendarIndex;
use App\Livewire\Bac\MemberIndex;
use App\Livewire\Bac\PhilgepsIndex;
use App\Livewire\Bac\ProcurementIndex;
use App\Livewire\Bac\ProcurementShow;
use App\Livewire\Budget\AllocationIndex;
use App\Livewire\Caf\CafIndex;
use App\Livewire\Caf\CafShow;
use App\Livewire\Dashboard\Show as DashboardShow;
use App\Livewire\Gaa\Index as GaaIndex;
use App\Livewire\Gaa\Show as GaaShow;
use App\Livewire\Gaa\Upload as GaaUpload;
use App\Livewire\Help\Index as HelpIndex;
use App\Livewire\Payment\PaymentIndex;
use App\Livewire\Planning\AppShow;
use App\Livewire\Planning\MarketScopingForm;
use App\Livewire\Planning\MarketScopingIndex;
use App\Livewire\Planning\MarketScopingShow;
use App\Livewire\Planning\PpmpForm;
use App\Livewire\Planning\PpmpIndex;
use App\Livewire\Planning\PpmpShow;
use App\Livewire\PurchaseOrder\PurchaseOrderIndex;
use App\Livewire\PurchaseOrder\PurchaseOrderShow;
use App\Livewire\PurchaseRequest\PurchaseRequestForm;
use App\Livewire\PurchaseRequest\PurchaseRequestIndex;
use App\Livewire\PurchaseRequest\PurchaseRequestShow;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\Supplier\BidderIndex;
use App\Livewire\Supplier\BidderShow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::get('/verify/{type}/{code}', DocumentVerificationController::class)->name('verify.document');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::get('/force-password-change', ForcePasswordChange::class)->name('password.force');

    Route::middleware('force.password.change')->group(function () {

        Route::get('/dashboard', function () {
            return redirect()->to(route(auth()->user()->dashboardRoute(), [], false));
        })->name('dashboard');

        foreach (['executive', 'budget', 'bac', 'planning', 'division', 'supplier', 'analytics'] as $type) {
            Route::get("/dashboard/{$type}", DashboardShow::class)
                ->name("dashboard.{$type}")
                ->defaults('type', $type);
        }

        // Phase 1: General Appropriations Act
        Route::prefix('gaa')->name('gaa.')->group(function () {
            Route::get('/', GaaIndex::class)->name('index');
            Route::get('/create', GaaUpload::class)->name('create');
            Route::get('/{gaa}', GaaShow::class)->name('show');
            Route::get('/{gaa}/print', GaaPdfController::class)->name('print');
        });

        // Phase 2: Annual Procurement Plan (one per fiscal year)
        Route::get('/app/{fiscalYear?}', AppShow::class)->name('app.show');

        // Phase 3: Budget Allocation
        Route::get('/budget-allocations', AllocationIndex::class)->name('budget-allocations.index');

        // Market Scoping Checklist
        Route::prefix('market-scoping')->name('market-scoping.')->group(function () {
            Route::get('/', MarketScopingIndex::class)->name('index');
            Route::get('/create', MarketScopingForm::class)->name('create');
            Route::get('/{marketScoping}/edit', MarketScopingForm::class)->name('edit');
            Route::get('/{marketScoping}', MarketScopingShow::class)->name('show');
            Route::get('/{marketScoping}/print', MarketScopingPdfController::class)->name('print');
        });

        // Phase 4: PPMP
        Route::prefix('ppmps')->name('ppmps.')->group(function () {
            Route::get('/', PpmpIndex::class)->name('index');
            Route::get('/create', PpmpForm::class)->name('create');
            Route::get('/{ppmp}/edit', PpmpForm::class)->name('edit');
            Route::get('/{ppmp}', PpmpShow::class)->name('show');
            Route::get('/{ppmp}/print', PpmpPdfController::class)->name('print');
        });

        // Phase 5: Purchase Request
        Route::prefix('purchase-requests')->name('purchase-requests.')->group(function () {
            Route::get('/', PurchaseRequestIndex::class)->name('index');
            Route::get('/create', PurchaseRequestForm::class)->name('create');
            Route::get('/{purchase_request}/edit', PurchaseRequestForm::class)->name('edit');
            Route::get('/{purchase_request}', PurchaseRequestShow::class)->name('show');
            Route::get('/{purchase_request}/print', PurchaseRequestPdfController::class)->name('print');
        });

        // Phase 6: Certificate of Availability of Funds
        Route::prefix('cafs')->name('cafs.')->group(function () {
            Route::get('/', CafIndex::class)->name('index');
            Route::get('/{caf}', CafShow::class)->name('show');
            Route::get('/{caf}/print', CafPdfController::class)->name('print');
        });

        // Phases 7-17: BAC, PhilGEPS, Bidding, Award, PO
        Route::prefix('procurements')->name('procurements.')->group(function () {
            Route::get('/', ProcurementIndex::class)->name('index');
            Route::get('/{procurement}', ProcurementShow::class)->name('show');
            Route::get('/{procurement}/evaluation', BidEvaluationShow::class)->name('evaluation');
            Route::get('/{procurement}/noa/print', NoticeOfAwardPdfController::class)->name('noa.print');
            Route::get('/{procurement}/ntp/print', NoticeToProceedPdfController::class)->name('ntp.print');
        });
        Route::get('/bac-calendar', CalendarIndex::class)->name('bac-calendar.index');
        Route::get('/bac-members', MemberIndex::class)->name('bac-members.index');
        Route::get('/philgeps-postings', PhilgepsIndex::class)->name('philgeps.index');

        Route::prefix('bidders')->name('bidders.')->group(function () {
            Route::get('/', BidderIndex::class)->name('index');
            Route::get('/{bidder}', BidderShow::class)->name('show');
        });

        Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
            Route::get('/', PurchaseOrderIndex::class)->name('index');
            Route::get('/{purchase_order}', PurchaseOrderShow::class)->name('show');
            Route::get('/{purchase_order}/print', PurchaseOrderPdfController::class)->name('print');
        });

        Route::get('/payments', PaymentIndex::class)->name('payments.index');

        Route::get('/reports', ReportsIndex::class)->name('reports.index');
        Route::get('/audit-trail', AuditTrailIndex::class)->name('audit-trail.index');

        Route::get('/settings/{tab?}', SettingsIndex::class)->name('settings.index');

        Route::get('/help/{module?}', HelpIndex::class)->name('help.index');

        Route::get('/documents/{document}/download', DocumentDownloadController::class)->name('documents.download');
    });
});

require __DIR__.'/bidder_portal.php';
