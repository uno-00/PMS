<?php

use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\GaaTemplateController;
use App\Http\Controllers\Pdf\CafPdfController;
use App\Http\Controllers\Pdf\GaaPdfController;
use App\Http\Controllers\Pdf\NoticeOfAwardPdfController;
use App\Http\Controllers\Pdf\NoticeToProceedPdfController;
use App\Http\Controllers\Pdf\MarketScopingPdfController;
use App\Http\Controllers\Pdf\PpmpConsolidationBp2020PdfController;
use App\Http\Controllers\Pdf\PpmpConsolidationPdfController;
use App\Http\Controllers\Pdf\PpmpConsolidationWfpPdfController;
use App\Http\Controllers\Pdf\PpmpPdfController;
use App\Http\Controllers\PpmpConsolidationExportController;
use App\Http\Controllers\Pdf\ProjectProposalPdfController;
use App\Http\Controllers\Pdf\PurchaseOrderPdfController;
use App\Http\Controllers\Pdf\PurchaseRequestPdfController;
use App\Http\Controllers\Verify\DocumentVerificationController;
use App\Livewire\AuditTrail\Index as AuditTrailIndex;
use App\Livewire\Auth\ForcePasswordChange;
use App\Livewire\Auth\Login;
use App\Livewire\Bac\BidEvaluationShow;
use App\Livewire\Bac\CalendarEventForm;
use App\Livewire\Bac\CalendarEventShow;
use App\Livewire\Bac\CalendarIndex;
use App\Livewire\Bac\MemberIndex;
use App\Livewire\Bac\PhilgepsForm;
use App\Livewire\Bac\PhilgepsIndex;
use App\Livewire\Bac\PhilgepsShow;
use App\Livewire\Bac\ProcurementIndex;
use App\Livewire\Bac\ProcurementShow;
use App\Livewire\Budget\AllocationForm;
use App\Livewire\Budget\AllocationIndex;
use App\Livewire\Caf\CafIndex;
use App\Livewire\Caf\CafShow;
use App\Livewire\Dashboard\Show as DashboardShow;
use App\Livewire\Gaa\Index as GaaIndex;
use App\Livewire\Gaa\Show as GaaShow;
use App\Livewire\Gaa\Upload as GaaUpload;
use App\Livewire\Help\Index as HelpIndex;
use App\Livewire\Payment\PaymentForm;
use App\Livewire\Payment\PaymentIndex;
use App\Livewire\Payment\PaymentShow;
use App\Livewire\Planning\AppShow;
use App\Livewire\Planning\MarketScopingForm;
use App\Livewire\Planning\MarketScopingIndex;
use App\Livewire\Planning\MarketScopingShow;
use App\Livewire\Planning\PpmpConsolidationIndex;
use App\Livewire\Planning\PpmpConsolidationWizard;
use App\Livewire\Planning\PpmpForm;
use App\Livewire\Planning\PpmpIndex;
use App\Livewire\Planning\PpmpShow;
use App\Livewire\Planning\ProjectProposalForm;
use App\Livewire\Planning\ProjectProposalIndex;
use App\Livewire\Planning\ProjectProposalShow;
use App\Livewire\Planning\ProjectProposalWizardMarketScoping;
use App\Livewire\Planning\ProjectProposalWizardPpmp;
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
            Route::get('/template/download', GaaTemplateController::class)->name('template');
            Route::get('/{gaa}', GaaShow::class)->name('show');
            Route::get('/{gaa}/print', GaaPdfController::class)->name('print');
        });

        // Phase 2: Annual Procurement Plan (one per fiscal year)
        Route::get('/app/{fiscalYear?}', AppShow::class)->name('app.show');

        // Phase 3: Budget Allocation
        Route::prefix('budget-allocations')->name('budget-allocations.')->group(function () {
            Route::get('/', AllocationIndex::class)->name('index');
            Route::get('/create', AllocationForm::class)->name('create');
            Route::get('/{allocation}/edit', AllocationForm::class)->name('edit');
        });

        // Market Scoping Checklist
        Route::prefix('market-scoping')->name('market-scoping.')->group(function () {
            Route::get('/', MarketScopingIndex::class)->name('index');
            Route::get('/create', MarketScopingForm::class)->name('create');
            Route::get('/{marketScoping}/edit', MarketScopingForm::class)->name('edit');
            Route::get('/{marketScoping}', MarketScopingShow::class)->name('show');
            Route::get('/{marketScoping}/print', MarketScopingPdfController::class)->name('print');
        });

        // Project Proposal (NMP-PP-01) — 3-step Indicative PPMP pipeline
        Route::prefix('project-proposals')->name('project-proposals.')->group(function () {
            Route::get('/', ProjectProposalIndex::class)->name('index');
            Route::get('/create', ProjectProposalForm::class)->name('create');
            Route::get('/{projectProposal}/edit', ProjectProposalForm::class)->name('edit');
            Route::get('/{projectProposal}/market-scoping', ProjectProposalWizardMarketScoping::class)->name('wizard.market-scoping');
            Route::get('/{projectProposal}/indicative-ppmp', ProjectProposalWizardPpmp::class)->name('wizard.indicative-ppmp');
            Route::get('/{projectProposal}', ProjectProposalShow::class)->name('show');
            Route::get('/{projectProposal}/print', ProjectProposalPdfController::class)->name('print');
        });

        // Phase 4: PPMP
        Route::prefix('ppmps')->name('ppmps.')->group(function () {
            Route::get('/indicative', PpmpIndex::class)->defaults('documentType', 'indicative')->name('indicative');
            Route::get('/final', PpmpIndex::class)->defaults('documentType', 'final')->name('final');
            Route::get('/', PpmpIndex::class)->name('index');
            Route::get('/create', PpmpForm::class)->name('create');
            Route::get('/{ppmp}/edit', PpmpForm::class)->name('edit');
            Route::get('/{ppmp}', PpmpShow::class)->name('show');
            Route::get('/{ppmp}/print', PpmpPdfController::class)->name('print');
        });

        // PPMP Consolidation (agency-wide Indicative/Final rollup)
        Route::prefix('ppmp-consolidations')->name('ppmp-consolidations.')->group(function () {
            Route::get('/', PpmpConsolidationIndex::class)->name('index');
            Route::get('/create', PpmpConsolidationWizard::class)->name('create');
            Route::get('/{consolidation}/wizard', PpmpConsolidationWizard::class)->name('wizard');
            Route::get('/{consolidation}/print', PpmpConsolidationPdfController::class)->name('print');
            Route::get('/{consolidation}/bp2020/print', PpmpConsolidationBp2020PdfController::class)->name('bp2020.print');
            Route::get('/{consolidation}/wfp/print', PpmpConsolidationWfpPdfController::class)->name('wfp.print');
            Route::get('/{consolidation}/export/{format?}', PpmpConsolidationExportController::class)->name('export');
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
        Route::prefix('bac-calendar')->name('bac-calendar.')->group(function () {
            Route::get('/', CalendarIndex::class)->name('index');
            Route::get('/create', CalendarEventForm::class)->name('create');
            Route::get('/{calendarEvent}/edit', CalendarEventForm::class)->name('edit');
            Route::get('/{calendarEvent}', CalendarEventShow::class)->name('show');
        });
        Route::get('/bac-members', MemberIndex::class)->name('bac-members.index');
        Route::prefix('philgeps')->name('philgeps.')->group(function () {
            Route::get('/', PhilgepsIndex::class)->name('index');
            Route::get('/create', PhilgepsForm::class)->name('create');
            Route::get('/{posting}/edit', PhilgepsForm::class)->name('edit');
            Route::get('/{posting}', PhilgepsShow::class)->name('show');
        });

        Route::prefix('bidders')->name('bidders.')->group(function () {
            Route::get('/', BidderIndex::class)->name('index');
            Route::get('/{bidder}', BidderShow::class)->name('show');
        });

        Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
            Route::get('/', PurchaseOrderIndex::class)->name('index');
            Route::get('/{purchase_order}', PurchaseOrderShow::class)->name('show');
            Route::get('/{purchase_order}/print', PurchaseOrderPdfController::class)->name('print');
        });

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', PaymentIndex::class)->name('index');
            Route::get('/create', PaymentForm::class)->name('create');
            Route::get('/{payment}/edit', PaymentForm::class)->name('edit');
            Route::get('/{payment}', PaymentShow::class)->name('show');
        });

        Route::get('/reports', ReportsIndex::class)->name('reports.index');
        Route::get('/audit-trail', AuditTrailIndex::class)->name('audit-trail.index');

        Route::get('/settings/{tab?}', SettingsIndex::class)->name('settings.index');

        Route::get('/help/{module?}', HelpIndex::class)->name('help.index');

        Route::get('/documents/{document}/download', DocumentDownloadController::class)->name('documents.download');
    });
});

require __DIR__.'/bidder_portal.php';
