<?php

namespace App\Livewire\Portal;

use App\Models\Bac\Procurement;
use App\Models\Supplier\Bidder;
use App\Services\Bac\BiddingService;
use App\Services\Bac\ClarificationService;
use App\Services\Supplier\BidDocumentOrderService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.portal')]
class OpportunityShow extends Component
{
    use WithFileUploads;

    public Procurement $procurement;

    public Bidder $bidder;

    public string $question = '';

    public $technicalFile = null;

    public $financialFile = null;

    public $eligibilityFile = null;

    public function mount(Procurement $procurement): void
    {
        $bidder = Auth::user()->bidder;
        abort_unless($bidder, 403, 'No supplier profile linked to this account.');

        $this->procurement = $procurement;
        $this->bidder = $bidder;
    }

    /** Phase 10: fee schedule per ABC bracket, mirroring common GPPB guidance. Configurable business rule, not hardcoded per-procurement. */
    protected function bidDocumentFee(): float
    {
        $abc = (float) $this->procurement->abc;

        return match (true) {
            $abc <= 500_000 => 500.0,
            $abc <= 1_000_000 => 1_000.0,
            $abc <= 5_000_000 => 5_000.0,
            $abc <= 10_000_000 => 10_000.0,
            $abc <= 50_000_000 => 25_000.0,
            default => 50_000.0,
        };
    }

    public function orderBidDocuments(BidDocumentOrderService $service): void
    {
        $service->order($this->procurement, $this->bidder, $this->bidDocumentFee());
        session()->flash('status', 'Bid document order created. Proceed to payment in "Bid Doc Orders".');
    }

    public function askClarification(ClarificationService $service): void
    {
        $this->validate(['question' => 'required|string|min:5|max:2000']);

        $service->ask($this->procurement, $this->bidder, Auth::user(), $this->question);

        $this->reset('question');
        session()->flash('status', 'Your clarification has been sent to the BAC Secretariat.');
        $this->procurement->refresh();
    }

    public function submitBid(BiddingService $service): void
    {
        $order = $this->procurement->bidDocumentOrders()->where('bidder_id', $this->bidder->id)->first();
        abort_unless($order && $order->payment_status === 'paid', 422, 'You must purchase and pay for the bidding documents before submitting a bid.');

        $this->validate([
            'technicalFile' => 'required|file|max:20480',
            'financialFile' => 'required|file|max:20480',
            'eligibilityFile' => 'required|file|max:20480',
        ]);

        $service->submitBid($this->procurement, $this->bidder, Auth::user(), $this->technicalFile, $this->financialFile, $this->eligibilityFile);

        $this->reset('technicalFile', 'financialFile', 'eligibilityFile');
        session()->flash('status', 'Bid submitted successfully. You may submit a new version until the deadline.');
        $this->procurement->refresh();
    }

    public function render()
    {
        $this->procurement->load(['philgepsPosting.documents', 'modeOfProcurement', 'clarifications' => fn ($q) => $q->where('bidder_id', $this->bidder->id)->latest()]);

        $order = $this->procurement->bidDocumentOrders()->where('bidder_id', $this->bidder->id)->first();
        $myBids = $this->procurement->bidSubmissions()->where('bidder_id', $this->bidder->id)->latest('version')->get();
        $biddingDocuments = $this->procurement->philgepsPosting?->documentsInCategory('bidding-documents')->get() ?? collect();

        return view('livewire.portal.opportunity-show', [
            'order' => $order,
            'myBids' => $myBids,
            'biddingDocuments' => $biddingDocuments,
            'canDownloadDocs' => $order && $order->payment_status === 'paid',
            'isOpen' => $this->procurement->philgepsPosting?->isOpenForSubmission() ?? false,
        ])->layout('components.layouts.portal', ['title' => $this->procurement->title]);
    }
}
