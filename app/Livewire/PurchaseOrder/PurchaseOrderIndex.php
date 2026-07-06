<?php

namespace App\Livewire\PurchaseOrder;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Supplier\Bidder;
use App\Services\Procurement\PurchaseOrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Phase 17: Purchase Order issuance. Applicable directly off an Approved
 * Purchase Request for Small Value Procurement / Shopping / Direct
 * Contracting (no bidding case required); PB/LSB/DC/NP-mode POs are
 * instead created from the procurement case page after NTP (see
 * Bac\ProcurementShow::createPurchaseOrder).
 */
#[Layout('components.layouts.app')]
class PurchaseOrderIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    public bool $showCreateModal = false;

    public string $purchase_request_id = '';

    public string $bidder_id = '';

    public string $delivery_date = '';

    public string $delivery_place = '';

    public array $items = [];

    public function mount(): void
    {
        Gate::authorize('purchase-order.view');
    }

    public function openCreateModal(): void
    {
        Gate::authorize('purchase-order.create');
        $this->reset('purchase_request_id', 'bidder_id', 'delivery_date', 'delivery_place', 'items');
        $this->showCreateModal = true;
    }

    public function updatedPurchaseRequestId(): void
    {
        $this->items = [];

        if (! $this->purchase_request_id) {
            return;
        }

        $pr = PurchaseRequest::with('items')->find($this->purchase_request_id);

        $this->items = $pr?->items->map(fn ($i) => [
            'item_name' => $i->item_name, 'description' => $i->description, 'unit' => $i->unit,
            'quantity' => (string) $i->quantity, 'unit_cost' => (string) $i->unit_cost,
        ])->all() ?? [];
    }

    public function create(PurchaseOrderService $service): void
    {
        Gate::authorize('purchase-order.create');

        $this->validate([
            'purchase_request_id' => 'required|exists:purchase_requests,id',
            'delivery_date' => 'required|date',
            'delivery_place' => 'required|string',
        ]);

        $pr = PurchaseRequest::findOrFail($this->purchase_request_id);
        abort_unless($pr->status === PurchaseRequestStatus::Approved, 422, 'Only an approved Purchase Request can be converted to a Purchase Order.');
        abort_if(PurchaseOrder::where('purchase_request_id', $pr->id)->exists(), 422, 'A Purchase Order already exists for this Purchase Request.');

        $po = $service->create([
            'purchase_request_id' => $pr->id,
            'bidder_id' => $this->bidder_id ?: null,
            'delivery_date' => $this->delivery_date,
            'delivery_place' => $this->delivery_place,
        ], Auth::user());

        foreach ($this->items as $item) {
            $po->items()->create($item);
        }
        $po->recalculateTotals();

        $this->showCreateModal = false;
        session()->flash('status', 'Purchase Order created as draft.');
        $this->redirect(route('purchase-orders.show', $po), navigate: false);
    }

    public function render()
    {
        $purchaseOrders = PurchaseOrder::query()
            ->with(['purchaseRequest.division', 'bidder'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('created_at')
            ->paginate(15);

        $eligiblePrs = PurchaseRequest::query()
            ->where('status', PurchaseRequestStatus::Approved)
            ->whereNotIn('id', PurchaseOrder::query()->whereNotNull('purchase_request_id')->pluck('purchase_request_id'))
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.purchase-order.purchase-order-index', [
            'purchaseOrders' => $purchaseOrders,
            'eligiblePrs' => $eligiblePrs,
            'bidders' => Bidder::query()->where('status', 'verified')->orderBy('company_name')->get(),
            'statuses' => PurchaseOrderStatus::cases(),
        ])->layout('components.layouts.app', ['title' => 'Purchase Orders']);
    }
}
