<?php

namespace App\Livewire\PurchaseRequest;

use App\Exceptions\BudgetExceededException;
use App\Models\Procurement\PurchaseRequest;
use App\Services\Bac\ProcurementCaseService;
use App\Services\Procurement\PurchaseRequestService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PurchaseRequestShow extends Component
{
    public PurchaseRequest $purchaseRequest;

    public string $remarks = '';

    public bool $showRejectModal = false;

    public function mount(PurchaseRequest $purchase_request): void
    {
        Gate::authorize('view', $purchase_request);
        $this->purchaseRequest = $purchase_request;
    }

    public function divisionChiefApprove(PurchaseRequestService $service): void
    {
        Gate::authorize('divisionChiefReview', PurchaseRequest::class);
        $service->divisionChiefApprove($this->purchaseRequest, Auth::user(), $this->remarks);
        session()->flash('status', 'Approved by Division Chief.');
        $this->purchaseRequest->refresh();
    }

    public function planningApprove(PurchaseRequestService $service): void
    {
        Gate::authorize('planningReview', PurchaseRequest::class);
        $service->planningApprove($this->purchaseRequest, Auth::user(), $this->remarks);
        session()->flash('status', 'Approved by Planning Office.');
        $this->purchaseRequest->refresh();
    }

    public function budgetApprove(PurchaseRequestService $service): void
    {
        Gate::authorize('budgetReview', PurchaseRequest::class);
        $service->budgetApprove($this->purchaseRequest, Auth::user(), $this->remarks);
        session()->flash('status', 'Approved by Budget Office.');
        $this->purchaseRequest->refresh();
    }

    public function hopeApprove(PurchaseRequestService $service): void
    {
        Gate::authorize('hopeApprove', PurchaseRequest::class);

        try {
            $service->hopeApprove($this->purchaseRequest, Auth::user(), $this->remarks);
            session()->flash('status', 'Purchase Request approved by HOPE. Certificate of Availability of Funds generated.');
        } catch (BudgetExceededException $e) {
            $this->addError('budget', $e->getMessage());
        }

        $this->purchaseRequest->refresh();
    }

    public function reject(PurchaseRequestService $service): void
    {
        $this->validate(['remarks' => ['required', 'string', 'min:5']]);
        $service->reject($this->purchaseRequest, Auth::user(), $this->remarks);
        $this->showRejectModal = false;
        session()->flash('status', 'Purchase Request rejected.');
        $this->purchaseRequest->refresh();
    }

    public function cancel(PurchaseRequestService $service): void
    {
        $service->cancel($this->purchaseRequest, Auth::user(), 'Cancelled by requester.');
        session()->flash('status', 'Purchase Request cancelled.');
        $this->purchaseRequest->refresh();
    }

    public function initiateProcurement(ProcurementCaseService $service): void
    {
        Gate::authorize('bac-calendar.manage');
        $case = $service->openCase($this->purchaseRequest, Auth::user());
        $this->redirect(route('procurements.show', $case), navigate: false);
    }

    public function render()
    {
        $items = $this->purchaseRequest->items()->with('ppmpItem')->get();
        $history = $this->purchaseRequest->workflowHistories()->with('performedBy')->latest('performed_at')->get();

        return view('livewire.purchase-request.purchase-request-show', compact('items', 'history'))
            ->layout('components.layouts.app', ['title' => $this->purchaseRequest->pr_no]);
    }
}
