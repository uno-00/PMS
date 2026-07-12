<?php

namespace Database\Seeders;

use App\Enums\CafStatus;
use App\Enums\ProcurementCaseStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Bac\BacCalendarEvent;
use App\Models\Bac\Procurement;
use App\Models\Planning\Ppmp;
use App\Models\Planning\PpmpItem;
use App\Models\Procurement\CertificateOfAvailabilityOfFunds;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Supplier\Bidder;
use App\Models\User;
use App\Services\Bac\AwardService;
use App\Services\Bac\BiddingService;
use App\Services\Bac\ClarificationService;
use App\Services\Bac\PhilgepsPostingService;
use App\Services\Bac\ProcurementCaseService;
use App\Services\Procurement\CafService;
use App\Services\Procurement\PurchaseOrderService;
use App\Services\Supplier\BidDocumentOrderService;
use Illuminate\Database\Seeder;

/**
 * Seeds BAC, bidding, award, and delivery sample data (Phases 7–17):
 * - completes CAF workflow on the seeded Purchase Request
 * - one fully completed public-bidding procurement (desktop computers)
 * - one active PhilGEPS posting with clarifications and bid submissions (network switches)
 * - one procurement at bid evaluation (office equipment SVP)
 */
class SampleBacProcurementSeeder extends Seeder
{
    protected User $bacSecretariat;

    protected User $bacChair;

    protected User $supplyOfficer;

    protected User $inspector;

    protected User $cashier;

    protected User $accountingOfficer;

    protected User $hope;

    protected User $endUser;

    protected Bidder $winningBidder;

    protected Bidder $runnerUpBidder;

    protected Bidder $thirdBidder;

    public function run(): void
    {
        if (! $this->loadDependencies()) {
            return;
        }

        if (! app()->runningUnitTests()) {
            $this->completeCafWorkflow();
        }

        $completedPr = PurchaseRequest::query()
            ->whereHas('items')
            ->where('status', PurchaseRequestStatus::Approved)
            ->first();

        if ($completedPr && ! app()->runningUnitTests()) {
            $this->seedCompletedPublicBiddingCase($completedPr);
        }

        $postedPr = $this->ensureSamplePurchaseRequest(
            itemNo: 3,
            itemName: 'Multifunction Printers (A3 capable)',
            description: 'Departmental printers for Planning and BAC Secretariat',
            quantity: 4,
            unitCost: 85000,
            purpose: 'Procurement of multifunction printers for office operations',
        );
        if ($postedPr) {
            $this->seedActivePostedCase($postedPr);
        }

        $evaluationPr = $this->ensureSamplePurchaseRequest(
            itemNo: 4,
            itemName: 'Video Conferencing Equipment Package',
            description: 'Cameras, microphones, and controllers for BAC hearing room',
            quantity: 2,
            unitCost: 125000,
            purpose: 'Procurement of video conferencing equipment for BAC hearings',
        );
        if ($evaluationPr) {
            $this->seedEvaluationStageCase($evaluationPr);
        }

        $this->command?->info('Sample BAC / bidding / award / delivery data seeded.');
    }

    protected function loadDependencies(): bool
    {
        $this->bacSecretariat = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->first();
        $this->bacChair = User::query()->where('email', 'bac.chair@pms.gov.ph')->first();
        $this->supplyOfficer = User::query()->where('email', 'supply.officer@pms.gov.ph')->first();
        $this->inspector = User::query()->where('email', 'inspector@pms.gov.ph')->first();
        $this->cashier = User::query()->where('email', 'cashier@pms.gov.ph')->first();
        $this->accountingOfficer = User::query()->where('email', 'accounting.officer@pms.gov.ph')->first();
        $this->hope = User::query()->where('email', 'hope@pms.gov.ph')->first();
        $this->endUser = User::query()->where('email', 'end.user@pms.gov.ph')->first();
        $this->winningBidder = Bidder::query()->where('email', 'bidder@supplier.com')->first();
        $this->runnerUpBidder = Bidder::query()->where('email', 'bidder2@supplier.com')->first();
        $this->thirdBidder = Bidder::query()->where('email', 'bidder3@supplier.com')->first();

        if (! $this->bacSecretariat || ! $this->bacChair || ! $this->winningBidder) {
            $this->command?->warn('Skipping SampleBacProcurementSeeder: run UserSeeder and BidderPortalSeeder first.');

            return false;
        }

        return true;
    }

