<?php

namespace App\Livewire\PurchaseOrder;

use App\Models\Procurement\Delivery;
use App\Models\Procurement\PurchaseOrder;
use App\Services\Procurement\PurchaseOrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PurchaseOrderShow extends Component
{
    public PurchaseOrder $purchaseOrder;

    public ?string $activeModal = null;

    // Delivery
    public string $delivery_receipt_no = '';

    public string $delivery_date = '';

    public string $delivery_status = 'delivered';

    public string $delivery_remarks = '';

    // Inspection / Acceptance
    public ?string $activeDeliveryId = null;

    public string $inspection_result = 'passed';

    public string $inspection_remarks = '';

    public string $acceptance_remarks = '';

    // Payment
    public string $or_no = '';

    public string $payment_amount = '';

    public string $payment_date = '';

    public string $payment_method = 'check';

    public string $payment_status = 'processed';

    public function mount(PurchaseOrder $purchase_order): void
    {
        Gate::authorize('view', $purchase_order);
        $this->purchaseOrder = $purchase_order;
        $this->payment_amount = (string) $purchase_order->total_amount;
    }

    public function openModal(string $modal, ?string $deliveryId = null): void
    {
        $this->activeModal = $modal;
        $this->activeDeliveryId = $deliveryId;
        $this->resetErrorBag();
    }

    public function closeModal(): void
    {
        $this->activeModal = null;
        $this->activeDeliveryId = null;
    }

    public function approve(PurchaseOrderService $service): void
    {
        Gate::authorize('purchase-order.approve');
        $service->approve($this->purchaseOrder, Auth::user());
        session()->flash('status', 'Purchase Order approved.');
        $this->purchaseOrder->refresh();
    }

    public function recordDelivery(PurchaseOrderService $service): void
    {
        Gate::authorize('delivery.record');
        $this->validate([
            'delivery_date' => 'required|date',
            'delivery_receipt_no' => 'nullable|string',
        ]);

        $service->recordDelivery($this->purchaseOrder, Auth::user(), [
            'delivery_receipt_no' => $this->delivery_receipt_no,
            'delivery_date' => $this->delivery_date,
            'status' => $this->delivery_status,
            'remarks' => $this->delivery_remarks,
        ]);

        $this->closeModal();
        session()->flash('status', 'Delivery recorded.');
        $this->purchaseOrder->refresh();
    }

    public function recordInspection(PurchaseOrderService $service): void
    {
        Gate::authorize('inspection.conduct');
        $this->validate(['inspection_result' => 'required|in:passed,failed']);

        $delivery = Delivery::findOrFail($this->activeDeliveryId);
        $service->recordInspection($delivery, Auth::user(), $this->inspection_result, $this->inspection_remarks ?: null);

        $this->closeModal();
        session()->flash('status', 'Inspection recorded.');
        $this->purchaseOrder->refresh();
    }

    public function recordAcceptance(PurchaseOrderService $service): void
    {
        Gate::authorize('acceptance.confirm');

        $delivery = Delivery::findOrFail($this->activeDeliveryId);
        $service->recordAcceptance($delivery, Auth::user(), $this->acceptance_remarks ?: null);

        $this->closeModal();
        session()->flash('status', 'Goods/services accepted.');
        $this->purchaseOrder->refresh();
    }

    public function recordPayment(PurchaseOrderService $service): void
    {
        Gate::authorize('payment.process');
        $this->validate([
            'or_no' => 'required|string',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        $service->recordPayment($this->purchaseOrder, Auth::user(), [
            'or_no' => $this->or_no,
            'amount' => $this->payment_amount,
            'payment_date' => $this->payment_date,
            'method' => $this->payment_method,
            'status' => $this->payment_status,
        ]);

        $this->closeModal();
        session()->flash('status', 'Payment recorded.');
        $this->purchaseOrder->refresh();
    }

    public function render()
    {
        $this->purchaseOrder->load(['items', 'bidder', 'purchaseRequest.division', 'deliveries.inspection', 'deliveries.acceptance', 'payments']);

        $history = $this->purchaseOrder->workflowHistories()->with('performedBy')->latest('performed_at')->get();

        return view('livewire.purchase-order.purchase-order-show', compact('history'))
            ->layout('components.layouts.app', ['title' => $this->purchaseOrder->po_no]);
    }
}
