<?php

namespace Tests\Feature\Workflow;

use App\Enums\ProcurementCaseStatus;
use App\Enums\PurchaseOrderStatus;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Supplier\Bidder;
use App\Models\User;
use App\Notifications\Bac\PhilgepsPostingPublishedNotification;
use App\Services\Bac\AwardService;
use App\Services\Bac\BiddingService;
use App\Services\Bac\PhilgepsPostingService;
use App\Services\Bac\ProcurementCaseService;
use App\Services\Procurement\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Walks Phases 7-17 end to end for a single approved Purchase Request:
 * BAC case opening -> PhilGEPS posting -> bid submission -> opening ->
 * evaluation -> post-qualification -> Notice of Award -> Notice to
 * Proceed -> Purchase Order -> Delivery -> Inspection -> Acceptance ->
 * Payment, asserting every workflow gate along the way.
 */
class BiddingAndAwardLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_full_bac_to_payment_lifecycle(): void
    {
        Notification::fake();

        $bacSecretariat = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->firstOrFail();
        $bacChair = User::query()->where('email', 'bac.chair@pms.gov.ph')->firstOrFail();
        $supplyOfficer = User::query()->where('email', 'supply.officer@pms.gov.ph')->firstOrFail();
        $inspector = User::query()->where('email', 'inspector@pms.gov.ph')->firstOrFail();
        $cashier = User::query()->where('email', 'cashier@pms.gov.ph')->firstOrFail();
        $bidder = Bidder::query()->where('email', 'bidder@supplier.com')->firstOrFail();

        $pr = PurchaseRequest::query()->whereHas('items')->firstOrFail();
        $this->assertTrue($pr->status->value === 'approved', 'Fixture PR must already be HOPE-approved.');

        // Phase 7: BAC Secretariat opens the procurement case.
        $case = app(ProcurementCaseService::class)->openCase($pr, $bacSecretariat);
        $this->assertSame(ProcurementCaseStatus::Planning, $case->status);

        // Phase 8: PhilGEPS posting (manual mode) + bidder notification.
        $posting = app(PhilgepsPostingService::class)->post($case, [
            'reference_no' => 'PHILGEPS-2026-000456',
            'posting_date' => now(),
            'closing_date' => now()->addDays(7),
        ], $bacSecretariat);
        $this->assertSame(ProcurementCaseStatus::Posted, $case->fresh()->status);
        Notification::assertSentTo($bidder, PhilgepsPostingPublishedNotification::class);

        // Phase 11: bidder submits an electronic bid before the deadline.
        $bid = app(BiddingService::class)->submitBid($case, $bidder, $bacSecretariat);
        $this->assertSame(1, $bid->version);

        // Closing the posting moves the case into Bidding.
        app(PhilgepsPostingService::class)->close($posting);
        $case->refresh();
        $this->assertSame(ProcurementCaseStatus::Bidding, $case->status);

        // Phase 12: BAC opens bids.
        app(BiddingService::class)->openBids($case, $bacSecretariat);
        $this->assertSame('opened', $bid->fresh()->status);

        // Phase 13: BAC-TWG evaluates.
        app(BiddingService::class)->evaluate($bid, $bacSecretariat, ['eligibility' => true, 'technical' => true, 'financial' => true], 95.5, 1, 'award');
        $case = app(ProcurementCaseService::class)->moveTo($case, ProcurementCaseStatus::Evaluation, 'Bid evaluated by BAC-TWG.');

        // Phase 14: post-qualification.
        app(BiddingService::class)->processPostQualification($case, $bidder, $bacSecretariat, true, 'Site visit conducted, documents validated.', 'passed');
        $case = app(ProcurementCaseService::class)->moveTo($case, ProcurementCaseStatus::PostQualification, 'Post-qualification in progress.');
        $case = app(ProcurementCaseService::class)->moveTo($case, ProcurementCaseStatus::Awarded, 'Post-qualification passed.');

        // Phase 15: Notice of Award.
        $noa = app(AwardService::class)->issueNoticeOfAward($case, $bidder, $bacChair, (float) $pr->total_amount);
        $this->assertSame(ProcurementCaseStatus::Awarded, $case->fresh()->status);
        app(AwardService::class)->respond($noa, true);
        $this->assertSame('accepted', $noa->fresh()->status->value);

        // Phase 16: Notice to Proceed.
        app(AwardService::class)->issueNoticeToProceed($case, $bacChair, now()->addDay()->toDateString(), 30);
        $this->assertSame(ProcurementCaseStatus::NtpIssued, $case->fresh()->status);

        // Phase 17: Purchase Order + Delivery + Inspection + Acceptance + Payment.
        $po = app(PurchaseOrderService::class)->create([
            'procurement_id' => $case->id,
            'purchase_request_id' => $pr->id,
            'bidder_id' => $bidder->id,
            'mode_of_procurement_id' => $case->mode_of_procurement_id,
            'subtotal' => $pr->total_amount,
            'tax_amount' => 0,
            'total_amount' => $pr->total_amount,
            'delivery_date' => now()->addDays(15),
            'delivery_place' => 'Main Office Warehouse',
        ], $supplyOfficer);
        $this->assertSame(PurchaseOrderStatus::Draft, $po->status);

        app(PurchaseOrderService::class)->approve($po, $bacChair);
        $this->assertSame(PurchaseOrderStatus::Approved, $po->fresh()->status);
        $this->assertSame(ProcurementCaseStatus::PoIssued, $case->fresh()->status);

        $delivery = app(PurchaseOrderService::class)->recordDelivery($po, $supplyOfficer, [
            'delivery_date' => now(),
            'delivery_receipt_no' => 'DR-2026-0001',
            'remarks' => 'Complete delivery per PO specification.',
        ]);
        $this->assertSame(PurchaseOrderStatus::Delivered, $po->fresh()->status);

        app(PurchaseOrderService::class)->recordInspection($delivery, $inspector, 'passed', 'All items conform to specification.');
        $this->assertSame(PurchaseOrderStatus::Inspected, $po->fresh()->status);

        app(PurchaseOrderService::class)->recordAcceptance($delivery, $supplyOfficer, 'Accepted in good order.');
        $po = $po->fresh();
        $this->assertSame(PurchaseOrderStatus::Accepted, $po->status);
        $this->assertSame(ProcurementCaseStatus::Completed, $case->fresh()->status);

        app(PurchaseOrderService::class)->recordPayment($po, $cashier, [
            'amount' => $po->total_amount,
            'or_no' => 'OR-2026-0001',
            'payment_date' => now(),
            'status' => 'released',
        ]);
        $this->assertSame(PurchaseOrderStatus::Paid, $po->fresh()->status);

        // Full audit trail must exist end-to-end.
        $this->assertGreaterThanOrEqual(4, $case->fresh()->workflowHistories()->count());
        $this->assertGreaterThanOrEqual(6, $po->fresh()->workflowHistories()->count());
    }
}