    protected function completeCafWorkflow(): void
    {
        $caf = CertificateOfAvailabilityOfFunds::query()->first();

        if (! $caf || ! $this->accountingOfficer) {
            return;
        }

        $service = app(CafService::class);

        if ($caf->status === CafStatus::Generated) {
            $service->certify($caf, $this->accountingOfficer, 'Sample CAF certified for demo.');
        }

        $caf->refresh();

        if ($caf->status === CafStatus::Certified) {
            $service->approve($caf, $this->hope ?? $this->bacChair, 'Sample CAF approved for demo.');
        }

        $caf->refresh();

        if ($caf->status === CafStatus::Approved) {
            $service->markPrinted($caf);
        }
    }

    protected function seedCompletedPublicBiddingCase(PurchaseRequest $pr): void
    {
        $existing = Procurement::query()->where('purchase_request_id', $pr->id)->first();

        if ($existing?->status === ProcurementCaseStatus::Completed) {
            return;
        }

        $caseService = app(ProcurementCaseService::class);
        $philgepsService = app(PhilgepsPostingService::class);
        $biddingService = app(BiddingService::class);
        $awardService = app(AwardService::class);
        $poService = app(PurchaseOrderService::class);
        $clarificationService = app(ClarificationService::class);
        $bidDocService = app(BidDocumentOrderService::class);

        $case = $existing ?? $caseService->openCase($pr, $this->bacSecretariat, 'goods', 'Public Bidding — Desktop Computers FY 2026');

        if (! $case->philgepsPosting) {
            $philgepsService->post($case, [
                'reference_no' => 'PHILGEPS-2026-000456',
                'posting_date' => now()->subDays(21),
                'closing_date' => now()->addDays(7),
            ], $this->bacSecretariat);
            $case->refresh();
        }

        if ($case->bidDocumentOrders()->doesntExist()) {
            foreach ([$this->winningBidder, $this->runnerUpBidder, $this->thirdBidder] as $bidder) {
                if (! $bidder) {
                    continue;
                }

                $order = $bidDocService->order($case, $bidder, 2500);
                $bidDocService->markPaid($order, 'OR-BD-2026-'.substr($bidder->id, 0, 4));
            }
        }

        if ($case->clarifications()->doesntExist()) {
            foreach ([$this->winningBidder, $this->runnerUpBidder, $this->thirdBidder] as $bidder) {
                if (! $bidder?->user || ! $case->philgepsPosting?->isOpenForSubmission()) {
                    continue;
                }

                $clarification = $clarificationService->ask(
                    $case,
                    $bidder,
                    $bidder->user,
                    'Please confirm whether OEM warranty must include on-site service within NCR.'
                );
                $clarificationService->answer(
                    $clarification,
                    $this->bacSecretariat,
                    'Yes. On-site warranty within NCR for a minimum of three (3) years is required per the PR specifications.'
                );
            }
        }

        if ($this->runnerUpBidder && ! $case->bidSubmissions()->where('bidder_id', $this->runnerUpBidder->id)->exists()) {
            if ($case->philgepsPosting?->isOpenForSubmission()) {
                $biddingService->submitBid($case, $this->runnerUpBidder, $this->bacSecretariat);
            }
        }

        if (! $case->bidSubmissions()->where('bidder_id', $this->winningBidder->id)->exists()) {
            if ($case->philgepsPosting?->isOpenForSubmission()) {
                $biddingService->submitBid($case, $this->winningBidder, $this->bacSecretariat);
            }
        }

        if ($case->status === ProcurementCaseStatus::Posted && $case->philgepsPosting) {
            $philgepsService->close($case->philgepsPosting);
            $case->refresh();
        }

        $winningBid = $case->bidSubmissions()->where('bidder_id', $this->winningBidder->id)->first();
        abort_if(! $winningBid, 500, 'Sample BAC seeder could not locate the winning bid submission.');

        if ($case->status === ProcurementCaseStatus::Bidding && ! $case->bidOpening) {
            $biddingService->openBids($case, $this->bacSecretariat, ['minutes_prepared' => true], [
                ['name' => $this->bacChair->name, 'role' => 'BAC Chairperson'],
                ['name' => $this->bacSecretariat->name, 'role' => 'BAC Secretariat'],
            ]);
            $case->refresh();
        }

        if ($case->status === ProcurementCaseStatus::Bidding && ! $winningBid->evaluation) {
            $biddingService->evaluate(
                $winningBid,
                $this->bacSecretariat,
                ['eligibility' => true, 'technical' => true, 'financial' => true],
                96.5,
                1,
                'award',
                'Lowest calculated responsive bid with complete eligibility documents.'
            );
            $case = $caseService->moveTo($case, ProcurementCaseStatus::Evaluation, 'Bid evaluated by BAC-TWG.');
        }

        if ($case->status === ProcurementCaseStatus::Evaluation) {
            $biddingService->processPostQualification(
                $case,
                $this->winningBidder,
                $this->bacSecretariat,
                true,
                'Warehouse inspection conducted; all licenses and mayor\'s permit validated.',
                'passed'
            );
            $case = $caseService->moveTo($case, ProcurementCaseStatus::PostQualification, 'Post-qualification in progress.');
            $case = $caseService->moveTo($case, ProcurementCaseStatus::Awarded, 'Post-qualification passed.');
        }

        if (! $case->noticeOfAward) {
            $noa = $awardService->issueNoticeOfAward($case, $this->winningBidder, $this->bacChair, (float) $pr->total_amount);
            $awardService->respond($noa, true, 'Bidder accepted the Notice of Award.');
        } elseif ($case->noticeOfAward->status->value !== 'accepted') {
            $awardService->respond($case->noticeOfAward, true, 'Bidder accepted the Notice of Award.');
        }

        if (! $case->noticeToProceed) {
            $awardService->issueNoticeToProceed($case, $this->bacChair, now()->subDays(2)->toDateString(), 30);
        }

        $case->refresh();

        $po = $case->purchaseOrders()->first();

        if (! $po) {
            $po = $poService->create([
                'procurement_id' => $case->id,
                'purchase_request_id' => $pr->id,
                'bidder_id' => $this->winningBidder->id,
                'mode_of_procurement_id' => $case->mode_of_procurement_id,
                'subtotal' => $pr->total_amount,
                'tax_amount' => 0,
                'total_amount' => $pr->total_amount,
                'delivery_date' => now()->addDays(10),
                'delivery_place' => 'DSGS Property Section, Quezon City',
            ], $this->supplyOfficer);
        }

        if ($po->status === PurchaseOrderStatus::Draft) {
            $poService->approve($po, $this->bacChair);
        }

        $po->refresh();

        $delivery = $po->deliveries()->first();

        if (! $delivery) {
            $delivery = $poService->recordDelivery($po, $this->supplyOfficer, [
                'delivery_date' => now()->subDays(3),
                'delivery_receipt_no' => 'DR-2026-0001',
                'remarks' => 'Complete delivery of 20 desktop units per PO specification.',
            ]);
        }

        $po->refresh();

        if ($po->status === PurchaseOrderStatus::Approved) {
            $poService->recordDelivery($po, $this->supplyOfficer, [
                'delivery_date' => now()->subDays(3),
                'delivery_receipt_no' => 'DR-2026-0001',
                'remarks' => 'Complete delivery of 20 desktop units per PO specification.',
            ]);
            $delivery = $po->deliveries()->first();
        }

        $po->refresh();

        if ($po->status === PurchaseOrderStatus::Delivered) {
            $poService->recordInspection($delivery, $this->inspector, 'passed', 'All units passed visual and functional inspection.');
        }

        $po->refresh();

        if ($po->status === PurchaseOrderStatus::Inspected) {
            $poService->recordAcceptance($delivery, $this->supplyOfficer, 'Accepted in good order by the Property Section.');
        }

        $po->refresh();

        if ($po->payments()->doesntExist() && in_array($po->status, [PurchaseOrderStatus::Accepted, PurchaseOrderStatus::Invoiced], true)) {
            $poService->recordPayment($po, $this->cashier, [
                'amount' => $po->total_amount,
                'or_no' => 'OR-2026-0001',
                'payment_date' => now()->subDay(),
                'status' => 'released',
            ]);
        }

        $this->seedCalendarEvents($case, completed: true);
    }

