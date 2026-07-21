<?php

namespace App\Livewire\Supplier;

use App\Models\Supplier\Bidder;
use App\Services\Supplier\BidderService;
use App\Services\Support\DocumentStorageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class BidderShow extends Component
{
    public Bidder $bidder;

    public bool $showSuspendModal = false;

    public string $suspend_remarks = '';

    public function mount(Bidder $bidder): void
    {
        Gate::authorize('view', $bidder);
        $this->bidder = $bidder;
    }

    public function verify(BidderService $service): void
    {
        Gate::authorize('verify', $this->bidder);

        $service->verify($this->bidder, Auth::user());
        session()->flash('status', 'Bidder verified and eligible to participate in procurement opportunities.');
        $this->bidder->refresh();
    }

    public function openSuspendModal(): void
    {
        Gate::authorize('verify', $this->bidder);
        $this->suspend_remarks = '';
        $this->showSuspendModal = true;
    }

    public function suspend(BidderService $service): void
    {
        Gate::authorize('verify', $this->bidder);
        $this->validate(['suspend_remarks' => 'required|string|max:1000']);

        $service->suspend($this->bidder, $this->suspend_remarks);
        $this->showSuspendModal = false;
        session()->flash('status', 'Bidder suspended.');
        $this->bidder->refresh();
    }

    public function downloadDocument(string $documentId, DocumentStorageService $documents): mixed
    {
        $document = $this->bidder->documents()->findOrFail($documentId);

        return redirect($documents->temporaryUrl($document));
    }

    public function render()
    {
        $this->bidder->load(['documents', 'bidSubmissions.procurement', 'clarifications.procurement', 'noticeOfAwards.procurement', 'bidDocumentOrders.procurement']);

        return view('livewire.supplier.bidder-show')
            ->layout('components.layouts.app', ['title' => $this->bidder->company_name]);
    }
}
