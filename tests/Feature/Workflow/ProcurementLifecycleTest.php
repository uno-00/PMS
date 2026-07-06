<?php

namespace Tests\Feature\Workflow;

use App\Enums\CafStatus;
use App\Enums\GaaStatus;
use App\Enums\PpmpStatus;
use App\Enums\PurchaseRequestStatus;
use App\Exceptions\BudgetExceededException;
use App\Models\Budget\BudgetAllocation;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Planning\Ppmp;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Settings\FiscalYear;
use App\Models\User;
use App\Services\Budget\BudgetAllocationService;
use App\Services\Procurement\PurchaseRequestService;
use App\Support\Roles;
use App\Support\Workflow\InvalidTransitionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end assertions for the "no overallocation allowed" and
 * "no duplicate utilization" rules that run through GAA -> Budget
 * Allocation -> PPMP -> Purchase Request (Phases 1, 3, 4, 5, 6), plus the
 * audit trail and event-driven CAF generation that tie the chain together.
 *
 * Relies on the standard DatabaseSeeder (via seed()), which leaves FY2026
 * fully walked through GAA -> APP -> PPMP -> PR -> CAF (see
 * SampleProcurementSeeder), so these tests exercise the *edges* of that
 * already-seeded state rather than re-building fixtures from scratch.
 */
class ProcurementLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_sample_lifecycle_reaches_caf_and_records_full_audit_trail(): void
    {
        $ppmp = Ppmp::query()->where('title', 'like', '%FY 2026%')->firstOrFail();
        $this->assertSame(PpmpStatus::Approved, $ppmp->status);

        $pr = PurchaseRequest::query()->where('ppmp_id', $ppmp->id)->firstOrFail();
        $this->assertSame(PurchaseRequestStatus::Approved, $pr->status);
        $this->assertNotNull($pr->certificateOfAvailabilityOfFunds, 'PurchaseRequestApproved event must auto-generate a CAF (Phase 6).');
        $this->assertSame(CafStatus::Generated, $pr->certificateOfAvailabilityOfFunds->status);

        // Every transition (Draft -> ... -> Approved) must be recorded for both PR and PPMP.
        $this->assertGreaterThanOrEqual(4, $pr->workflowHistories()->count());
        $this->assertGreaterThanOrEqual(4, $ppmp->workflowHistories()->count());
    }

    public function test_ppmp_item_fully_consumed_by_approved_pr_cannot_be_requested_again(): void
    {
        $ppmp = Ppmp::query()->where('title', 'like', '%FY 2026%')->firstOrFail();
        $consumedItem = $ppmp->items()->where('item_name', 'like', 'Desktop Computers%')->firstOrFail();

        $service = app(PurchaseRequestService::class);
        $available = $service->availableForPpmpItem($consumedItem);

        $this->assertSame(0.0, $available, 'The seeded PR already consumed the full ABC of this PPMP item.');

        $division = $consumedItem->ppmp->division;
        $fiscalYear = $consumedItem->ppmp->fiscalYear;
        $requester = User::query()->where('email', 'end.user@pms.gov.ph')->firstOrFail();

        $newPr = PurchaseRequest::query()->create([
            'fiscal_year_id' => $fiscalYear->id,
            'division_id' => $division->id,
            'ppmp_id' => $ppmp->id,
            'purpose' => 'Attempted duplicate utilization of an already-consumed PPMP item.',
            'status' => PurchaseRequestStatus::Draft,
            'requested_by' => $requester->id,
        ]);

        $this->expectException(BudgetExceededException::class);
        $service->addItem($newPr, $consumedItem, 1, 1000);
    }

    public function test_purchase_request_item_cannot_exceed_ppmp_items_remaining_balance(): void
    {
        $ppmp = Ppmp::query()->where('title', 'like', '%FY 2026%')->firstOrFail();
        $switchesItem = $ppmp->items()->where('item_name', 'like', 'Network Switches%')->firstOrFail();

        $service = app(PurchaseRequestService::class);
        $available = $service->availableForPpmpItem($switchesItem);
        $this->assertGreaterThan(0, $available, 'This PPMP item was not consumed by the seeded sample PR.');

        $requester = User::query()->where('email', 'end.user@pms.gov.ph')->firstOrFail();
        $pr = PurchaseRequest::query()->create([
            'fiscal_year_id' => $ppmp->fiscal_year_id,
            'division_id' => $ppmp->division_id,
            'ppmp_id' => $ppmp->id,
            'purpose' => 'Partial utilization within remaining PPMP balance.',
            'status' => PurchaseRequestStatus::Draft,
            'requested_by' => $requester->id,
        ]);

        // Requesting exactly the remaining balance succeeds...
        $item = $service->addItem($pr, $switchesItem, 1, $available);
        $this->assertNotNull($item->id);

        // ...but a second request against the now-fully-reserved item must fail,
        // proving in-flight PRs (not yet HOPE-approved) still reserve funds.
        $pr2 = PurchaseRequest::query()->create([
            'fiscal_year_id' => $ppmp->fiscal_year_id,
            'division_id' => $ppmp->division_id,
            'ppmp_id' => $ppmp->id,
            'purpose' => 'Should be rejected: overlaps with a reserved in-flight PR.',
            'status' => PurchaseRequestStatus::Draft,
            'requested_by' => $requester->id,
        ]);

        $this->expectException(BudgetExceededException::class);
        $service->addItem($pr2, $switchesItem, 1, 1);
    }

    public function test_budget_allocation_service_refuses_sub_allocation_beyond_remaining_balance(): void
    {
        $root = BudgetAllocation::query()->whereNull('parent_id')->firstOrFail();
        $service = app(BudgetAllocationService::class);
        $user = User::query()->where('email', 'budget.officer@pms.gov.ph')->firstOrFail();

        $this->expectException(BudgetExceededException::class);
        $service->allocate($root, [
            'level' => 'division',
            'division_id' => $root->division_id,
            'allocated_amount' => $root->remaining_balance + 1,
        ], $user);
    }

    public function test_budget_allocation_utilize_and_release_keep_balance_consistent(): void
    {
        $allocation = BudgetAllocation::query()->whereNull('parent_id')->firstOrFail();
        $service = app(BudgetAllocationService::class);
        $before = $allocation->remaining_balance;

        $service->utilize($allocation, 1000);
        $this->assertEquals($before - 1000, $allocation->fresh()->remaining_balance);

        $service->release($allocation, 1000);
        $this->assertEquals($before, $allocation->fresh()->remaining_balance);
    }

    public function test_invalid_workflow_transition_is_rejected(): void
    {
        $fiscalYear = FiscalYear::query()->create([
            'year' => 2027,
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
            'status' => 'active',
            'is_current' => false,
        ]);

        $draftGaa = GeneralAppropriationsAct::query()->create([
            'fiscal_year_id' => $fiscalYear->id,
            'title' => 'Draft GAA for transition-guard test',
            'total_amount' => 1000,
            'status' => GaaStatus::Draft,
        ]);

        // Draft -> Approved skips the mandatory Validated step and must be rejected.
        $this->expectException(InvalidTransitionException::class);
        $draftGaa->transitionTo(GaaStatus::Approved);
    }

    public function test_non_bac_role_cannot_access_settings_or_audit_trail(): void
    {
        $endUser = User::query()->where('email', 'end.user@pms.gov.ph')->firstOrFail();

        $this->actingAs($endUser)->get(route('settings.index'))->assertForbidden();
        $this->actingAs($endUser)->get(route('audit-trail.index'))->assertForbidden();
    }

    public function test_super_admin_bypasses_all_permission_checks(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@pms.gov.ph')->firstOrFail();
        $this->assertTrue($superAdmin->hasRole(Roles::SUPER_ADMIN));

        $this->actingAs($superAdmin)->get(route('settings.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('audit-trail.index'))->assertOk();
    }
}