    protected function seedActivePostedCase(PurchaseRequest $pr): void
    {
        if (Procurement::query()->where('purchase_request_id', $pr->id)->exists()) {
            return;
        }

        $caseService = app(ProcurementCaseService::class);
        $philgepsService = app(PhilgepsPostingService::class);
        $biddingService = app(BiddingService::class);
        $clarificationService = app(ClarificationService::class);
        $bidDocService = app(BidDocumentOrderService::class);

        $case = $caseService->openCase($pr, $this->bacSecretariat, 'goods', 'SVP — Network Switches FY 2026');

        $philgepsService->post($case, [
            'reference_no' => 'PHILGEPS-2026-000789',
            'posting_date' => now()->subDays(3),
            'closing_date' => now()->addDays(10),
        ], $this->bacSecretariat);

        $case->refresh();

        foreach ([$this->winningBidder, $this->runnerUpBidder] as $bidder) {
            if (! $bidder) {
                continue;
            }

            $order = $bidDocService->order($case, $bidder, 1500);
            $bidDocService->markPaid($order, 'OR-BD-2026-'.substr($bidder->id, 0, 4));

            if ($bidder->user) {
                $pending = $clarificationService->ask(
                    $case,
                    $bidder,
                    $bidder->user,
                    'Will the agency accept equivalent 48-port managed switches from other manufacturers?'
                );

                if ($bidder->email === 'bidder@supplier.com') {
                    $clarificationService->answer(
                        $pending,
                        $this->bacSecretariat,
                        'Equivalent models are allowed provided they meet the minimum 24-port managed, Layer 3 specification.'
                    );
                }
            }

            $biddingService->submitBid($case, $bidder, $this->bacSecretariat);
        }

        $this->seedCalendarEvents($case, completed: false);
    }

