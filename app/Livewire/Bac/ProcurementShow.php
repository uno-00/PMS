<?php

namespace App\Livewire\Bac;

use App\Enums\ProcurementCaseStatus;
use App\Models\Bac\BacCalendarEvent;
use App\Models\Bac\BidClarification;
use App\Models\Bac\BidSubmission;
use App\Models\Bac\Procurement;
use App\Models\Supplier\Bidder;
use App\Services\Bac\AwardService;
use App\Services\Bac\BiddingService;
use App\Services\Bac\ClarificationService;
use App\Services\Bac\PhilgepsPostingService;
use App\Services\Procurement\PurchaseOrderService;
use App\Services\Support\DocumentStorageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class ProcurementShow extends Component
{
    use WithFileUploads;

    public Procurement $procurement;

    public ?string $activeModal = null;

    // Schedule activity
    public string $activity_type = 'pre_procurement_conference';

    public string $scheduled_at = '';

    public string $venue = '';

    // PhilGEPS posting
    public string $reference_no = '';

    public string $posting_date = '';

    public string $closing_date = '';

    // Post-qualification / bidder selection
    public string $bidder_id = '';

    public bool $site_visit_conducted = false;

    public string $document_validation_notes = '';

    public string $result = 'passed';

    // NOA
    public string $amount = '';

    // NTP
    public string $effectivity_date = '';

    public string $contract_duration_days = '';

    // PO
    public string $delivery_date = '';

    public string $delivery_place = '';

    public array $po_items = [];

    public string $remarks = '';

    // Clarification answer
    public string $answer_text = '';

    public ?string $answeringClarificationId = null;

    // Bidding document upload
    public $biddingDocumentFile = null;

    public function mount(Procurement $procurement): void
    {
        Gate::authorize('bac-calendar.view');
        $this->procurement = $procurement;
    }

    public function openModal(string $modal): void
    {
        $this->activeModal = $modal;
        $this->resetErrorBag();

        if ($modal === 'po' && empty($this->po_items)) {
            $this->po_items = $this->procurement->purchaseRequest->items->map(fn ($i) => [
                'item_name' => $i->item_name, 'description' => $i->description, 'unit' => $i->unit,
                'quantity' => (string) $i->quantity, 'unit_cost' => (string) $i->unit_cost,
            ])->all();
        }
    }

    public function closeModal(): void
    {
        $this->activeModal = null;
    }

    public function scheduleActivity(): void
    {
        Gate::authorize('bac-calendar.manage');
        $this->validate(['activity_type' => 'required', 'scheduled_at' => 'required|date', 'venue' => 'nullable|string']);

        BacCalendarEvent::query()->create([
            'procurement_id' => $this->procurement->id,
            'activity_type' => $this->activity_type,
            'title' => BacCalendarEvent::TYPES[$this->activity_type] ?? $this->activity_type,
            'scheduled_at' => $this->scheduled_at,
            'venue' => $this->venue,
            'status' => 'scheduled',
            'created_by' => Auth::id(),
        ]);

        $this->closeModal();
        session()->flash('status', 'Activity scheduled.');
    }

    public function postToPhilgeps(PhilgepsPostingService $service): void
    {
        Gate::authorize('philgeps.post');
        $this->validate(['closing_date' => 'required|date|after:today']);

        $service->post($this->procurement, [
            'reference_no' => $this->reference_no ?: null,
            'posting_date' => $this->posting_date ?: now(),
            'closing_date' => $this->closing_date,
            'remarks' => $this->remarks,
        ], Auth::user());

        $this->closeModal();
        session()->flash('status', 'Procurement posted to PhilGEPS.');
        $this->procurement->refresh();
    }

    public function closePosting(PhilgepsPostingService $service): void
    {
        Gate::authorize('philgeps.manage');
        $service->close($this->procurement->philgepsPosting);
        session()->flash('status', 'Posting closed. Proceeding to bidding.');
        $this->procurement->refresh();
    }

    public function openBids(BiddingService $service): void
    {
        Gate::authorize('bid-opening.conduct');
        $service->openBids($this->procurement, Auth::user());
        session()->flash('status', 'Bids opened.');
        $this->procurement->refresh();
    }

    public function evaluateBid(string $bidSubmissionId, BiddingService $service): void
    {
        Gate::authorize('bid-evaluation.evaluate');
        $bid = BidSubmission::findOrFail($bidSubmissionId);
        $service->evaluate($bid, Auth::user(), [], 100, 1, 'recommended', 'Compliant with all requirements.');
        session()->flash('status', 'Bid evaluated.');
        $this->procurement->refresh();
    }

    public function processPostQualification(BiddingService $service): void
    {
        Gate::authorize('post-qualification.process');
        $this->validate(['bidder_id' => 'required', 'result' => 'required']);

        $bidder = Bidder::findOrFail($this->bidder_id);
        $service->processPostQualification($this->procurement, $bidder, Auth::user(), $this->site_visit_conducted, $this->document_validation_notes, $this->result);

        if ($this->result === 'passed') {
            $this->procurement->transitionTo(ProcurementCaseStatus::PostQualification, 'Post-qualification completed.', enforce: false);
        }

        $this->closeModal();
        session()->flash('status', 'Post-qualification recorded.');
        $this->procurement->refresh();
    }

    public function issueNoa(AwardService $service): void
    {
        Gate::authorize('award.generate');
        $this->validate(['bidder_id' => 'required', 'amount' => 'required|numeric|min:0.01']);

        $bidder = Bidder::findOrFail($this->bidder_id);
        $service->issueNoticeOfAward($this->procurement, $bidder, Auth::user(), (float) $this->amount, $this->remarks);

        $this->closeModal();
        session()->flash('status', 'Notice of Award issued.');
        $this->procurement->refresh();
    }

    public function issueNtp(AwardService $service): void
    {
        Gate::authorize('ntp.generate');
        $this->validate(['effectivity_date' => 'required|date']);

        $service->issueNoticeToProceed($this->procurement, Auth::user(), $this->effectivity_date, $this->contract_duration_days ? (int) $this->contract_duration_days : null);

        $this->closeModal();
        session()->flash('status', 'Notice to Proceed issued.');
        $this->procurement->refresh();
    }

    public function createPurchaseOrder(PurchaseOrderService $service): void
    {
        Gate::authorize('purchase-order.create');
        $this->validate(['delivery_date' => 'required|date', 'delivery_place' => 'required|string']);

        $noa = $this->procurement->noticeOfAward;

        $po = $service->create([
            'procurement_id' => $this->procurement->id,
            'purchase_request_id' => $this->procurement->purchase_request_id,
            'bidder_id' => $noa?->bidder_id,
            'mode_of_procurement_id' => $this->procurement->mode_of_procurement_id,
            'delivery_date' => $this->delivery_date,
            'delivery_place' => $this->delivery_place,
        ], Auth::user());

        foreach ($this->po_items as $item) {
            $po->items()->create($item);
        }

        $po->recalculateTotals();

        $this->closeModal();
        session()->flash('status', 'Purchase Order created as draft.');
        $this->redirect(route('purchase-orders.show', $po), navigate: false);
    }

    public function answerClarification(string $clarificationId, ClarificationService $service): void
    {
        Gate::authorize('clarification.answer');
        $this->validate(['answer_text' => 'required|string|min:2|max:2000']);

        $clarification = BidClarification::findOrFail($clarificationId);
        $service->answer($clarification, Auth::user(), $this->answer_text);

        $this->reset('answer_text', 'answeringClarificationId');
        session()->flash('status', 'Clarification answered.');
        $this->procurement->refresh();
    }

    public function uploadBiddingDocument(DocumentStorageService $documents): void
    {
        Gate::authorize('philgeps.manage');
        $this->validate(['biddingDocumentFile' => 'required|file|max:20480']);

        $documents->store($this->biddingDocumentFile, $this->procurement->philgepsPosting, 'philgeps', 'bidding-documents', Auth::user());

        $this->reset('biddingDocumentFile');
        session()->flash('status', 'Bidding document uploaded. Bidders who paid for the documents can now download it.');
        $this->procurement->refresh();
    }

    public function render()
    {
        $this->procurement->load([
            'purchaseRequest.division', 'modeOfProcurement', 'bacChairperson', 'calendarEvents',
            'philgepsPosting.documents', 'bidSubmissions.bidder', 'bidSubmissions.evaluation',
            'postQualifications.bidder', 'noticeOfAward.bidder', 'noticeToProceed', 'purchaseOrders',
            'clarifications.bidder',
        ]);

        $bidders = Bidder::query()->where('status', 'verified')->orderBy('company_name')->get();
        $history = $this->procurement->workflowHistories()->with('performedBy')->latest('performed_at')->get();

        return view('livewire.bac.procurement-show', ['bidders' => $bidders, 'history' => $history])
            ->layout('components.layouts.app', ['title' => $this->procurement->case_no]);
    }
}