    protected function seedEvaluationStageCase(PurchaseRequest $pr): void
    {
        if (Procurement::query()->where('purchase_request_id', $pr->id)->exists()) {
            return;
        }

        $caseService = app(ProcurementCaseService::class);
        $philgepsService = app(PhilgepsPostingService::class);
        $biddingService = app(BiddingService::class);
        $bidDocService = app(BidDocumentOrderService::class);

        $case = $caseService->openCase($pr, $this->bacSecretariat, 'goods', 'SVP — Office Equipment FY 2026');

        $posting = $philgepsService->post($case, [
            'reference_no' => 'PHILGEPS-2026-000901',
            'posting_date' => now()->subDays(14),
            'closing_date' => now()->addDays(5),
        ], $this->bacSecretariat);

        $case->refresh();

        foreach ([$this->winningBidder, $this->thirdBidder] as $bidder) {
            if (! $bidder) {
                continue;
            }

            $order = $bidDocService->order($case, $bidder, 1000);
            $bidDocService->markPaid($order, 'OR-BD-2026-'.substr($bidder->id, 0, 4));
            $bid = $biddingService->submitBid($case, $bidder, $this->bacSecretariat);
        }

        $philgepsService->close($posting);
        $case->refresh();

        $biddingService->openBids($case, $this->bacSecretariat);

        $winningBid = $case->bidSubmissions()->where('bidder_id', $this->winningBidder->id)->first();

        if ($winningBid) {
            $biddingService->evaluate(
                $winningBid,
                $this->bacSecretariat,
                ['eligibility' => true, 'technical' => true, 'financial' => true],
                92.0,
                1,
                'award',
                'Responsive bid pending post-qualification review.'
            );
            $caseService->moveTo($case, ProcurementCaseStatus::Evaluation, 'Bid evaluation completed; awaiting post-qualification.');
        }

        $this->seedCalendarEvents($case, completed: false);
    }

    protected function ensureSamplePurchaseRequest(
        int $itemNo,
        string $itemName,
        string $description,
        float $quantity,
        float $unitCost,
        string $purpose,
    ): ?PurchaseRequest {
        $ppmp = Ppmp::query()->whereHas('items')->first();

        if (! $ppmp || ! $this->endUser || ! $this->hope) {
            return null;
        }

        $templateItem = $ppmp->items()->first();

        $item = $ppmp->items()->firstOrCreate(
            ['item_no' => $itemNo],
            [
                'item_name' => $itemName,
                'description' => $description,
                'unit' => 'unit',
                'quantity' => $quantity,
                'estimated_unit_cost' => $unitCost,
                'abc' => $quantity * $unitCost,
                'schedule_start' => '2026-04-01',
                'schedule_end' => '2026-05-31',
                'mode_of_procurement_id' => $templateItem?->mode_of_procurement_id,
                'fund_source_id' => $templateItem?->fund_source_id,
                'pap_id' => $templateItem?->pap_id,
                'uacs_code_id' => $templateItem?->uacs_code_id,
                'budget_allocation_id' => $templateItem?->budget_allocation_id,
            ]
        );

        $existing = PurchaseRequest::query()
            ->where('ppmp_id', $ppmp->id)
            ->whereHas('items', fn ($q) => $q->where('ppmp_item_id', $item->id))
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->createApprovedPurchaseRequest($ppmp, $item, $purpose, $this->endUser);
    }

    protected function createApprovedPurchaseRequest(
        Ppmp $ppmp,
        PpmpItem $item,
        string $purpose,
        User $requester,
    ): PurchaseRequest {
        $pr = PurchaseRequest::query()->create([
            'fiscal_year_id' => $ppmp->fiscal_year_id,
            'division_id' => $ppmp->division_id,
            'ppmp_id' => $ppmp->id,
            'purpose' => $purpose,
            'status' => PurchaseRequestStatus::Draft,
            'requested_by' => $requester->id,
        ]);

        $pr->items()->create([
            'ppmp_item_id' => $item->id,
            'item_name' => $item->item_name,
            'description' => $item->description,
            'unit' => $item->unit,
            'quantity' => $item->quantity,
            'unit_cost' => $item->estimated_unit_cost,
        ]);

        $pr->refresh();

        $pr->transitionTo(PurchaseRequestStatus::DivisionChief, 'Sample data.', enforce: false);
        $pr->transitionTo(PurchaseRequestStatus::Planning, 'Sample data.', enforce: false);
        $pr->transitionTo(PurchaseRequestStatus::Budget, 'Sample data.', enforce: false);
        $pr->update(['hope_by' => $this->hope->id, 'hope_at' => now()]);
        $pr->transitionTo(PurchaseRequestStatus::Approved, 'Sample data approved by HOPE.', enforce: false);

        app(CafService::class)->generate($pr, User::query()->where('email', 'budget.officer@pms.gov.ph')->first());

        return $pr->fresh();
    }

    protected function seedCalendarEvents(Procurement $case, bool $completed): void
    {
        if ($case->calendarEvents()->exists()) {
            return;
        }

        $events = $completed
            ? [
                ['activity_type' => 'pre_bid_conference', 'scheduled_at' => now()->subDays(18), 'venue' => 'BAC Conference Room'],
                ['activity_type' => 'bid_opening', 'scheduled_at' => now()->subDays(6), 'venue' => 'BAC Conference Room'],
                ['activity_type' => 'post_qualification', 'scheduled_at' => now()->subDays(4), 'venue' => 'Supplier Warehouse, Makati City'],
                ['activity_type' => 'notice_of_award', 'scheduled_at' => now()->subDays(3), 'venue' => 'BAC Office'],
                ['activity_type' => 'notice_to_proceed', 'scheduled_at' => now()->subDays(2), 'venue' => 'BAC Office'],
                ['activity_type' => 'purchase_order', 'scheduled_at' => now()->subDay(), 'venue' => 'Supply Division'],
            ]
            : [
                ['activity_type' => 'pre_bid_conference', 'scheduled_at' => now()->addDays(2), 'venue' => 'BAC Conference Room'],
                ['activity_type' => 'bid_opening', 'scheduled_at' => now()->addDays(11), 'venue' => 'BAC Conference Room'],
            ];

        foreach ($events as $event) {
            BacCalendarEvent::query()->create([
                'procurement_id' => $case->id,
                'activity_type' => $event['activity_type'],
                'title' => BacCalendarEvent::TYPES[$event['activity_type']] ?? $event['activity_type'],
                'scheduled_at' => $event['scheduled_at'],
                'venue' => $event['venue'],
                'status' => $completed ? 'completed' : 'scheduled',
                'created_by' => $this->bacSecretariat->id,
            ]);
        }
    }
}
